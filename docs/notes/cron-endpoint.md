**Cron Endpoint (Fallback) — Setup and usage**

- **Purpose:** provide a protected HTTP endpoint that triggers `php artisan schedule:run` and a single `queue:work --once` run. Use an external cron service (cron-job.org, easycron) to call it every minute when Hostinger's cron panel or `crontab` is unreliable.

- **Files changed:** `routes/web.php` — added POST `/_internal/cron/{token}`.

- **Add to `.env`:**

  CRON_TOKEN=replace_with_a_long_random_string

- **How to test locally / from your machine:**

```bash
# Example (replace <TOKEN> and domain)
curl -X POST https://grupoatlantiscrm.eu/new/_internal/cron/<TOKEN>
```

Expected response: `OK` (HTTP 200). Check Laravel logs and queue behavior to confirm jobs ran.

- **Configure cron-job.org (example):**
  - Create a free account.
  - Add a new job:
    - URL: `https://grupoatlantiscrm.eu/new/_internal/cron/<TOKEN>`
    - Method: POST
    - Interval: 1 minute

- **Security notes:**
  - Keep `CRON_TOKEN` secret and long (32+ chars).
  - Use HTTPS only. Consider IP allowlisting or short-lived tokens if needed.

- **Deploy:**
  - Commit and deploy the code change to production (or paste the route snippet into `routes/web.php`).
  - Add `CRON_TOKEN` to the production `.env`.
  - Configure the external cron to POST the URL every minute.
