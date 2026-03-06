#!/usr/bin/env bash

set -euo pipefail

# -------------------------------------------------------------------
# Load config
# -------------------------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG="${SCRIPT_DIR}/runpod.yaml"
ENV_FILE="${SCRIPT_DIR}/../.env"

# Load .env for RUNPOD_API_KEY and other secrets (strip Windows CR line endings)
if [[ -f "$ENV_FILE" ]]; then
    set -a
    # shellcheck source=/dev/null
    source <(sed 's/\r//' "$ENV_FILE")
    set +a
fi

if [[ ! -f "$CONFIG" ]]; then
    echo "[ERROR] Config file not found: ${CONFIG}" >&2
    exit 1
fi

# Convert YAML to JSON once at startup so all jq calls can use standard JSON parsing
CONFIG_JSON=$(python3 -c "
import sys, json
try:
    import yaml
except ImportError:
    sys.exit('[ERROR] Python yaml module not found. Run: pip install pyyaml')
with open('${CONFIG}') as f:
    print(json.dumps(yaml.safe_load(f)))
") || exit 1

IMAGE=$(echo "$CONFIG_JSON" | jq -r '.image')
SSH_KEY=$(eval echo "$(echo "$CONFIG_JSON" | jq -r '.key')")
RUNPOD_API_KEY="${RUNPOD_API_KEY:-}"

# Load SSH public key lazily (only when needed)
load_ssh_pubkey() {
    if [[ -z "${SSH_PUBKEY:-}" ]]; then
        SSH_PUBKEY="$(cat "${SSH_KEY}.pub")"
    fi
}

SSH_DAEMON_ARGS='bash -c "apt-get update -qq && DEBIAN_FRONTEND=noninteractive apt-get install -y openssh-server && mkdir -p ~/.ssh && chmod 700 ~/.ssh && echo $MY_SSH_PUBLIC_KEY >> ~/.ssh/authorized_keys && chmod 700 ~/.ssh/authorized_keys && ssh-keygen -A && service ssh start && sleep infinity"'

# -------------------------------------------------------------------
# RunPod GraphQL API helper
# -------------------------------------------------------------------
runpod_api() {
    local query="$1"
    if [[ -z "$RUNPOD_API_KEY" ]]; then
        log_error "RUNPOD_API_KEY must be set in .env. Get it from https://www.runpod.io/console/user/settings"
        exit 1
    fi
    curl -sSL --max-time 30 -X POST \
        "https://api.runpod.io/graphql?api_key=${RUNPOD_API_KEY}" \
        -H 'Content-Type: application/json' \
        -d "$query"
}

# -------------------------------------------------------------------
# Colors
# -------------------------------------------------------------------
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

log_info() { echo -e "${CYAN}[INFO]${NC}  $*"; }
log_ok() { echo -e "${GREEN}[OK]${NC}    $*"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC}  $*"; }
log_error() { echo -e "${RED}[ERROR]${NC} $*"; }

# -------------------------------------------------------------------
# Dynamic pod lookup via runpodctl
# -------------------------------------------------------------------

# Derive a pod name from a model path.
# e.g. "lmstudio-community/Qwen3.5-35B-A3B-GGUF" -> "lmstudio-qwen35b-a3b-gguf"
pod_name_from_model() {
    local model="$1"
    local segment
    segment=$(basename "$model")
    echo "lmstudio-$(echo "$segment" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]/-/g' | sed 's/-\+/-/g' | sed 's/^-//;s/-$//')"
}

# Returns a comma-separated list of quoted pod names from config
pod_name_filter() {
    local i count filter_parts=() names_json
    count=$(echo "$CONFIG_JSON" | jq '.pods | length')
    for ((i = 0; i < count; i++)); do
        local model
        model=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].model")
        filter_parts+=("\"$(pod_name_from_model "$model")\"")
    done
    local IFS=','
    echo "${filter_parts[*]}"
}

