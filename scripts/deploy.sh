#!/usr/bin/env bash
# =============================================================
# deploy.sh — ONE-CLICK Deployment & Setup Pipeline
#             for M-Pesa Analyzer Platform (Monorepo)
#
# Usage:  bash scripts/deploy.sh
#         (run from the repository root)
#
# 13-Step Automated Pipeline:
#   1. Detect Dynamic IP (Public / LAN Fallback)
#   2. Pre-flight System & Resource Check (OS, CPU, RAM, Disk)
#   3. Git Working Tree Sanity Check & Pull
#   4. Write / Patch .env Configuration
#   5. Stop Old Containers
#   6. Build & Start All Containers (MySQL, Web, ML)
#   7. Wait for MySQL Health (:3306)
#   8. Run Database Migrations & Seeders
#   9. Wait for ML Microservice Readiness (:9050 / :9021)
#  10. Wait for WebApp Server Readiness (:80 / :9002)
#  11. Container Permissions Enforcement (web/writable)
#  12. Housekeeping, Docker Storage & Cache Clearing
#  13. Clean Status Summary & Endpoints
# =============================================================
set -euo pipefail

# ── Colors & Formatting ───────────────────────────────────────
GREEN="\033[0;32m"; YELLOW="\033[1;33m"; RED="\033[0;31m"
CYAN="\033[0;36m"; BOLD="\033[1m"; RESET="\033[0m"

DEPLOY_START_TIME=$(date +%s)
SECTION_START_TIME=$DEPLOY_START_TIME
PREV_SECTION=""
declare -a STAGE_NAMES=()
declare -a STAGE_DURATIONS=()

format_duration() {
    local S=$1
    if [ "$S" -ge 60 ]; then
        local M=$((S / 60))
        local R=$((S % 60))
        echo "${M}m ${R}s"
    else
        echo "${S}s"
    fi
}

log()     { echo -e "${GREEN}[OK]${RESET}   $1"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET} $1"; }
err()     {
    local NOW=$(date +%s)
    local ELAPSED=$((NOW - DEPLOY_START_TIME))
    echo -e "${RED}[FAIL]${RESET} $1 (failed after $(format_duration $ELAPSED))"
    exit 1
}

section() {
    local NOW=$(date +%s)
    if [ -n "$PREV_SECTION" ]; then
        local DURATION=$((NOW - SECTION_START_TIME))
        echo -e "${CYAN}──> Completed: ${PREV_SECTION} in $(format_duration $DURATION)${RESET}"
        STAGE_NAMES+=("$PREV_SECTION")
        STAGE_DURATIONS+=("$DURATION")
    fi
    PREV_SECTION="$1"
    SECTION_START_TIME=$NOW
    echo -e "\n${CYAN}${BOLD}=== $1 ===${RESET}"
}

finish_deployment() {
    local NOW=$(date +%s)
    if [ -n "$PREV_SECTION" ]; then
        local DURATION=$((NOW - SECTION_START_TIME))
        echo -e "${CYAN}──> Completed: ${PREV_SECTION} in $(format_duration $DURATION)${RESET}"
        STAGE_NAMES+=("$PREV_SECTION")
        STAGE_DURATIONS+=("$DURATION")
        PREV_SECTION=""
    fi
    local TOTAL_DURATION=$((NOW - DEPLOY_START_TIME))
    echo ""
    echo -e "${GREEN}${BOLD}══════════════════════════════════════════════════════════════${RESET}"
    echo -e "${GREEN}${BOLD}   DEPLOYMENT TIMELINE & EXECUTION SUMMARY${RESET}"
    echo -e "${GREEN}${BOLD}══════════════════════════════════════════════════════════════${RESET}"
    for i in "${!STAGE_NAMES[@]}"; do
        printf "   %-48s : %s\n" "${STAGE_NAMES[$i]}" "$(format_duration ${STAGE_DURATIONS[$i]})"
    done
    echo -e "   ------------------------------------------------------------"
    printf "   ${BOLD}%-48s${RESET} : ${BOLD}%s (%ss)${RESET}\n" "Total Overall Deployment Time" "$(format_duration $TOTAL_DURATION)" "$TOTAL_DURATION"
    echo -e "${GREEN}${BOLD}══════════════════════════════════════════════════════════════${RESET}"
}

# ── Guard: must run from repository root ─────────────────────
[ -f "docker-compose.yml" ] || err "Run from the repository root (where docker-compose.yml lives)."

