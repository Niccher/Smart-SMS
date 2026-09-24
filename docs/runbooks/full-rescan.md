# Operational Runbook: Executing a Full Database AI Rescan

This runbook outlines the steps to re-classify and re-extract historical SMS messages following prompt template edits, dictionary updates, or model upgrades.

---

## 1. When to Perform a Full Rescan

- After updating prompt templates in `/admin/ml/prompts`.
- After adding new known financial senders to `tbl_Allowed_Senders`.
- After switching to a larger or fine-tuned LLM model.
- If previous runs failed due to database timeouts or memory exhaustion.

---

## 2. Procedure A: Via Web Dashboard (User/Operator)

1. Log into the Web Dashboard at http://localhost.
2. Navigate to **History $\to$ ML Jobs** or **Dashboard**.
3. **Trigger Partial Rescan**: Click **Rescan Unprocessed** to process only pending or errored records.
4. **Trigger Full Rescan**: Click **Full Rescan (Reset All)**. This action:
   - Clears `tbl_Sms_Processing` status records.
   - Clears LLM-derived fields on `tbl_Sms` (`sms_category`, `sms_amount`, etc.).
   - Dispatches a background job request to `http://ml-mpesa-analyzer:9050/process/for-user/{id}`.
5. **Monitor Progress**:
   The dashboard polls `/dashboard/rescan/progress` showing real-time counters of total, processed, and classified messages.

---

## 3. Procedure B: Via CLI (Spark Command)

From inside the WebApp container or host directory:

```bash
# Dispatch processing cycle directly via Spark CLI
php spark llm:process

# Monitor processing jobs in real time
php spark llm:process --limit 50
```

To inspect job execution telemetry:
```sql
SELECT job_id, user_id, status, created_at, metadata 
FROM tbl_Processing_Jobs 
ORDER BY job_id DESC LIMIT 5;
```