# Returns JSON array of all running configured pods
our_pods_json() {
    local raw names_filter
    raw=$(runpodctl get pod --allfields 2> /dev/null) || raw=''
    if [[ -z "$raw" ]]; then
        echo '[]'
        return
    fi
    names_filter=$(pod_name_filter)
    echo "$raw" | awk 'NR>1 {
        status = "UNKNOWN"
        if ($0 ~ /RUNNING/) status = "RUNNING"
        else if ($0 ~ /STOPPED/) status = "STOPPED"
        else if ($0 ~ /EXITED/)  status = "EXITED"
        printf "{\"id\":\"%s\",\"name\":\"%s\",\"desiredStatus\":\"%s\"}\n", $1, $2, status
    }' | jq -s --argjson names "[${names_filter}]" \
        '[.[] | select(.name as $n | $names | index($n) != null)]' 2> /dev/null || echo '[]'
}

# Returns desiredStatus for a given pod ID
pod_status() {
    local pod_id="$1"
    local line
    line=$(runpodctl get pod --allfields 2> /dev/null \
        | awk -v id="$pod_id" '$1 == id {print $0}') || line=''
    if echo "$line" | grep -q 'RUNNING'; then
        echo 'RUNNING'
    elif echo "$line" | grep -q 'STOPPED'; then
        echo 'STOPPED'
    elif echo "$line" | grep -q 'EXITED'; then
        echo 'EXITED'
    else echo 'UNKNOWN'; fi
}

# Returns SSH host and port for a given pod ID (format "host port").
# Strips ANSI codes, retries up to 120s.
pod_ssh_details() {
    local pod_id="$1"
    local max_wait=120 elapsed=0 row entry
    while [[ $elapsed -lt $max_wait ]]; do
        row=$(runpodctl get pod --allfields 2> /dev/null \
            | sed 's/\x1b\[[0-9;]*[mGKHF]//g' \
            | awk -v id="$pod_id" '$1 == id {print $0}') || row=''
        entry=$(echo "$row" \
            | grep -oE '[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+:[0-9]+->22' \
            | sed 's/->22//' | head -1) || entry=''
        if [[ -n "$entry" ]]; then
            echo "${entry%%:*} ${entry##*:}"
            return 0
        fi
        log_info "Waiting for SSH port on pod ${pod_id}... (${elapsed}s)" >&2
        sleep 5
        elapsed=$((elapsed + 5))
    done
    log_error "SSH port for pod ${pod_id} did not appear within ${max_wait}s." >&2
    return 1
}

# -------------------------------------------------------------------
# Helpers
# -------------------------------------------------------------------

# Parse pod ID from "pod "abc123xyz" created" output
parse_pod_id() {
    grep -oP 'pod "\K[^"]+' || true
}

# Wait until a pod reaches a given status (default: RUNNING)
wait_for_pod() {
    local pod_id="$1"
    local target_status="${2:-RUNNING}"
    local max_wait=300 elapsed=0
    log_info "Waiting for pod ${pod_id} to reach ${target_status}..."
    while [[ $elapsed -lt $max_wait ]]; do
        local status
        status=$(pod_status "$pod_id")
        if [[ "$status" == "$target_status" ]]; then
            log_ok "Pod ${pod_id} is ${target_status}."
            return 0
        fi
        log_info "  Pod ${pod_id} status: ${status:-unknown} (${elapsed}s elapsed, waiting for ${target_status})..."
        sleep 5
        elapsed=$((elapsed + 5))
    done
    log_warn "Pod ${pod_id} did not reach ${target_status} within ${max_wait}s."
    return 1
}

