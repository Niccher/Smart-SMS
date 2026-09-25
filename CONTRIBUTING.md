# Contributing to Smart Finance Platform

Thank you for your interest in contributing to the **Smart Finance Platform**! We welcome community contributions to improve our transaction parsers, machine learning models, Redis performance, and conversational financial advisor.

---

## Code of Conduct

All contributors are expected to uphold our [Code of Conduct](CODE_OF_CONDUCT.md). Please read it before participating.

---

## Getting Started

1. **Fork the Repository**: Fork [Smart-Finance-Platform](https://github.com/Niccher/Smart-Finance-Platform) to your GitHub account.
2. **Clone Locally**:
   ```bash
   git clone https://github.com/<your-username>/Smart-Finance-Platform.git
   cd Smart-Finance-Platform
   ```
3. **Environment Setup**:
   ```bash
   cp .env.example .env
   docker compose up --build -d
   ```
4. **Access Running Services**:
   - WebApp: `http://localhost`
   - AI Chat: `http://localhost/dashboard/chat`
   - ML Swagger Docs: `http://localhost:8001/docs`

---

## Privacy-First Testing: Never Submit Real SMS Data!

> [!CAUTION]
> **Zero Financial Data Policy**: Under no circumstances should pull requests, issues, or test files include real personal financial records, actual bank account numbers, or real M-Pesa transaction balances.

Always generate test data using the included synthetic generator:
```bash
python3 scripts/generate_synthetic_sms.py --count 100 --output scratch/test_payloads.json
```

---

## Coding Standards & Quality Gates

Before opening a pull request, ensure all linters and tests pass:

1. **Documentation Quality Linter**:
   ```bash
   python3 scripts/lint-docs.py
   ```
2. **PHP Syntax & Style**:
   - Follow standard **PSR-12** formatting.
   - Run PHP syntax check:
     ```bash
     find web/ -name "*.php" -exec php -l {} \;
     ```
3. **Python (ML Microservice)**:
   - Adhere to **PEP 8** style guidelines.
   - Run pytest suite:
     ```bash
     docker compose exec ml pytest tests/ -v
     ```

---

## Pull Request Lifecycle

1. Create a descriptive feature branch:
   ```bash
   git checkout -b feat/support-new-bank-parser
   ```
2. Write clean, self-documenting code with informative commit messages following [Conventional Commits](https://www.conventionalcommits.org/):
   - `feat(parser): add support for NCBA Bank transaction alerts`
   - `fix(chat): resolve context window truncation in Sheng queries`
3. Push to your fork:
   ```bash
   git push origin feat/support-new-bank-parser
   ```
4. Open a Pull Request targeting `main`. Fill in the PR template with testing notes and screenshots.
