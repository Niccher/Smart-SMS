# Troubleshooting Guide (Operator) — M-Pesa Analyzer Platform

Common operational issues and remedies when running the multi-container monorepo stack.

---

## 1. WebApp Returns 500 or Blank Screen

**Symptom**: `http://localhost:9002` displays a 500 server error or blank screen.

**Remedy**:
1. Check WebApp container logs:
   ```bash
   docker compose logs web
   ```
2. Verify directory permissions on `web/writable/`:
   ```bash
   chmod -R 775 web/writable
   ```
3. Check `web/writable/logs/` for the active PHP error trace.

---

## 2. Port Conflict on 9002, 9021, or 9306

**Symptom**: `bind: address already in use` during `docker compose up` or `deploy.sh`.

**Remedy**:
Update the conflicting port in your `.env` file:
- `WEB_PORT=9003`
- `ML_MPESA_ANALYZER_API_PORT=9023`
- `MYSQL_HOST_PORT=9307`
Then restart: `docker compose up -d` or `bash scripts/deploy.sh`.

---

## 3. Database Connection Failure or Blocked Migrations

**Symptom**: Web container repeatedly logs `Waiting for MySQL to accept connections...` or ML container logs `DB not reachable at startup`.

**Remedy**:
1. Inspect MySQL container health:
   ```bash
   docker compose ps mysql
   docker compose logs mysql
   ```
2. Ensure MySQL container has completed initial InnoDB initialization.
3. If database state is corrupted in development, perform a clean volume reset:
   ```bash
   docker compose down -v
   docker compose up --build -d
   ```

---

## 4. ML Container Fails Health Check or Exits

**Symptom**: `ml` container status is `unhealthy` or repeatedly restarts.

**Remedy**:
1. Inspect ML container logs:
   ```bash
   docker compose logs ml
   ```
2. Check if the GGUF model download was interrupted. Run the download helper:
   ```bash
   bash scripts/download-models.sh
   ```
3. Check host memory: The quantized Qwen2.5 1.5B model requires at least 2.5 GB free RAM. If host RAM is constrained, switch to external cloud API mode by setting `LLM_ENGINE=external` in `.env`.

---

## 5. Background Poller Idle or Auto-Jobs Disabled

**Symptom**: Uploaded SMS transactions remain in `tbl_Sms` without getting enriched by the ML engine.

**Remedy**:
1. Log in to the WebApp Dashboard as admin.
2. Navigate to **Admin -> ML Config** and verify that **Auto-Jobs** is switched to **Enabled**.
3. Alternatively, trigger an on-demand batch run:
   ```bash
   curl -X POST http://localhost:9021/process/trigger
   ```