# Run a script on a pod via SSH
run_remote() {
    local pod_id="$1"
    local script="$2"
    local ssh_info host port
    ssh_info=$(pod_ssh_details "$pod_id")
    if [[ -z "$ssh_info" ]]; then
        log_error "Could not determine SSH details for pod ${pod_id}."
        return 1
    fi
    host=$(echo "$ssh_info" | awk '{print $1}')
    port=$(echo "$ssh_info" | awk '{print $2}')
    log_info "SSH: ssh root@${host} -p ${port} -i ${SSH_KEY}"
    # Retry until SSH daemon accepts connections (port visible != daemon ready)
    local max_wait=60 elapsed=0
    until ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 -o BatchMode=yes \
        -i "$SSH_KEY" -p "$port" "root@${host}" true < /dev/null 2> /dev/null; do
        if [[ $elapsed -ge $max_wait ]]; then
            log_error "SSH daemon on ${host}:${port} not ready after ${max_wait}s."
            return 1
        fi
        log_info "Waiting for SSH daemon on ${host}:${port}... (${elapsed}s)"
        sleep 5
        elapsed=$((elapsed + 5))
    done
    ssh -o StrictHostKeyChecking=no \
        -o ConnectTimeout=30 \
        -i "$SSH_KEY" \
        -p "$port" \
        "root@${host}" \
        "bash -s" <<< "$script"
}

# Build install script: install LM Studio and start the server (no model download)
build_install_script() {
    cat << 'INSTALL_EOF'
set -e
echo "[SETUP] Installing LM Studio..."
curl -fsSL https://lmstudio.ai/install.sh | bash
export PATH="/root/.lmstudio/bin:$PATH"
echo 'export PATH="/root/.lmstudio/bin:$PATH"' >> /root/.bashrc
echo "[SETUP] Installing CUDA runtime for LM Studio..."
lms runtime get llama.cpp-linux-x86_64-nvidia-cuda12-avx2 2>&1 \
    || lms runtime get llama.cpp-linux-x86_64-nvidia-cuda12-avx2 --allow-incompatible 2>&1 \
    || echo "[WARN] Could not download CUDA runtime."
sleep 3
CUDA_RUNTIME=$(lms runtime ls 2>/dev/null | awk '/nvidia-cuda/ {print $1}' | sort -V | tail -1)
if [[ -n "$CUDA_RUNTIME" ]]; then
    lms runtime select "$CUDA_RUNTIME" 2>&1 && echo "[SETUP] CUDA runtime selected: $CUDA_RUNTIME"
else
    # fallback: try selecting by known name pattern directly
    lms runtime select llama.cpp-linux-x86_64-nvidia-cuda12-avx2 2>&1 \
        && echo "[SETUP] CUDA runtime selected (fallback)." \
        || echo "[WARN] Could not select CUDA runtime."
fi
echo "[SETUP] Starting LM Studio server on port 1234..."
nohup lms server start --port 1234 --bind 0.0.0.0 > /var/log/lmstudio.log 2>&1 &
sleep 5
echo "[SETUP] LM Studio server log:"
cat /var/log/lmstudio.log
echo "[SETUP] Enabling justInTimeModelLoading..."
python3 -c "
import json
path = '/root/.lmstudio/.internal/http-server-config.json'
with open(path) as f:
    cfg = json.load(f)
cfg['justInTimeModelLoading'] = True
with open(path, 'w') as f:
    json.dump(cfg, f, indent=2)
"
echo "[SETUP] LM Studio server started (PID $!), log: /var/log/lmstudio.log"
INSTALL_EOF
}

