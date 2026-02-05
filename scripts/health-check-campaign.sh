#!/bin/bash
# Health Check Campaign - DI Form Buddy
# Runs health checks across DI platform sites via SSH
#
# Usage:
#   ./scripts/health-check-campaign.sh                    # 10 sites/pod, all pods
#   ./scripts/health-check-campaign.sh --sample=5         # 5 sites/pod
#   ./scripts/health-check-campaign.sh --pods=1,2,3       # specific pods only
#   ./scripts/health-check-campaign.sh --sample=all       # full coverage
#   ./scripts/health-check-campaign.sh --dry-run          # preview only

set -euo pipefail

# ─── Configuration ────────────────────────────────────────────────────────────

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
DEALERS_JSON="${DEALERS_JSON:-$HOME/code/dealerinspire/feature-dev-shared-scripts/devtools-cli/.cache/dealers.json}"
RESULTS_DIR="$PROJECT_DIR/results/health-checks/$(date +%Y-%m-%d)"
TOOL_PATH="$PROJECT_DIR/di-form-buddy.php"

# Defaults
SAMPLE_SIZE=10
PODS=""
DRY_RUN=false
PARALLEL=1

# SSH settings
SSH_USER="${SSH_USERNAME:-nhart}"
SSH_TIMEOUT=30
SSH_RETRIES=3

# sshpass expects SSHPASS env var, setup.sh uses SSH_PASSWORD
export SSHPASS="${SSHPASS:-$SSH_PASSWORD}"

# ─── Logging ──────────────────────────────────────────────────────────────────

log() {
    echo "[Health Check] $(date '+%Y-%m-%d %H:%M:%S') $*"
}

log_error() {
    echo "[Health Check] $(date '+%Y-%m-%d %H:%M:%S') ERROR: $*" >&2
}

# ─── Argument Parsing ─────────────────────────────────────────────────────────

usage() {
    cat <<EOF
Usage: $(basename "$0") [OPTIONS]

Options:
  --sample=N      Sites per pod (default: 10, use 'all' for full coverage)
  --pods=N,M,...  Specific pods to check (default: all live pods 1-47)
  --dry-run       Preview what would be checked without executing
  --parallel=N    Run N pods in parallel (default: 1)
  --help          Show this help message

Environment:
  SSH_USERNAME    SSH user for pods (default: nhart)
  SSH_PASSWORD    SSH password (required, set via setup.sh)
  DEALERS_JSON    Path to dealers.json (default: devtools cache)

Examples:
  $(basename "$0")                          # 10 sites/pod, all pods
  $(basename "$0") --sample=5 --pods=1,2    # 5 sites on pods 1 and 2
  $(basename "$0") --sample=all --pods=1    # all sites on pod 1
  $(basename "$0") --dry-run                # preview only
EOF
    exit 0
}

parse_args() {
    while [[ $# -gt 0 ]]; do
        case "$1" in
            --sample=*)
                SAMPLE_SIZE="${1#*=}"
                ;;
            --pods=*)
                PODS="${1#*=}"
                ;;
            --dry-run)
                DRY_RUN=true
                ;;
            --parallel=*)
                PARALLEL="${1#*=}"
                ;;
            --help|-h)
                usage
                ;;
            *)
                log_error "Unknown option: $1"
                usage
                ;;
        esac
        shift
    done
}

# ─── Validation ───────────────────────────────────────────────────────────────

validate_environment() {
    if [[ ! -f "$DEALERS_JSON" ]]; then
        log_error "dealers.json not found at: $DEALERS_JSON"
        log_error "Run: devtools dash dealers -f"
        exit 1
    fi

    if [[ ! -f "$TOOL_PATH" ]]; then
        log_error "di-form-buddy.php not found at: $TOOL_PATH"
        exit 1
    fi

    if [[ -z "${SSH_PASSWORD:-}" ]] && [[ -z "${SSHPASS:-}" ]]; then
        log_error "SSH_PASSWORD or SSHPASS not set. Run: source setup.sh"
        exit 1
    fi

    if ! command -v jq &>/dev/null; then
        log_error "jq is required but not installed"
        exit 1
    fi

    if ! command -v sshpass &>/dev/null; then
        log_error "sshpass is required but not installed"
        log_error "Install with: brew install hudochenkov/sshpass/sshpass"
        exit 1
    fi
}

# ─── Dealer Data ──────────────────────────────────────────────────────────────

