# Changelog — M-Pesa Analyzer Platform (Monorepo)

All notable changes to this project will be documented in this file.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning follows [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

---

## [3.5.0] — 2026-09-25
### Added
- **Conversational AI Financial Assistant ("Ask My M-Pesa")**:
  - WebApp conversational chat interface at `/dashboard/chat` supporting multi-turn dialogues in English and Sheng.
  - Native Android conversational chat activity (`ChatActivity`) with model metadata, suggested starter chips, and markdown message rendering.
  - Contextual aggregation injection injecting monthly spending, Fuliza fees, top recipients, and balance history into prompts.
- **Persistent Chat History (`tbl_Chat_Messages`)**:
  - Migration `2026-09-25-000031_CreateTblChatMessages.php` creating `tbl_Chat_Messages`.
  - Client platform tracking (`platform` column: `webapp` vs `mobile`) and device user-agent capture.
  - Interactive WebApp history display with visual `Web` and `Mobile` badges and clear history endpoint (`POST /dashboard/chat/clear`).
  - Mobile gateway endpoints: `GET /api/v1/chat/history` and `DELETE /api/v1/chat/history`.
- **Redis 7 In-Memory Caching & Telemetry**:
  - Redis 7 container integration (`mpesa-redis` on port 6379, 128 MB maxmemory with `volatile-lru` eviction).
  - High-performance session storage and prompt/response caching.
  - 4th real-time KPI card in `admin/telemetry` tracking memory RSS, keyspace hit rate %, total keys, ops/sec, and ping latency.
- **Cron Daemon Diagnostics**:
  - Added daemon active/inactive status indicator to `admin/crons`.
  - Added live AJAX streaming modal for `/var/log/mpesa-cron.log` (`GET /admin/crons/daemon-log`).
- **Interactive Multi-Release Changelog Modal**:
  - Reusable modal (`_changelog_modal.php`) and footer (`_footer.php`) displaying interactive release timeline across all admin and landing pages.

### Changed
- **Mobile Gateway Proxy**: Android companion app calls WebApp on port 80 (`/api/v1/chat`), eliminating direct port 8001 firewall and NAT issues.
- **Unified Release Train v3.5.0**: Synchronized versions across `version.json`, FastAPI `main.py`, and Android `build.gradle.kts` (versionCode 4).

### Fixed
- **Android Biometric Double-Prompt**: Resolved race condition between `MainActivity.onStop()` and `LockActivity` by introducing a 30-second grace period.
- **Cron Zero Executions in Container**: Resolved Debian PAM loginuid termination (`session optional pam_loginuid.so`) and missing Docker environment variables by exporting `cron_env.sh` on startup.
- **Chat 403 Forbidden & 404 Not Found**: Added CSRF exception and X-CSRF-TOKEN headers in WebApp, and created `Api\V1\ChatController` for mobile routing.

---

## [3.4.0] — 2026-09-07
### Added
- **Global Ace Admin Theme Integration**: Clean corporate Ace theme across all landing pages, authentication views, error templates, and admin dashboard.
- **Persistent Dark Mode**: LocalStorage theme state management with automatic system preference detection.
- **Ace Transactional HTML Emails**: Custom Ace-styled templates for 2FA, password reset, and magic links.
- **Standardized Error Pages**: Ace-branded templates for HTTP 400, 401, 403, 404, 500, and 503.

---

## [3.3.0] — 2026-08-26
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