# Build load script: download the model for a single pod, then load into memory
build_load_script() {
    local model="$1"
    local url="$2"
    local context_length="${3:-}"
    local gpu_offload="${4:-}"
    local filename
    filename=$(basename "$url")
    # Write Python finder to file on the remote host to avoid complex inline quoting.
    # Note: ${model}, ${filename}, ${url} are expanded here (local), \$ is kept literal.
    cat << LOAD_EOF
set -e
export PATH="/root/.lmstudio/bin:\$PATH"
mkdir -p "\$HOME/.lmstudio/models/${model}"
if [[ -f "\$HOME/.lmstudio/models/${model}/${filename}" ]]; then
    echo "[LOAD] Model already downloaded, skipping: ${filename}"
else
    echo "[LOAD] Downloading: ${filename}"
    if command -v aria2c &>/dev/null || apt-get install -y -qq aria2 &>/dev/null; then
        aria2c -x 16 -s 16 --file-allocation=none \
            --console-log-level=notice --summary-interval=5 \
            -d "\$HOME/.lmstudio/models/${model}" -o "${filename}" "${url}"
    else
        curl -L --progress-bar -C - -o "\$HOME/.lmstudio/models/${model}/${filename}" "${url}"
    fi
    echo "[LOAD] Download complete."
    echo "[LOAD] Waiting for LM Studio to index the model..."
    sleep 10
fi
echo "[LOAD] Looking up model ID..."
model_id=\$(export PATH="/root/.lmstudio/bin:\$PATH" && lms ls 2>/dev/null | awk 'NR>1 && NF>0 {print \$1}' | python3 -c "
import sys
needle = sys.argv[1].lower().replace('.gguf', '')
lines = [l.strip() for l in sys.stdin if l.strip()]
matches = [(l, len(l)) for l in lines if l.lower() in needle]
if matches:
    print(max(matches, key=lambda x: x[1])[0])
else:
    print('[ERROR] No matching model ID. Available:', file=sys.stderr)
    [print(' ', l, file=sys.stderr) for l in lines]
    sys.exit(1)
" "${filename}") || {
    echo "[ERROR] Could not resolve model ID for ${filename}"
    exit 1
}
rm -f /tmp/find_model_id.py
echo "[LOAD] Resolved model ID: \${model_id}"
already_loaded=\$(curl -sf http://localhost:1234/api/v0/models | python3 -c "
import sys, json
data = json.load(sys.stdin).get('data', [])
model_id = sys.argv[1]
print('yes' if any(m.get('id','') == model_id and m.get('state') == 'loaded' for m in data) else 'no')
" "\${model_id}" 2>/dev/null || echo 'no')
if [[ "\${already_loaded}" == "yes" ]]; then
    echo "[LOAD] Model already loaded, skipping."
else
    load_args=("\${model_id}")
    if [[ -n "${context_length}" ]]; then
        load_args+=(--context-length "${context_length}")
        echo "[LOAD] Context length: ${context_length}"
    fi
    if [[ -n "${gpu_offload}" && "${gpu_offload}" != "none" ]]; then
        load_args+=(--gpu "${gpu_offload}")
        echo "[LOAD] GPU offload: ${gpu_offload}"
    fi
    # disconnect stdin so lms load does not hang on interactive prompts
    # note: lms load may return non-zero even on success, so || true to avoid set -e abort
    lms load "\${load_args[@]}" < /dev/null || true
    # verify via lms ps (shows actual loaded context, unlike the v0 API which returns max_context_length)
    # lms ps columns: IDENTIFIER MODEL STATUS SIZE_NUM SIZE_UNIT CONTEXT DEVICE TTL
    actual_ctx=\$(lms ps 2>/dev/null | awk -v id="\${model_id}" '\$1 == id {print \$6}')
    if [[ -z "\${actual_ctx}" ]]; then
        echo "[ERROR] Model failed to load (not found in lms ps output)"
        exit 1
    fi
    echo "[LOAD] Model loaded."
    echo "[LOAD] Loaded context length: \${actual_ctx}"
fi
LOAD_EOF
}

# -------------------------------------------------------------------
# pod create helper — uses RunPod GraphQL API (podFindAndDeployOnDemand) directly.
# runpodctl always pins the same machine; the GraphQL API searches the full pool like the GUI.
# Retries up to 10 times with short fixed delay.
# Prints pod_id to stdout; all log output to stderr.
# -------------------------------------------------------------------
_create_pod_with_fallback() {
    local name="$1" gpu="$2" hdd="$3"

    # build JSON payload via python3 to handle all escaping correctly
    local payload
    payload=$(python3 -c "
import json, sys
name        = sys.argv[1]
gpu         = sys.argv[2]
hdd         = int(sys.argv[3])
image       = sys.argv[4]
docker_args = sys.argv[5]
pubkey      = sys.argv[6]

mutation = '''
mutation {
  podFindAndDeployOnDemand(input: {
    cloudType: SECURE,
    gpuCount: 1,
    gpuTypeId: ''' + json.dumps(gpu) + ''',
    name: ''' + json.dumps(name) + ''',
    imageName: ''' + json.dumps(image) + ''',
    containerDiskInGb: ''' + str(hdd) + ''',
    volumeInGb: 0,
    minVcpuCount: 2,
    minMemoryInGb: 15,
    ports: \"22/tcp,1234/http\",
    dockerArgs: ''' + json.dumps(docker_args) + ''',
    env: [{key: \"MY_SSH_PUBLIC_KEY\", value: ''' + json.dumps(pubkey) + '''}]
  }) {
    id
    machineId
  }
}
'''
print(json.dumps({'query': mutation}))
" "$name" "$gpu" "$hdd" "$IMAGE" "$SSH_DAEMON_ARGS" "$SSH_PUBKEY")

    local max_attempts=10 attempt
    for ((attempt = 1; attempt <= max_attempts; attempt++)); do
        log_info "  Attempt ${attempt}/${max_attempts}: calling RunPod GraphQL API..." >&2
        local response pod_id err_msg
        response=$(runpod_api "$payload" 2>&1) || true
        pod_id=$(echo "$response" | python3 -c "
import sys, json
try:
    d = json.load(sys.stdin)
    print(d['data']['podFindAndDeployOnDemand']['id'])
except Exception:
    pass
" 2> /dev/null || true)
        if [[ -n "$pod_id" ]]; then
            echo "$pod_id"
            return 0
        fi
        err_msg=$(echo "$response" | python3 -c "
import sys, json
try:
    d = json.load(sys.stdin)
    errs = d.get('errors', [])
    print(errs[0]['message'] if errs else 'unknown error')
except Exception:
    print(sys.stdin.read()[:200])
" 2> /dev/null || echo "unknown error")
        if [[ $attempt -lt $max_attempts ]]; then
            log_warn "Attempt ${attempt}/${max_attempts} failed: ${err_msg}" >&2
            log_info "Retrying in 5s..." >&2
            sleep 5
        else
            log_error "All ${max_attempts} attempts failed: ${err_msg}" >&2
        fi
    done
    return 1
}

# -------------------------------------------------------------------
# create
# -------------------------------------------------------------------

# Check that all GPUs from config are available via runpodctl get cloud.
# Aborts if any GPU is not found in the cloud listing.
check_gpu_availability() {
    log_info "Checking GPU availability..."
    local cloud_list
    cloud_list=$(runpodctl get cloud 2> /dev/null) || {
        log_error "Could not fetch GPU list from RunPod."
        exit 1
    }
    local pod_count all_ok=true
    pod_count=$(echo "$CONFIG_JSON" | jq '.pods | length')
    local i
    for ((i = 0; i < pod_count; i++)); do
        local gpu
        gpu=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].gpu")
        if echo "$cloud_list" | grep -qF "$gpu"; then
            log_ok "  GPU available: ${gpu}"
        else
            log_error "  GPU NOT available: ${gpu}"
            all_ok=false
        fi
    done
    if [[ "$all_ok" == false ]]; then
        echo ""
        log_error "One or more GPUs are unavailable. Aborting."
        log_info "Available GPUs:"
        echo "$cloud_list" | awk 'NR>1 {
            line = $0
            sub(/^[0-9]+x /, "", line)
            gpu = substr(line, 1, 31)
            gsub(/[[:space:]]+$/, "", gpu)
            print "  " gpu
        }'
        exit 1
    fi
}

cmd_create() {
    local existing
    existing=$(our_pods_json | jq -r '.[].name' 2> /dev/null || true)
    if [[ -n "$existing" ]]; then
        log_error "The following pods already exist:"
        echo "$existing" | sed 's/^/  /'
        log_error "Run './runpod.sh delete' first."
        exit 1
    fi

    check_gpu_availability

    local pod_count
    pod_count=$(echo "$CONFIG_JSON" | jq '.pods | length')
    log_info "Creating ${pod_count} pods from $(basename "$CONFIG")..."
    load_ssh_pubkey

    declare -a pod_ids=()

    # Delete all pods created so far and exit
    rollback() {
        log_warn "Rolling back: deleting ${#pod_ids[@]} created pod(s)..."
        for rollback_id in "${pod_ids[@]}"; do
            runpodctl remove pod "$rollback_id" 2>&1 || true
            log_ok "  Rolled back pod ${rollback_id}."
        done
        exit 1
    }

    # --- Step 1: create all pods ---
    local i
    for ((i = 0; i < pod_count; i++)); do
        local name gpu model hdd
        model=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].model")
        name=$(pod_name_from_model "$model")
        gpu=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].gpu")
        hdd=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].hdd // 100")

        log_info "Creating pod $((i + 1))/${pod_count}: ${name} | ${gpu} | ${hdd} GB"
        local pod_id
        pod_id=$(_create_pod_with_fallback "$name" "$gpu" "$hdd") || {
            log_error "Pod $((i + 1)) could not be created."
            rollback
        }
        log_ok "Pod $((i + 1)) created: ${pod_id}"
        pod_ids+=("$pod_id")
    done

    # --- Step 2: wait for RUNNING ---
    echo ""
    log_info "Waiting for all pods to reach RUNNING..."
    for pod_id in "${pod_ids[@]}"; do
        wait_for_pod "$pod_id" || {
            log_error "Pod ${pod_id} did not reach RUNNING."
            rollback
        }
    done

    # --- Step 3: check SSH reachability ---
    echo ""
    log_info "Checking SSH reachability..."
    for pod_id in "${pod_ids[@]}"; do
        local ssh_info
        ssh_info=$(pod_ssh_details "$pod_id") || {
            log_error "Pod ${pod_id} is not reachable via SSH."
            rollback
        }
        local host port
        host=$(echo "$ssh_info" | awk '{print $1}')
        port=$(echo "$ssh_info" | awk '{print $2}')
        log_ok "  Pod ${pod_id}: ssh root@${host} -p ${port}"
    done

    # --- Step 4: install LM Studio + start server ---
    echo ""
    log_info "Installing LM Studio and starting server on all pods..."
    local install_script
    install_script=$(build_install_script)
    for ((i = 0; i < pod_count; i++)); do
        local name model
        model=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].model")
        name=$(pod_name_from_model "$model")
        log_info "Installing on ${name} (${pod_ids[$i]})..."
        run_remote "${pod_ids[$i]}" "$install_script" || {
            log_error "Install failed for ${name} (${pod_ids[$i]})."
            rollback
        }
        log_ok "LM Studio installed and server started on ${name}."
    done

    # --- Summary ---
    echo ""
    log_ok "All pods ready. Summary:"
    printf "  %-30s %-20s %s\n" "Name" "Pod ID" "GPU"
    printf "  %-30s %-20s %s\n" "----" "------" "---"
    for ((i = 0; i < pod_count; i++)); do
        local name gpu
        name=$(pod_name_from_model "$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].model")")
        gpu=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].gpu")
        printf "  %-30s %-20s %s\n" "$name" "${pod_ids[$i]}" "$gpu"
    done
    echo ""
    log_info "LM Studio endpoint pattern: https://<pod-id>-1234.proxy.runpod.net"
    log_info "Run './runpod.sh load' to download the models onto the pods."

    echo ""
}

