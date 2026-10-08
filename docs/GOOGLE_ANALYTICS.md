# Google Analytics 4 setup

The public site sends page views to GA4 using `GA_MEASUREMENT_ID`. The admin dashboard reads aggregate reports through the Google Analytics Data API and displays realtime active users (30-minute and 5-minute windows), active users per minute, and month-to-date total users. Analytics credentials are used server-side and are never sent to the browser.

## 1. Configure the GA4 property

1. Create or open a GA4 property in [Google Analytics](https://analytics.google.com/).
2. Under **Admin → Data streams**, open the site's Web stream and copy its Measurement ID (`G-...`). The current project default is `G-H11E5E71R8`; replace it if your stream uses another ID.
3. Copy the numeric **Property ID** from **Admin → Property details**. This is not the Measurement ID.

## 2. Create a read-only service account

1. In [Google Cloud Console](https://console.cloud.google.com/), select or create a project.
2. Enable **Google Analytics Data API** under **APIs & Services → Library**.
3. Under **IAM & Admin → Service Accounts**, create a service account and create a **JSON** key.
4. In Google Analytics, go to **Admin → Property access management** and add the service account's `client_email` from the JSON key. Grant it the **Viewer** role only. A human Google account being an Administrator does not grant access to the service account automatically.

The application requests the `analytics.readonly` OAuth scope. Do not grant the service account Editor or Administrator access for this dashboard.

## 3. Configure the application

Set these values in `.env`:

```dotenv
GA_MEASUREMENT_ID=G-H11E5E71R8
GA4_PROPERTY_ID=123456789
GA4_REPORTING_TIMEZONE=Asia/Manila
GA4_SERVICE_ACCOUNT_FILE=app/private/google-analytics-service-account.json
```

Replace `123456789` with the numeric GA4 Property ID and set the reporting timezone to the timezone configured for that GA4 property.

Place the downloaded service-account JSON file at:

```text
storage/app/private/google-analytics-service-account.json
```

The default `GA4_SERVICE_ACCOUNT_FILE` path is relative to Laravel's `storage` directory. It can also be set to an absolute path managed by your deployment's secret store. Never commit the JSON key or paste its private key into source control. The default key path is excluded from `.gitignore`; restrict filesystem access to the application account.

After changing environment or config values, refresh cached configuration:

```sh
php artisan config:clear
# On deployments that cache config:
php artisan config:cache
```

## 4. Verify the connection

Sign in with an account allowed to view the admin dashboard. The **Website traffic** panel should show the latest GA4 reports. Realtime data is cached for about one minute; monthly totals are cached for about 15 minutes. GA4 may report zero while the site has no recent visitors, and monthly totals may be delayed by Google.

If the dashboard shows unavailable values (`—`):

- **403 / insufficient permissions:** add the service account's exact `client_email` as a Viewer on the correct GA4 property.
- **401 / invalid credentials:** verify the JSON key path, file permissions, and that the key has not been revoked.
- **API or property errors:** verify Google Analytics Data API is enabled and `GA4_PROPERTY_ID` is numeric and belongs to that property.
- **Date/month boundary mismatch:** set `GA4_REPORTING_TIMEZONE` to the GA4 property's timezone.

The dashboard keeps realtime and monthly results independent, so a failure in one report does not hide the other report's data. API errors are logged server-side without logging credentials.

The browser refreshes through a same-origin dashboard endpoint. Temporary refresh
failures preserve the last counts and show **Update delayed · retrying**. Requests
retry after 10 seconds, then back off up to one minute; successful refreshes restore
the Live status. Polling stops when the session expires or dashboard access is lost.