# ── 1. Detect Dynamic IP ──────────────────────────────────────
section "1. Detecting Dynamic IP"
DETECTED_IP=$(curl -s --max-time 5 ip.me 2>/dev/null | tr -d '[:space:]' || true)
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP=$(curl -s --max-time 5 ifconfig.me 2>/dev/null | tr -d '[:space:]' || true)
fi
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP=$(curl -s --max-time 5 icanhazip.com 2>/dev/null | tr -d '[:space:]' || true)
fi
# Fallback to local LAN IP if offline or no external public route
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP=$(hostname -I 2>/dev/null | awk '{print $1}' | tr -d '[:space:]' || true)
fi
# Ultimate fallback to localhost
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP="127.0.0.1"
fi
log "Dynamic Host IP: $DETECTED_IP"

# ── 2. Pre-flight System & Resource Check ─────────────────────
section "2. Pre-flight Disk & RAM Check"

# OS Specifications
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS_PRETTY="${PRETTY_NAME:-$NAME}"
else
    OS_PRETTY="$(uname -s)"
fi
KERNEL="$(uname -r)"
ARCH="$(uname -m)"

# CPU Specifications
CPU_CORES=$(nproc 2>/dev/null || echo "1")
CPU_MODEL=$(grep -m1 "model name" /proc/cpuinfo 2>/dev/null | sed -E "s/^model name\s*:\s*//" | tr -s " " || echo "Generic CPU")
[ -z "$CPU_MODEL" ] && CPU_MODEL="Generic CPU"

# RAM Specifications
TOTAL_MEM_MB=$(free -m | awk '/Mem:/ {print $2}')
USED_MEM_MB=$(free -m | awk '/Mem:/ {print $3}')
FREE_MEM_MB=$(free -m | awk '/Mem:/ {print ($7 != "" ? $7 : $4)}')
MEM_PCT=$(awk -v u="$USED_MEM_MB" -v t="$TOTAL_MEM_MB" 'BEGIN {if (t>0) printf "%.1f", (u/t)*100; else print "0"}')
MEM_FREE_PCT=$(awk -v f="$FREE_MEM_MB" -v t="$TOTAL_MEM_MB" 'BEGIN {if (t>0) printf "%.1f", (f/t)*100; else print "0"}')
TOTAL_MEM_GB=$(awk -v m="$TOTAL_MEM_MB" 'BEGIN {printf "%.2f GB", m/1024}')
USED_MEM_GB=$(awk -v m="$USED_MEM_MB" 'BEGIN {printf "%.2f GB", m/1024}')
FREE_MEM_GB=$(awk -v m="$FREE_MEM_MB" 'BEGIN {printf "%.2f GB", m/1024}')

# Disk Specifications on /
DISK_LINE=$(df -h / | awk 'NR>1 {print $(NF-4), $(NF-3), $(NF-2), $(NF-1), $(NF)}')
DISK_TOTAL=$(echo "$DISK_LINE" | awk '{print $1}')
DISK_USED=$(echo "$DISK_LINE" | awk '{print $2}')
DISK_FREE=$(echo "$DISK_LINE" | awk '{print $3}')
DISK_PCT=$(echo "$DISK_LINE" | awk '{print $4}')
DISK_FREE_MB=$(df -m / | awk 'NR>1 {print $(NF-2)}')

log "OS Environment   : ${OS_PRETTY} (${ARCH}, kernel ${KERNEL})"
log "CPU Architecture : ${CPU_MODEL} (${CPU_CORES} vCPU)"
log "Available Memory : ${FREE_MEM_MB}MB free of ${TOTAL_MEM_MB}MB"
log "RAM Breakdown    : Total: ${TOTAL_MEM_GB} | Used: ${USED_MEM_GB} (${MEM_PCT}%) | Free: ${FREE_MEM_GB} (${MEM_FREE_PCT}%)"
log "Available Disk   : ${DISK_FREE_MB}MB free on /"
log "Disk Breakdown   : Total: ${DISK_TOTAL} | Used: ${DISK_USED} (${DISK_PCT}) | Free: ${DISK_FREE} (${DISK_FREE_MB}MB)"

if [ "$FREE_MEM_MB" -lt 2500 ]; then
    warn "Free RAM is below 2.5 GB (${FREE_MEM_MB}MB). Local LLM inference may experience memory pressure."
fi

if [ "$DISK_FREE_MB" -lt 8000 ]; then
    warn "Free disk is below 8GB (${DISK_FREE_MB}MB free). Running proactive Docker cache prune..."
    docker builder prune -af >/dev/null 2>&1 || true
    docker image prune -f >/dev/null 2>&1 || true
    RECHECK_DISK=$(df -m / | awk 'NR>1 {print $(NF-2)}')
    log "Reclaimed disk space: ${RECHECK_DISK}MB free now"
fi