# -------------------------------------------------------------------
# load
# -------------------------------------------------------------------
cmd_load() {
    local pods_json count
    pods_json=$(our_pods_json) || pods_json='[]'
    count=$(echo "$pods_json" | jq 'length')
    if [[ "$count" -eq 0 ]]; then
        log_error "No running pods found. Run './runpod.sh create' first."
        exit 1
    fi

    local pod_count
    pod_count=$(echo "$CONFIG_JSON" | jq '.pods | length')
    log_info "Loading models onto ${count} pod(s)..."

    local i
    for ((i = 0; i < pod_count; i++)); do
        local model url name pod_id
        model=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].model")
        url=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].url")
        name=$(pod_name_from_model "$model")

        pod_id=$(echo "$pods_json" | jq -r --arg n "$name" '.[] | select(.name == $n) | .id' | head -1)
        if [[ -z "$pod_id" ]]; then
            log_error "No running pod found for '${name}'. Skipping."
            continue
        fi

        local context_length gpu_offload
        context_length=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].context_length // \"\"")
        gpu_offload=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].gpu_offload // \"\"")
        log_info "Downloading model on ${name} (${pod_id})..."
        local load_script
        load_script=$(build_load_script "$model" "$url" "$context_length" "$gpu_offload")
        run_remote "$pod_id" "$load_script" || {
            log_error "Model download failed for ${name} (${pod_id})."
            exit 1
        }
        log_ok "Model loaded on ${name}."
    done

    echo ""
    log_ok "All models loaded."
}

