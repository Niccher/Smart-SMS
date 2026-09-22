# Automated Testing Guide — M-Pesa Analyzer Platform

This guide documents running test suites across the CodeIgniter 4 WebApp, the FastAPI microservice, and documentation quality checks.

---

## 1. Running All Tests in Docker

### WebApp PHPUnit Suite
```bash
docker compose exec web vendor/bin/phpunit
```

### ML Microservice Pytest Suite
```bash
docker compose exec ml pytest
```

---

## 2. Running Tests Locally on Host

### WebApp Tests (`web/`)
From `web/`:
```bash
composer test
# or directly:
vendor/bin/phpunit
```

### ML Microservice Tests (`ml/`)
From `ml/` (with virtual environment active):
```bash
pytest tests/ -v
```

---

## 3. Documentation Quality Linting

Validate that markdown documentation complies with `project-docs v3.1` (no broken links, valid Mermaid diagrams, README line limit, no credential leakage):
```bash
python3 scripts/lint-docs.py .
```