# ── 3. Git Working Tree Sanity Check & Pull ───────────────────
section "3. Git Working Tree Check & Pull"
if [ -d ".git" ]; then
    if [ -n "$(git status --porcelain 2>/dev/null)" ]; then
        warn "Uncommitted local changes detected in working tree. Stashing safely..."
        git stash save "deploy-auto-stash-$(date +%Y%m%d%H%M%S)" >/dev/null 2>&1 || true
    fi
    # If a remote exists, pull latest
    if git remote | grep -q "origin"; then
        log "Pulling latest changes from origin..."
        git pull origin main 2>&1 | tail -5 || warn "Could not pull from origin, continuing with local code..."
    else
        log "Working tree clean (local repository mode)"
    fi
fi

# ── 4. Write / Patch .env ─────────────────────────────────────
section "4. Writing .env Configuration"
if [ ! -f ".env" ]; then
    [ -f ".env.example" ] && cp .env.example .env && log "Created .env from .env.example" \
        || err ".env missing and no .env.example found — cannot continue"
fi

# Helper: upsert key=value in .env
set_env() {
    local K="$1" V="$2"
    if grep -q "^${K}=" .env; then
        sed -i "s|^${K}=.*|${K}=${V}|g" .env
    elif grep -q "^${K} *=" .env; then
        sed -i "s|^${K} *=.*|${K} = ${V}|g" .env
    else
        echo "${K}=${V}" >> .env
    fi
}

# Resolve Ports
WEB_PORT=$(grep "^WEB_PORT" .env 2>/dev/null | cut -d'=' -f2 | tr -d ' ' || echo "9002")
[ -z "$WEB_PORT" ] && WEB_PORT="9002"
ML_PORT=$(grep "^ML_MPESA_ANALYZER_API_PORT" .env 2>/dev/null | cut -d'=' -f2 | tr -d ' ' || echo "9021")
[ -z "$ML_PORT" ] && ML_PORT="9021"

# Dynamically patch baseURL with the detected host IP and WEB_PORT
set_env "app.baseURL" "http://${DETECTED_IP}:${WEB_PORT}/"
set_env "CI_ENVIRONMENT" "development"
set_env "DB_HOST" "mysql"
set_env "DB_PORT" "3306"
set_env "DB_NAME" "db_mpesa_analyzer"
set_env "DB_USER" "root"
set_env "ML_BACKEND_URL" "http://ml-mpesa-analyzer:9050"

log "app.baseURL               -> http://${DETECTED_IP}:${WEB_PORT}/"
log "ML_BACKEND_URL            -> http://ml-mpesa-analyzer:9050 (Internal network)"
log "DB_HOST                   -> mysql (Docker internal)"

# ── 5. Stop Old Containers ────────────────────────────────────
section "5. Stopping Old Containers"
docker compose down --remove-orphans 2>&1 | tail -3
log "Old containers stopped and networks cleaned"

# ── 6. Build & Start Containers ───────────────────────────────
section "6. Building & Starting All Containers"
docker compose up --build -d 2>&1 | tail -20
log "Containers created and started (mysql, web, ml)"

# Display Docker images and container runtime sizes
echo ""
echo -e "   ${BOLD}Docker Image Sizes:${RESET}"
docker images --filter "reference=*mpesa*" --filter "reference=*mysql*" --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}" 2>/dev/null | sed 's/^/   /' || true

echo ""
echo -e "   ${BOLD}Container Runtime Layer Sizes:${RESET}"
docker ps --size --filter "name=mpesa" --filter "name=shared-mysql" --format "table {{.Names}}\t{{.Status}}\t{{.Size}}" 2>/dev/null | sed 's/^/   /' || true

# ── 7. Wait for MySQL Health ──────────────────────────────────
section "7. Waiting for MySQL to Become Healthy"
printf "   Waiting for MySQL"
MYSQL_HEALTHY=false
for i in $(seq 1 40); do
    STATUS=$(docker inspect --format='{{.State.Health.Status}}' shared-mysql 2>/dev/null || echo "missing")
    if [ "$STATUS" = "healthy" ]; then
        echo ""
        log "MySQL is healthy and accepting connections"
        MYSQL_HEALTHY=true
        break
    fi
    printf "."
    sleep 3
done
$MYSQL_HEALTHY || { echo ""; warn "MySQL did not report healthy within timeout. Check: docker compose logs mysql"; }

# ── 8. Run Database Migrations & Seeding ──────────────────────
section "8. Running Database Migrations & Seeding"
if $MYSQL_HEALTHY; then
    docker compose exec -T web php spark migrate --all 2>&1 \
        && log "Database migrations executed successfully" \
        || warn "Migrations had notices or completed with warnings"

    log "Applying initial database seeders..."
    docker compose exec -T web php spark db:seed SuperAdminSeeder 2>&1 || true
    docker compose exec -T web php spark db:seed AllowedSendersSeeder 2>&1 || true
    docker compose exec -T web php spark db:seed MLControlsSeeder 2>&1 || true
    log "Seeders executed successfully"
else
    warn "Skipping migrations and seeding because MySQL is not yet healthy"
fi