get_live_sites_for_pod() {
    local pod_num="$1"
    local limit="$2"

    local jq_filter='.[] | select(.has_production_site == 1 and .pod == '"$pod_num"') | {domain, slug, oem_name, pod}'

    if [[ "$limit" == "all" ]]; then
        jq -c "$jq_filter" "$DEALERS_JSON"
    else
        jq -c "$jq_filter" "$DEALERS_JSON" | head -n "$limit"
    fi
}

get_live_pods() {
    if [[ -n "$PODS" ]]; then
        # User specified pods
        echo "$PODS" | tr ',' '\n'
    else
        # All live pods (1-47, excluding 0)
        jq -r '[.[] | select(.has_production_site == 1 and .pod > 0) | .pod] | unique | sort | .[]' "$DEALERS_JSON"
    fi
}

count_sites_on_pod() {
    local pod_num="$1"
    jq '[.[] | select(.has_production_site == 1 and .pod == '"$pod_num"')] | length' "$DEALERS_JSON"
}

# ─── SSH Execution ────────────────────────────────────────────────────────────

run_on_pod() {
    local pod_num="$1"
    local domain="$2"
    local retry=0
    local result=""

    local pod_host="deploy.pod${pod_num}.dealerinspire.com"
    local remote_cmd="php $TOOL_PATH --site=$domain --health-check --all --output=json"

    while [[ $retry -lt $SSH_RETRIES ]]; do
        result=$(sshpass -e ssh -o StrictHostKeyChecking=no -o ConnectTimeout=$SSH_TIMEOUT \
            "$SSH_USER@$pod_host" "$remote_cmd" 2>/dev/null) && break
        ((retry++))
        [[ $retry -lt $SSH_RETRIES ]] && sleep 2
    done

    if [[ -z "$result" ]]; then
        # SSH failed, return error JSON
        echo '{"mode":"health_check_all","site":"'"$domain"'","status":"fail","error":"ssh_failed","message":"SSH connection failed after '"$SSH_RETRIES"' retries"}'
    else
        echo "$result"
    fi
}

# ─── Results Processing ───────────────────────────────────────────────────────

init_results_dir() {
    mkdir -p "$RESULTS_DIR/by-pod"
    log "Results directory: $RESULTS_DIR"
}

append_result() {
    local result="$1"
    local pod_num="$2"

    echo "$result" >> "$RESULTS_DIR/all-results.jsonl"
    echo "$result" >> "$RESULTS_DIR/by-pod/pod${pod_num}.jsonl"

    # Track failures separately
    local status
    status=$(echo "$result" | jq -r '.status // "unknown"')
    if [[ "$status" != "pass" ]]; then
        echo "$result" >> "$RESULTS_DIR/failures.jsonl"
    fi
}

generate_summary() {
    local total_sites="$1"
    local total_passed="$2"
    local total_partial="$3"
    local total_failed="$4"
    local total_forms="$5"
    local forms_passed="$6"

    cat > "$RESULTS_DIR/summary.json" <<EOF
{
  "campaign": "health_check",
  "date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "sample_size": "$SAMPLE_SIZE",
  "pods_checked": $(get_live_pods | wc -l | tr -d ' '),
  "sites": {
    "total": $total_sites,
    "passed": $total_passed,
    "partial": $total_partial,
    "failed": $total_failed,
    "pass_rate": $(echo "scale=2; $total_passed * 100 / $total_sites" | bc 2>/dev/null || echo "0")
  },
  "forms": {
    "total": $total_forms,
    "passed": $forms_passed,
    "pass_rate": $(echo "scale=2; $forms_passed * 100 / $total_forms" | bc 2>/dev/null || echo "0")
  }
}
EOF
    log "Summary written to: $RESULTS_DIR/summary.json"
}

# ─── Main Campaign Logic ──────────────────────────────────────────────────────

