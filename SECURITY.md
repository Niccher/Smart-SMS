# Security Policy

The **Smart Finance Platform** ecosystem treats financial data security, privacy, and cryptographic integrity as paramount. We adhere to a strict **zero-financial-data-leak commitment**.

---

## Supported Versions

Only the latest active major/minor release receives security updates and vulnerability patches:

| Version | Supported          |
| ------- | ------------------ |
| 3.5.x   | :white_check_mark: |
| 3.4.x   | :x:                |
| < 3.4.0 | :x:                |

---

## Zero-Financial-Data-Leak Commitment

1. **In-Memory Decryption**: All incoming mobile financial SMS payloads are transmitted using AES-256-CBC encryption. Decryption occurs strictly in-memory inside the platform gateway. Raw unencrypted SMS payloads are never written to disk or public server logs.
2. **No Third-Party Analytics**: Neither the web dashboard nor the API gateway uses external trackers, telemetry beacons, or third-party advertising SDKs.
3. **Local/Self-Hosted LLM First**: For maximum privacy, the platform supports local offline LLM inference via `llama.cpp` (Qwen 2.5 7B GGUF) running within the Docker network boundary, ensuring financial queries never leave the host.
4. **Environment Isolation**: Database passwords, Redis credentials, and cryptographic IV/keys are managed exclusively via environment variables and never checked into source control.

---

## Reporting a Vulnerability

If you discover a security vulnerability in this repository, please do **NOT** open a public issue.

Please report vulnerabilities privately:
* **Security Contact**: `domi777nicch@gmail.com`
* **Subject Line**: `[SECURITY VULNERABILITY] Smart Finance Platform - <Component>`
* **Information to Include**:
  - Detailed description of the vulnerability.
  - Steps to reproduce or proof-of-concept payload (sanitized with mock data).
  - Potential impact on financial confidentiality or platform availability.

### Response Timeline
* **Initial Acknowledgement**: Within 48 hours.
* **Triage & Assessment**: Within 5 business days.
* **Remediation & Patch Release**: Coordinated with the reporter before public disclosure.
