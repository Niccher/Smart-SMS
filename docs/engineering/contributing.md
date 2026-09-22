# Contributing Guidelines — M-Pesa Analyzer Platform

Guidelines for contributing code, views, endpoints, and ML services to the monorepo.

---

## 1. Branching & Commit Strategy

- Branch naming: `feature/<name>`, `fix/<name>`, `docs/<name>`, `refactor/<name>`.
- Conventional commit scopes:
  - `web`: CodeIgniter 4 controllers, models, views, and migrations.
  - `ml`: FastAPI routers, inference services, prompts, and llama-server.
  - `docs`: Documentation updates.
  - `ci`: GitHub Actions workflows and Docker Compose definitions.

---

## 2. Pull Request Definition of Done

Every pull request modifying platform behavior must satisfy:

1. **Database Safety**:
   - If database columns are changed, CodeIgniter migrations in `web/app/Database/Migrations/` must be included.
2. **Automated Tests**:
   - Run PHPUnit tests if `web/` is modified: `docker compose exec web vendor/bin/phpunit`.
   - Run Pytest tests if `ml/` is modified: `docker compose exec ml pytest`.
3. **Documentation Integrity**:
   - Run documentation quality linter:
     ```bash
     python3 scripts/lint-docs.py .
     ```
   - Must pass with 0 errors.
4. **Environment Variables**:
   - If new configuration flags are added, update `.env.example` and `docs/user/configuration.md`.