# -------------------------------------------------------------------
# unload
# -------------------------------------------------------------------
cmd_unload() {
    local pods_json count
    pods_json=$(our_pods_json) || pods_json='[]'
    count=$(echo "$pods_json" | jq 'length')
    if [[ "$count" -eq 0 ]]; then
        log_error "No running pods found."
        exit 1
    fi

    local pod_count
    pod_count=$(echo "$CONFIG_JSON" | jq '.pods | length')
    log_info "Unloading models from ${count} pod(s)..."

    local i
    for ((i = 0; i < pod_count; i++)); do
        local model name pod_id
        model=$(echo "$CONFIG_JSON" | jq -r ".pods[${i}].model")
        name=$(pod_name_from_model "$model")

        pod_id=$(echo "$pods_json" | jq -r --arg n "$name" '.[] | select(.name == $n) | .id' | head -1)
        if [[ -z "$pod_id" ]]; then
            log_warn "No running pod found for '${name}'. Skipping."
            continue
        fi

        log_info "Unloading models on ${name} (${pod_id})..."
        run_remote "$pod_id" 'export PATH="/root/.lmstudio/bin:$PATH" && lms unload --all' || {
            log_error "Unload failed for ${name} (${pod_id})."
            exit 1
        }
        log_ok "Models unloaded on ${name}."
    done

    echo ""
    log_ok "All models unloaded."
}

