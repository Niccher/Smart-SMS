# Setup and Run Guide — M-Pesa Analyzer Platform

This guide walks through starting and operating the full **M-Pesa Analyzer Platform** multi-container stack, which runs the CodeIgniter 4 WebApp, the Python FastAPI ML microservice, MySQL 8.4, and phpMyAdmin.

---

## 1. Prerequisites

- **Docker Engine** 24.0+ and **Docker Compose** v2
- Host ports available:
  - `9002` (WebApp Dashboard)
  - `9021` (FastAPI Microservice)
  - `9022` (llama-server)
  - `9306` (MySQL Database)
  - `9000` (phpMyAdmin)

---

## 2. Step-by-Step Run Instructions

From the repository root (`MPesa/`):

### Step 1: Copy Environment Configuration
```bash
cp .env.example .env
```

### Step 2: Start the Multi-Service Stack
```bash
docker compose up --build -d
```

During container boot:
1. The **MySQL 8.4** container starts and runs health checks on port `3306`.
2. The **WebApp** container waits for MySQL, executes migrations via `php spark migrate --all`, seeds default admin credentials, starts the cron daemon, and launches Apache on port `9002`.
3. The **ML** container verifies or downloads the Qwen2.5 GGUF weights, starts `llama-server` on port `8080`, and launches FastAPI with the background DB poller on port `9050`.
4. The **phpMyAdmin** container connects to MySQL for web-based administration on port `9000`.

### Step 3: Verify Service Health
```bash
# Check WebApp health
curl -f http://localhost:9002/health

# Check ML microservice health
curl -f http://localhost:9021/health
```

Expected ML response:
```json
{"status": "ok", "llm_provider": "openai-compatible", "llm_model": "qwen2.5-1.5b-instruct", "db_configured": true}
```

### Step 4: Access Interfaces
- **Web Application Dashboard**: http://localhost:9002
- **ML Swagger UI & Documentation**: http://localhost:9021/docs
- **phpMyAdmin Database Tool**: http://localhost:9000
  - Host: `mysql`
  - User: `root`
  - Password: `root_password` (or value from `.env`)

### Step 5: Stopping the Stack
```bash
docker compose down
```
To preserve persistent database data in the `mysql-data` volume, do not pass `-v`.
