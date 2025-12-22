# 🚀 Quick Start Guide - Git Repository Monitoring

Get started with the Git Repository Monitoring service in 5 minutes!

## Prerequisites

- Laravel 12 project running
- MySQL database configured
- Git API token (GitHub, GitLab, or Bitbucket)

## Step 1: Run Migrations

```bash
php artisan migrate
```

Expected output:
```
✓ 2025_12_22_030351_create_repository_files_table
✓ 2025_12_22_030411_create_repository_change_notifications_table
✓ 2025_12_22_030431_create_repository_sync_logs_table
```

## Step 2: Start Your Server

```bash
php artisan serve
```

Server will start at: `http://localhost:8000`

## Step 3: Test the API

### Option A: Using cURL

```bash
# Sync a repository
curl -X POST http://localhost:8000/api/repositories/sync \
  -H "Content-Type: application/json" \
  -d '{
    "repo_url": "https://api.github.com/repos/laravel/framework",
    "repo_name": "laravel-framework",
    "branch": "11.x",
    "token": ""
  }'
```

### Option B: Using the Test Script

```bash
./tests/api_test.sh
```

### Option C: Using Postman

1. Import `docs/postman_collection.json`
2. Set `base_url` to `http://localhost:8000`
3. Run "Sync Repository" request

## Step 4: View Results

### Get Directory Tree
```bash
curl http://localhost:8000/api/repositories/laravel-framework/structure
```

### Get New Files
```bash
curl http://localhost:8000/api/repositories/laravel-framework/changes?status=new
```

### Get Notifications
```bash
curl http://localhost:8000/api/repositories/laravel-framework/notifications?sent=false
```

### Get Latest Sync Status
```bash
curl http://localhost:8000/api/repositories/laravel-framework/latest-sync
```

## Expected Response

```json
{
  "status": "success",
  "message": "Repository synced successfully",
  "data": {
    "sync_log_id": 1,
    "runtime_seconds": 5,
    "statistics": {
      "scanned": 150,
      "new": 5,
      "modified": 3,
      "deleted": 2
    },
    "directory_tree_string": "📁 src/\n  📄 index.js [new]\n",
    "changes": {
      "new": [...],
      "modified": [...],
      "deleted": [...]
    }
  }
}
```

## Common Use Cases

### Monitor Your Own Repository

```bash
curl -X POST http://localhost:8000/api/repositories/sync \
  -H "Content-Type: application/json" \
  -d '{
    "repo_url": "https://api.github.com/repos/YOUR_USERNAME/YOUR_REPO",
    "repo_name": "my-project",
    "branch": "main",
    "token": "YOUR_GITHUB_TOKEN"
  }'
```

### Check for Changes Daily

Set up a cron job or Laravel scheduler:

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        $service = new \App\Services\GitRepositoryService(
            'https://api.github.com/repos/myorg/myrepo',
            env('GIT_TOKEN')
        );
        $service->syncRepository('my-project');
    })->daily();
}
```

### Get Unsent Email Notifications

```bash
curl "http://localhost:8000/api/repositories/my-project/notifications?sent=false&type=email"
```

## Troubleshooting

### Error: "API request failed"
- Check your Git API token
- Verify repository URL format
- Ensure you have access to the repository

### Error: "Table not found"
- Run migrations: `php artisan migrate`

### Error: "Connection refused"
- Start the server: `php artisan serve`

## Next Steps

1. **Read Full Documentation**: `docs/REPOSITORY_API_DOCUMENTATION.md`
2. **Explore Features**: `docs/REPOSITORY_MONITORING_README.md`
3. **Review Implementation**: `docs/IMPLEMENTATION_SUMMARY.md`
4. **Test All Endpoints**: Use Postman collection

## Git Platform Tokens

### GitHub
1. Go to Settings → Developer settings → Personal access tokens
2. Generate new token with `repo` scope
3. Copy token

### GitLab
1. Go to Preferences → Access Tokens
2. Create token with `read_api` scope
3. Copy token

### Bitbucket
1. Go to Personal settings → App passwords
2. Create password with `repository:read` permission
3. Copy password

## API Endpoints Summary

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/repositories/sync` | POST | Sync repository |
| `/api/repositories/{repo}/structure` | GET | Get directory tree |
| `/api/repositories/{repo}/changes` | GET | Get changes |
| `/api/repositories/{repo}/notifications` | GET | Get notifications |
| `/api/repositories/{repo}/sync-logs` | GET | Get sync logs |
| `/api/repositories/{repo}/latest-sync` | GET | Get latest sync |
| `/api/repositories/notifications/{id}/mark-sent` | PATCH | Mark as sent |

## Support

- 📖 Full API Docs: `docs/REPOSITORY_API_DOCUMENTATION.md`
- 📋 README: `docs/REPOSITORY_MONITORING_README.md`
- 📦 Postman: `docs/postman_collection.json`
- 🧪 Test Script: `tests/api_test.sh`

---

**You're all set! 🎉**

Start monitoring your repositories and detecting changes automatically!