# -------------------------------------------------------------------
# delete
# -------------------------------------------------------------------
cmd_delete() {
    log_info "Fetching pods..."
    local pods_json count
    pods_json=$(our_pods_json) || pods_json='[]'
    count=$(echo "$pods_json" | jq 'length')
    if [[ "$count" -eq 0 ]]; then
        log_warn "No configured pods found. Nothing to delete."
        return 0
    fi
    log_info "Found ${count} pod(s) to terminate."
    while read -r pod; do
        local pod_id pod_name
        pod_id=$(echo "$pod" | jq -r '.id')
        pod_name=$(echo "$pod" | jq -r '.name')
        log_info "Terminating pod '${pod_name}' (${pod_id})..."
        if runpodctl remove pod "$pod_id" 2>&1; then
            log_ok "Pod '${pod_name}' (${pod_id}) terminated."
        else
            log_warn "Could not terminate '${pod_name}' (${pod_id}). Remove manually."
        fi
    done < <(echo "$pods_json" | jq -c '.[]')
}

# -------------------------------------------------------------------
# status
# -------------------------------------------------------------------
cmd_status() {
    log_info "Fetching pods..."
    local pods_json count
    pods_json=$(our_pods_json) || pods_json='[]'
    count=$(echo "$pods_json" | jq 'length')
    if [[ "$count" -eq 0 ]]; then
        log_warn "No configured pods found."
        return 0
    fi

    local pod_count
    pod_count=$(echo "$CONFIG_JSON" | jq '.pods | length')

    echo ""
    while read -r pod; do
        local pod_id pod_name pod_status_val
        pod_id=$(echo "$pod" | jq -r '.id')
        pod_name=$(echo "$pod" | jq -r '.name')
        pod_status_val=$(echo "$pod" | jq -r '.desiredStatus')

        echo -e "${CYAN}=== ${pod_name} (${pod_id}) ===${NC}"

        # --- Pod running? ---
        if [[ "$pod_status_val" == "RUNNING" ]]; then
            log_ok "  Pod:       RUNNING"
        else
            log_warn "  Pod:       ${pod_status_val}"
        fi

        # --- SSH details ---
        local ssh_info host port
        ssh_info=$(pod_ssh_details "$pod_id" 2> /dev/null) || ssh_info=''
        if [[ -n "$ssh_info" ]]; then
            host=$(echo "$ssh_info" | awk '{print $1}')
            port=$(echo "$ssh_info" | awk '{print $2}')
            echo "             ssh root@${host} -p ${port} -i ${SSH_KEY}"
        fi

        # --- LM Studio endpoint reachable? ---
        local lmstudio_url="https://${pod_id}-1234.proxy.runpod.net"
        local http_code
        http_code=$(curl -sf --max-time 10 -o /dev/null -w "%{http_code}" "${lmstudio_url}/api/v0/models" 2> /dev/null || echo "000")
        if [[ "$http_code" == "200" ]]; then
            log_ok "  LM Studio: reachable (${lmstudio_url})"
        else
            log_warn "  LM Studio: not reachable (${lmstudio_url}, HTTP ${http_code})"
        fi

        # --- Model loaded? (via lms ps) ---
        local ps_output
        ps_output=$(run_remote "$pod_id" 'export PATH="/root/.lmstudio/bin:$PATH" && lms ps' 2> /dev/null || echo '')
        if echo "$ps_output" | grep -q 'No models'; then
            log_warn "  Model:     not loaded"
        elif [[ -n "$ps_output" ]]; then
            log_ok "  Model:     $(echo "$ps_output" | grep -v '^$' | tail -n +2 | head -5 | tr '\n' ' ')"
        else
            log_warn "  Model:     unknown"
        fi

        echo ""
    done < <(echo "$pods_json" | jq -c '.[]')
}

