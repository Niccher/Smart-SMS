# Configuration Guide — M-Pesa Analyzer Platform

All multi-service configurations are managed via the `.env` file at the root of the monorepo.

---

## 1. Stack & Port Variables

| Variable | Service | Default | Purpose |
|---|---|---|---|
| `WEB_PORT` | WebApp | `9002` | Host published port for the CodeIgniter dashboard |
| `ML_MPESA_ANALYZER_API_PORT` | ML | `9021` | Host published port for the FastAPI microservice |
| `ML_MPESA_ANALYZER_LLAMA_PORT` | ML | `9022` | Host published port for the local llama-server |
| `MYSQL_HOST_PORT` | MySQL | `9306` | Host published port for external MySQL connections |

---

## 2. Database Variables

| Variable | Default | Purpose |
|---|---|---|
| `DB_HOST` | `mysql` | Hostname of MySQL container on the Docker network |
| `DB_PORT` | `3306` | Internal MySQL port |
| `DB_NAME` | `db_mpesa_analyzer` | Relational database schema name |
| `DB_USER` | `root` | Database username |
| `DB_PASSWORD` | `root_password` | Database password |
| `MYSQL_ROOT_PASSWORD` | `root_password` | Root administrative password for MySQL container |

---

## 3. WebApp & Mobile API Gateway Variables

| Variable | Default | Purpose |
|---|---|---|
| `CI_ENVIRONMENT` | `development` | CodeIgniter mode (`development` or `production`) |
| `app.baseURL` | `http://localhost:9002/` | Base URL used for link generation and redirects |
| `ML_BACKEND_URL` | `http://ml-mpesa-analyzer:9050` | Microservice URL used by WebApp commands |
| `MPESA_CRYPT_KEY` | *(Set in .env)* | 16-byte key for AES-128-CBC decryption |
| `MPESA_CRYPT_IV` | *(Set in .env)* | Fallback 16-byte IV for legacy uploads |
| `SUPERADMIN_EMAIL` | `superadmin@mpesa-analyzer.local` | Email of seeded superadmin user |
| `SUPERADMIN_PASSWORD` | *(Set in .env)* | Initial password of seeded superadmin user |

---

## 4. ML Intelligence Microservice Variables

| Variable | Default | Purpose |
|---|---|---|
| `LLM_ENGINE` | `local` | Active inference engine (`local` or `external`) |
| `LLM_MODEL` | `qwen2.5-1.5b-instruct` | Active model identifier |
| `MODEL_PATH` | `/models/qwen2.5-1.5b-instruct-q4_k_m.gguf` | Path to active GGUF weights inside container |
| `LLAMA_PORT` | `8080` | Internal port for llama-server binary |
| `LLM_CTX_SIZE` | `16384` | Context window size allocated to llama-server |
| `LLM_BATCH_SIZE` | `512` | Batch evaluation size for prompt token ingestion |
| `BATCH_SIZE` | `5` | Unprocessed SMS processed per classification run |
| `POLL_INTERVAL` | `30` | Seconds between autonomous DB poll queries |
| `LLM_FALLBACK_ENABLED`| `false` | Enable cloud LLM fallback if local engine errors |
| `ML_INTERNAL_SECRET` | *(Optional)* | Secret key for `X-Internal-Secret` header validation |