run_campaign() {
    init_results_dir

    local pods
    pods=$(get_live_pods)
    local pod_count
    pod_count=$(echo "$pods" | wc -l | tr -d ' ')

    # Calculate total sites to check
    local total_to_check=0
    for pod in $pods; do
        local pod_sites
        pod_sites=$(count_sites_on_pod "$pod")
        if [[ "$SAMPLE_SIZE" == "all" ]]; then
            total_to_check=$((total_to_check + pod_sites))
        else
            local check_count=$((pod_sites < SAMPLE_SIZE ? pod_sites : SAMPLE_SIZE))
            total_to_check=$((total_to_check + check_count))
        fi
    done

    log "Source: dealers.json"
    log "Sample: $SAMPLE_SIZE sites per pod"
    log "Pods: $pod_count"
    log "Total sites to check: $total_to_check"

    if [[ "$DRY_RUN" == true ]]; then
        log "DRY RUN - Preview of sites to check:"
        echo ""
        for pod in $pods; do
            local sites
            sites=$(get_live_sites_for_pod "$pod" "$SAMPLE_SIZE")
            local site_count
            site_count=$(echo "$sites" | wc -l | tr -d ' ')
            echo "Pod $pod ($site_count sites):"
            echo "$sites" | jq -r '.domain' | head -5 | sed 's/^/  /'
            [[ $site_count -gt 5 ]] && echo "  ... and $((site_count - 5)) more"
            echo ""
        done
        exit 0
    fi

    log "═══════════════════════════════════════════════════════"

    # Tracking variables
    local grand_total_sites=0
    local grand_total_passed=0
    local grand_total_partial=0
    local grand_total_failed=0
    local grand_total_forms=0
    local grand_forms_passed=0

    for pod in $pods; do
        local sites
        sites=$(get_live_sites_for_pod "$pod" "$SAMPLE_SIZE")
        local site_count
        site_count=$(echo "$sites" | wc -l | tr -d ' ')

        log "Pod $pod: $site_count sites"

        local pod_passed=0
        local pod_partial=0
        local pod_failed=0
        local pod_forms=0
        local pod_forms_passed=0
        local current=0

        while IFS= read -r site_json; do
            ((current++))
            local domain
            domain=$(echo "$site_json" | jq -r '.domain')

            # Run health check
            local result
            result=$(run_on_pod "$pod" "$domain")

            # Parse result
            local status
            status=$(echo "$result" | jq -r '.status // "fail"')
            local forms_checked
            forms_checked=$(echo "$result" | jq -r '.forms_checked // 0')
            local forms_pass
            forms_pass=$(echo "$result" | jq -r '.forms_passed // 0')

            # Update counters
            pod_forms=$((pod_forms + forms_checked))
            pod_forms_passed=$((pod_forms_passed + forms_pass))

            case "$status" in
                pass)
                    ((pod_passed++))
                    log "  [$current/$site_count] $domain — PASS ($forms_pass/$forms_checked forms)"
                    ;;
                partial)
                    ((pod_partial++))
                    log "  [$current/$site_count] $domain — PARTIAL ($forms_pass/$forms_checked forms)"
                    ;;
                *)
                    ((pod_failed++))
                    local error_msg
                    error_msg=$(echo "$result" | jq -r '.error // .message // "unknown"')
                    log "  [$current/$site_count] $domain — FAIL ($error_msg)"
                    ;;
            esac

            # Save result
            append_result "$result" "$pod"

        done <<< "$sites"

        # Pod summary
        local pod_total=$((pod_passed + pod_partial + pod_failed))
        local pod_healthy=$((pod_passed + pod_partial))
        log "Pod $pod complete: $pod_healthy/$pod_total healthy"
        log "─────────────────────────────────────────────────────"

        # Update grand totals
        grand_total_sites=$((grand_total_sites + pod_total))
        grand_total_passed=$((grand_total_passed + pod_passed))
        grand_total_partial=$((grand_total_partial + pod_partial))
        grand_total_failed=$((grand_total_failed + pod_failed))
        grand_total_forms=$((grand_total_forms + pod_forms))
        grand_forms_passed=$((grand_forms_passed + pod_forms_passed))
    done

    # Final summary
    log "═══════════════════════════════════════════════════════"
    log "CAMPAIGN COMPLETE"
    log "  Sites checked: $grand_total_sites"
    log "  Fully healthy: $grand_total_passed"
    log "  Partial: $grand_total_partial"
    log "  Failed: $grand_total_failed"
    log "  Forms checked: $grand_total_forms"
    log "  Forms passing: $grand_forms_passed"
    log "Results: $RESULTS_DIR/"

    # Generate summary JSON
    generate_summary "$grand_total_sites" "$grand_total_passed" "$grand_total_partial" \
                     "$grand_total_failed" "$grand_total_forms" "$grand_forms_passed"
}

# ─── Entry Point ──────────────────────────────────────────────────────────────

main() {
    parse_args "$@"
    validate_environment
    run_campaign
}

main "$@"