# ── 9. Wait for ML Microservice Readiness (:9050 / :9021) ─────
section "9. Waiting for ML Microservice Ready (:9021)"
printf "   Checking ml-mpesa-analyzer status"
ML_READY=false
for i in $(seq 1 45); do
    ML_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:${ML_PORT}/health 2>/dev/null || echo "000")
    if [ "$ML_CODE" = "200" ]; then
        echo ""
        log "ML intelligence microservice is healthy (HTTP $ML_CODE)"
        ML_READY=true
        break
    fi
    printf "."
    sleep 3
done
$ML_READY || { echo ""; warn "ML microservice took longer to initialize. Check: docker compose logs ml"; }

# ── 10. Wait for Web Server Readiness (:80 / :9002) ───────────
section "10. Waiting for Web Server Ready (:${WEB_PORT})"
printf "   Waiting for WebApp"
WEB_READY=false
for i in $(seq 1 25); do
    CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:${WEB_PORT}/health 2>/dev/null || echo "000")
    if [[ "$CODE" =~ ^(200|301|302|403)$ ]]; then
        echo ""
        log "WebApp server is responding (HTTP $CODE)"
        WEB_READY=true
        break
    fi
    printf "."
    sleep 2
done
$WEB_READY || { echo ""; warn "Web server did not respond in time. Check: docker compose logs web"; }

# ── 11. Container Permissions Enforcement ─────────────────────
section "11. Setting Container Writable Directory Permissions"
docker compose exec -T web sh -c "
    mkdir -p writable/cache writable/logs writable/session writable/uploads/payloads writable/debugbar writable/backups && \
    chown -R www-data:www-data writable && \
    chmod -R 775 writable
" 2>&1 && log "Writable permissions enforced (www-data:775)" || warn "Failed to update writable permissions"

# ── 12. Housekeeping, Storage Metrics & Cache Clearing ────────
section "12. Housekeeping & Cache Clearing"
docker compose exec -T web php spark cache:clear 2>&1 || true
log "CodeIgniter 4 application cache cleared"

docker compose exec -T web sh -c "
    rm -rf writable/debugbar/* writable/tmp/* 2>/dev/null || true
" && log "Temporary runtime files and debugbar profiles cleared" || true

docker builder prune -af >/dev/null 2>&1 && log "Docker BuildKit build cache pruned" || true
docker image prune -f >/dev/null 2>&1 && log "Dangling Docker build layers pruned" || true

echo ""
echo -e "   ${BOLD}Docker Storage Footprint:${RESET}"
docker system df 2>/dev/null | sed 's/^/   /' || true

# ── 13. Container Status & Final Summary ──────────────────────
section "13. Deployment Summary & Health Verification"
docker compose ps

echo ""
FINAL_WEB=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:${WEB_PORT}/health 2>/dev/null || echo "000")

if [ "$FINAL_WEB" = "200" ]; then
    log "PLATFORM IS LIVE — ALL SYSTEMS OPERATIONAL"
    echo ""
    echo -e "  ${BOLD}WebApp Dashboard :${RESET}  http://${DETECTED_IP}:${WEB_PORT}/"
    echo -e "  ${BOLD}Admin ML Config  :${RESET}  http://${DETECTED_IP}:${WEB_PORT}/admin/ml"
    echo -e "  ${BOLD}ML API Swagger   :${RESET}  http://${DETECTED_IP}:${ML_PORT}/docs"
    echo -e "  ${BOLD}ML Health Probe  :${RESET}  http://${DETECTED_IP}:${ML_PORT}/health"
    echo -e "  ${BOLD}WebApp Health    :${RESET}  http://${DETECTED_IP}:${WEB_PORT}/health"
    echo ""
    finish_deployment

elif [ "$FINAL_WEB" = "500" ]; then
    warn "HTTP 500 detected on WebApp — dumping live error output..."
    echo -e "${RED}"
    curl -s http://localhost:${WEB_PORT}/health \
        | python3 -c "
import sys, json
try:
    d = json.load(sys.stdin)
    print('Error:', d.get('message',''))
    for f in d.get('trace', [])[:10]:
        print(' ', f.get('file','').split('/')[-1], ':', f.get('line',''), '->', f.get('function',''))
except Exception:
    sys.stdin = open('/dev/stdin')
    print(sys.stdin.read()[:2000])
" 2>/dev/null || true
    echo -e "${RESET}"
    echo ""
    echo -e "${YELLOW}Recent WebApp container logs:${RESET}"
    docker compose logs web 2>&1 | tail -30
    finish_deployment
    err "Fix the errors above, then re-run: bash scripts/deploy.sh"

else
    warn "HTTP ${FINAL_WEB} — unexpected status code. Recent WebApp logs:"
    docker compose logs web 2>&1 | tail -30
    finish_deployment
fi
