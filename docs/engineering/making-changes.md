# Making Changes — Engineering Guide

Instructions for modifying CodeIgniter 4 controllers, FastAPI routers, database schemas, and ML inference logic in the monorepo.

---

## 1. Task Navigator

| If you want to… | Files to touch |
|---|---|
| **Add a new Mobile API endpoint** | 1. `web/app/Config/Routes.php` under `api/v1` group<br/>2. `web/app/Controllers/Api/V1/`<br/>3. `docs/api/contract.md` and `docs/api/openapi.yaml` |
| **Add a new Dashboard page** | 1. `web/app/Config/Routes.php`<br/>2. `web/app/Controllers/`<br/>3. `web/app/Views/` |
| **Add a database table or column** | 1. `web/app/Database/Migrations/`<br/>2. `web/app/Models/`<br/>3. If accessed by ML: `ml/app/db/queries.py`<br/>4. `docs/engineering/database.md` |
| **Add or update an ML microservice endpoint** | 1. `ml/app/routers/` or `ml/app/main.py`<br/>2. `ml/app/models/schemas.py`<br/>3. `ml/tests/`<br/>4. `docs/api/contract.md` |
| **Change LLM prompts or extraction logic** | 1. `ml/app/services/classifier.py` or `ml/app/services/extractor.py`<br/>2. `ml/app/utils/prompt_templates.py`<br/>3. `docs/services/ml.md` |
| **Tune local llama-server parameters** | 1. `.env.example` and `.env` (`LLM_CTX_SIZE`, `LLM_BATCH_SIZE`)<br/>2. `ml/entrypoint.sh`<br/>3. `docs/services/ml.md` |

---

## 2. Definition of Done (DoD)

Before opening a pull request:

- [ ] Route registered with appropriate authentication filters (`session`, `admin`, or Bearer token).
- [ ] Database migration written if tables or columns changed.
- [ ] Automated tests pass: `composer test` and `pytest`.
- [ ] Documentation updated to reflect changes.
- [ ] Documentation quality check passes: `python3 scripts/lint-docs.py .`.