# -------------------------------------------------------------------
# restart: stop and restart lm studio server on all running pods
# -------------------------------------------------------------------
cmd_restart() {
    local pods_json count
    pods_json=$(our_pods_json) || pods_json='[]'
    count=$(echo "$pods_json" | jq 'length')
    if [[ "$count" -eq 0 ]]; then
        log_error "No running pods found."
        exit 1
    fi

    log_info "Restarting LM Studio server on ${count} pod(s)..."

    while read -r pod; do
        local pod_id pod_name
        pod_id=$(echo "$pod" | jq -r '.id')
        pod_name=$(echo "$pod" | jq -r '.name')
        log_info "Restarting server on ${pod_name} (${pod_id})..."
        run_remote "$pod_id" 'export PATH="/root/.lmstudio/bin:$PATH" && lms server stop 2>/dev/null || true && sleep 2 && nohup lms server start --port 1234 --bind 0.0.0.0 > /var/log/lmstudio.log 2>&1 & sleep 5 && echo "[RESTART] Server log:" && cat /var/log/lmstudio.log && echo "[RESTART] Server restarted (PID $!)"' || {
            log_error "Restart failed for ${pod_name} (${pod_id})."
            exit 1
        }
        log_ok "LM Studio server restarted on ${pod_name}."
    done < <(echo "$pods_json" | jq -c '.[]')

    echo ""
    log_ok "Done. Run './runpod.sh load' to reload models."
}

# -------------------------------------------------------------------
# Entry point
# -------------------------------------------------------------------
ACTION="${1:-}"

case "$ACTION" in
    create) cmd_create ;;
    load) cmd_load ;;
    unload) cmd_unload ;;
    delete) cmd_delete ;;
    status) cmd_status ;;
    restart) cmd_restart ;;
    *)
        echo "Usage: $0 {create|load|unload|delete|status|restart}"
        echo ""
        echo "  create   Check GPUs, create pods, install LM Studio, start server"
        echo "  load     Download models onto all running pods"
        echo "  unload   Unload all models from running pods"
        echo "  delete   Terminate all pods"
        echo "  status   Show current pod status"
        echo "  restart  Restart LM Studio server with updated parameters"
        echo ""
        exit 1
        ;;
esac
