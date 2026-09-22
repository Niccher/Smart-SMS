# M-Pesa Analyzer Platform

Full-stack financial analytics platform and mobile API gateway for M-Pesa transactions. Combines a CodeIgniter 4 web application, an autonomous Python FastAPI LLM intelligence microservice, and a shared MySQL 8.4 database to ingest, decrypt, classify, and visualize mobile money transactions.

Stack: PHP 8.3 (CodeIgniter 4), Python 3.12 (FastAPI, llama.cpp, Qwen2.5), MySQL 8.4, Docker Compose

**If you only need to run the system, this page is enough.**  
Software engineers: [docs/README.md](docs/README.md).

---

## What “Running” Looks Like

| Component | URL / Port | Expected Response / Check |
|-----------|------------|---------------------------|
| **Web Dashboard** | http://localhost:9002 | Login / Dashboard interface |
| **WebApp Health** | http://localhost:9002/health | HTTP 200 OK |
| **ML Microservice** | http://localhost:9021 | FastAPI root / API endpoint |
| **ML Health Check** | http://localhost:9021/health | `{"status": "ok", "db_configured": true}` |
| **ML Swagger UI** | http://localhost:9021/docs | Interactive OpenAPI documentation |
| **phpMyAdmin** | http://localhost:9000 | MySQL database administration |
| **MySQL Server** | localhost:9306 | Direct database access port |

---

## Prerequisites

### Option A — Docker (Recommended)
- Git
- Docker Engine 24+ & Docker Compose v2
- Minimum 4 GB host RAM (8 GB recommended for local CPU LLM inference)

### Option B — Without Docker
- Native PHP 8.3+ with `intl`, `mbstring`, `mysqli`, `curl` extensions & Composer 2.x
- Python 3.12+ with virtual environment & llama-server binary
- Running MySQL 8.4 server instance
- Full native instructions: [docs/engineering/local-development.md](docs/engineering/local-development.md)

---

## Setup and Run

1. Clone or navigate to the repository directory:
   ```bash
   cd MPesa
   ```
2. Create your local environment configuration:
   ```bash
   cp .env.example .env
   ```
3. Start the entire container stack:
   ```bash
   docker compose up --build -d
   ```
   *Migrations and default database seeds execute automatically during WebApp boot.*
4. Verify healthchecks:
   ```bash
   curl http://localhost:9002/health
   curl http://localhost:9021/health
   ```
5. Open http://localhost:9002 in your browser.
6. Stop the stack when done:
   ```bash
   docker compose down
   ```

---

## Configuration Users May Change

| Variable | Default | Purpose |
|----------|---------|---------|
| `WEB_PORT` | `9002` | Host port for the WebApp dashboard |
| `ML_MPESA_ANALYZER_API_PORT` | `9021` | Host port for FastAPI microservice |
| `MYSQL_HOST_PORT` | `9306` | External port for MySQL 8.4 |
| `PHPMYADMIN_PORT` | `9000` | Host port for phpMyAdmin GUI |
| `CI_ENVIRONMENT` | `development` | CodeIgniter environment (`development` or `production`) |
| `LLM_ENGINE` | `local` | Inference engine (`local` llama-server or `external` API) |
| `SUPERADMIN_EMAIL` | `superadmin@mpesa-analyzer.local` | Default admin email seeded at boot |
| `SUPERADMIN_PASSWORD` | `change_this_secure_password_123!` | Initial administrator password |

Full environment variable reference: [docs/user/configuration.md](docs/user/configuration.md).

---

## Something Went Wrong?

- **Port in use (9002, 9021, 9306, 9000)**: Adjust the respective port in `.env`.
- **MySQL container unhealthy**: Check logs with `docker compose logs mysql`.
- **ML container fails health check**: Ensure the GGUF model exists or check `docker compose logs ml`.
- **Web 500 error**: Verify `web/writable/` permissions: `chmod -R 777 web/writable`.
- Detailed operational remedies: [docs/user/troubleshooting.md](docs/user/troubleshooting.md).

---

## Software Engineers

- **Architecture Overview**: [docs/architecture/overview.md](docs/architecture/overview.md)
- **Inter-Service Communication**: [docs/architecture/communication.md](docs/architecture/communication.md)
- **Data & Canonical Storage**: [docs/architecture/data-and-storage.md](docs/architecture/data-and-storage.md)
- **CodeIgniter 4 Service**: [docs/services/codeigniter.md](docs/services/codeigniter.md)
- **FastAPI Microservice**: [docs/services/fastapi.md](docs/services/fastapi.md)
- **LLM & Inference**: [docs/services/ml.md](docs/services/ml.md)
- **Mobile Client Integration**: [docs/services/android.md](docs/services/android.md)
- **Making Changes Safely**: [docs/engineering/making-changes.md](docs/engineering/making-changes.md)
