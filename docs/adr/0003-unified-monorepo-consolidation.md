# ADR 0003: Unified Monorepo Consolidation

## Status
Accepted (2026-09-22)

## Context and Problem Statement
Previously, the M-Pesa Analyzer platform was maintained across two separate repositories:
1. `MPesa-Analyzer-WebApp` (PHP 8.3 / CodeIgniter 4 presentation, database, and mobile gateway)
2. `ML-Mpesa-Analyser` (Python 3.12 / FastAPI / llama.cpp autonomous inference engine)

Both services shared the identical MySQL 8.4 database schema (`db_mpesa_analyzer`) and Docker network namespace (`hosts-shared-network`). Running or developing the platform required orchestrating two separate Compose environments, coordinating database migrations manually, and maintaining synchronized yet disjointed documentation trees.

## Decision Drivers
- **Operational Simplicity**: Single `docker compose up --build -d` command to boot the entire stack (Database, WebApp, phpMyAdmin, and ML microservice).
- **Atomic Migrations & Features**: Cross-service features (such as adding new category rules, model prompts, or telemetry tables) can be committed in a single pull request.
- **Unified Documentation**: Compliance with the dual-audience `project-docs v3.1` standard with zero broken links and cohesive architecture diagrams.
- **Commit History Preservation**: Merging Git trees using unrelated-histories strategy to preserve 100% of historical commits, blame annotations, and authorship.

## Considered Options
1. **Maintain Polyrepo with Git Submodules**:
   - Pros: Separate repositories remain independent.
   - Cons: Submodule pointer drift, complex CI orchestration, two separate Compose files required.
2. **Copy-Paste Consolidation**:
   - Pros: Quick to execute.
   - Cons: Total loss of Git commit history, authorship, and blame tracking for one or both codebases.
3. **Git History Merge into Unified Monorepo (Chosen)**:
   - Pros: Preserves 100% of Git commit history from both repositories; unifies `web/` and `ml/` subdirectories; single root Compose and documentation hub.

## Decision Outcome
The platform is consolidated into a monorepo structure at `/home/niccher/Music/hosts/MPesa`:
- `web/`: CodeIgniter 4 application.
- `ml/`: FastAPI microservice and llama.cpp runtime.
- `docs/`: Centralized engineering and operator documentation hub adhering to `project-docs v3.1`.
- Root `docker-compose.yml`: Multi-container orchestration managing `mysql`, `phpmyadmin`, `web`, and `ml`.

## Consequences
- **Positive**: Single repository clone, single pull request workflow, unified CI pipelines, simplified onboarding.
- **Negative**: Monorepo clone size includes both PHP and Python dependencies (mitigated by `.gitignore` rules on virtualenvs and vendor directories).
