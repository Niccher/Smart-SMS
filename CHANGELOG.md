# Changelog — M-Pesa Analyzer Platform (Monorepo)

All notable changes to this project will be documented in this file.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning follows [Semantic Versioning](https://semver.org/).

---

## [Unreleased]
### Added
- **Unified Monorepo Consolidation** — Merged `MPesa-Analyzer-WebApp` (PHP 8.3 / CodeIgniter 4) and `ML-Mpesa-Analyser` (Python 3.12 / FastAPI / llama.cpp) into a single unified repository with synchronized architecture, combined Docker Compose, and shared `project-docs v3.1` documentation.

---

## WebApp Service (CodeIgniter 4)

### [3.3.0] — 2026-08-26
#### Added
- **Per-user category rules engine** — `AnalysisCallbackController.php` implements exact/contains matching rules with hit telemetry and isolated retroactive backfill per user. Replaces the old monolithic `Analyse.php`.
- **Structured REST API v1 layer**:
  - `Api/V1/BaseApiController.php` — shared JWT auth, versioning, JSON response.
  - `Api/V1/AnalyticsController.php` — per-user analytics endpoints.
  - `Api/V1/AuthController.php` — token issue, refresh, revoke.
  - `Api/V1/NotesController.php` — transaction notes CRUD.
  - `Filters/ForceJsonResponseFilter.php` — enforces `application/json` on all `/api/*` routes.
- **`app/Config/version.json`** — single source of truth for app version (`3.3.0`), changelog bullets, GitHub URL, and APK download link; consumed by the Android AppInfo screen via `/api/v1/system/version`.
- **Dynamic versioning endpoint** `GET /api/v1/system/version` — serves changelog and version metadata to Android clients for update banners.
- `loot_uploaded` email templates (HTML + plain-text) for SMS upload notifications.
- Admin system nav section (`Views/Admin/System/_nav.php`).

#### Changed
- Controllers renamed to singular PSR-4 class names (`DashboardController`, `AuthController`, etc.).
- Models renamed to singular PSR-4 convention (`BudgetModel`, `DeviceModel`, etc.).
- `CryptoHelper` secure dynamic IV generation added; IV transmitted alongside ciphertext.
- Upload payload directory standardized to `uploads/payloads/`.
- Routes fully rewritten for RESTful resource layout.

#### Removed
- `Controllers/Analyse.php` — superseded by `AnalysisCallbackController.php`.
- `Controllers/Testar.php` — development/testing controller removed.

#### Fixed
- `Commands/LlmProcess.php` retry logic and auto-fail timeout.
- Blocklist status view enhanced with stats accordion.

### [3.2.0] — 2026-08-11
#### Added
- Blocklist status dashboard with stats accordion.
- ML allowed-senders management and job statistics panel.
- Retention cron job for automated data housekeeping.

### [3.1.0] — 2026-08-05
#### Added
- Device tracking UI (user & admin pages with audit, tokens, and activity).
- Upload pipeline: `user_id` stamping, SMS ownership resolution, audit calls.

### [3.0.0] — 2026-07-20
#### Added
- HTML email overhaul with rich transactional templates.
- GDPR user-data deletion flow.
- Complete migration to one-table-per-migration pattern.

---

## ML Intelligence Microservice (FastAPI & LLM)

### [1.2.0] — 2026-08-27
#### Added
- **Parallelized LLM calls** — `SenderClassifier` and `Extractor` use `asyncio.gather` for concurrent batch processing.
- **Blocked-sender auto-bypass** — processing jobs automatically skip senders on blocklist.
- **`seed_prompts.py`** — standalone utility script to seed prompts into DB via REST API.
- **Model management endpoints** — upload and delete GGUF models.
- **GGUF metadata introspection** — architecture, parameters, and context window exposed via `read_gguf_metadata`.
- **Prompt versioning** — `tbl_LLM_Prompts` table with immutable version history.
- **Canonical SMS write** — single `upsert_sms_analysis` replaces multiple update queries.
- **Auto-jobs gate** — `is_auto_jobs_enabled()` toggle on `tbl_ML_Controls`.
- Docker Compose healthchecks for FastAPI service.

#### Fixed
- Query for unprocessed SMS corrected to `WHERE p.sms_id IS NULL`, eliminating infinite processing hangs.
- `asyncio.gather` event-loop handling in classifier and extractor.

### [1.1.0] — 2026-08-11
#### Added
- Model and admin management layer.
- DB-prompt overrides with hardcoded default fallback.
- Auto-job control and per-job rich metadata JSON storage.

### [1.0.0] — 2026-07-20
#### Added
- Initial FastAPI LLM classification service with local llama.cpp backend.
- `POST /process/for-user` per-user SMS processing endpoint with job tracking.
- Asynchronous MySQL connection handling with background worker.
