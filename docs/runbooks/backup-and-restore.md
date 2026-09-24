# Runbook: Database Backup & Restoration

This runbook outlines standard operating procedures for taking, downloading, and restoring database backups in the Mpesa Analyzer WebApp.

---

## 1. WebApp UI Backup Procedure (Recommended)

1. Log into the Web Dashboard as a SuperAdmin.
2. Navigate to **Admin &rarr; System &rarr; Backups** (`/admin/system/backup`).
3. Verify the **Disk Headroom** badge confirms sufficient free space (minimum 2x database size required).
4. Click **Create Full Backup**.
5. The system invokes `mysqldump` in the background and writes the compressed SQL snapshot into `writable/backups/`.
6. Click **Download** on the generated backup card to store a secure offsite copy.

---

## 2. Command-Line Backup Procedure (mysqldump)

To create an immediate database dump from the container terminal:

```bash
docker exec -i mpesa-analyzer-mysql mysqldump \
  -u root -p"${MYSQL_ROOT_PASSWORD}" \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  db_mpesa_analyzer > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## 3. Database Restoration Procedure

> [!CAUTION]
> Restoring a backup overwrites all current database tables and transactions. Ensure all users are logged out before proceeding.

### Via Web Console:
1. Navigate to **Admin &rarr; System &rarr; Backups**.
2. Locate the backup file in the archives table or upload a previous `.sql` dump.
3. Click **Restore** and confirm the confirmation prompt.

### Via Command-Line:
```bash
docker exec -i mpesa-analyzer-mysql mysql \
  -u root -p"${MYSQL_ROOT_PASSWORD}" \
  db_mpesa_analyzer < backup_to_restore.sql
```

After restoration, verify system health:
```bash
curl -f http://localhost/health
```\n