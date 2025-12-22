# Git Repository Monitoring Backend Service - API Documentation

## Overview

This backend service provides comprehensive Git repository monitoring with automatic change detection, notification system, and detailed tracking of repository structure changes.

## Features

✅ **Multi-Platform Support**: GitHub, GitLab, Bitbucket, and generic Git APIs
✅ **Complete Repository Scanning**: Fetches full directory hierarchy including nested folders
✅ **Change Detection**: Automatically detects new, modified, and deleted files/folders
✅ **Database Storage**: Stores complete repository structure with metadata
✅ **Notification System**: Triggers email, UI alerts, and message logs for changes
✅ **Sync Logging**: Tracks every sync operation with statistics and runtime
✅ **Directory Tree**: Builds and formats hierarchical folder structure

---

## Database Schema

### 1. `repository_files` Table

Stores all files and folders from monitored repositories.

| Field | Type | Description |
|-------|------|-------------|
| record_id | bigint | Primary key |
| repo_name | string | Repository identifier |
| repo_url | string | Git API URL |
| file_path | string(500) | Full path to file/folder |
| folder_path | string(500) | Parent folder path |
| file_type | enum | 'file' or 'dir' |
| size | bigint | File size in bytes |
| sha | string | Git SHA hash |
| commit_date | timestamp | Last commit date |
| last_commit_sha | string | SHA of last commit |
| last_commit_message | text | Last commit message |
| change_status | enum | 'unchanged', 'new', 'modified', 'deleted' |
| last_checked_at | timestamp | Last sync timestamp |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record update time |

### 2. `repository_change_notifications` Table

Tracks all change notifications.

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| repo_name | string | Repository identifier |
| repository_file_id | bigint | Foreign key to repository_files |
| notification_type | enum | 'email', 'ui_alert', 'message_log' |
| change_type | enum | 'new', 'modified', 'deleted' |
| file_path | text | Path of changed file |
| message | text | Notification message |
| sent | boolean | Whether notification was sent |
| sent_at | timestamp | When notification was sent |
| metadata | json | Additional data |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record update time |

### 3. `repository_sync_logs` Table

Logs every repository sync operation.

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| repo_name | string | Repository identifier |
| repo_url | string | Git API URL |
| status | enum | 'pending', 'running', 'completed', 'failed' |
| started_at | timestamp | Sync start time |
| completed_at | timestamp | Sync completion time |
| runtime_seconds | integer | Total runtime |
| files_scanned | integer | Number of files scanned |
| new_files | integer | Number of new files detected |
| modified_files | integer | Number of modified files |
| deleted_files | integer | Number of deleted files |
| error_message | text | Error details if failed |
| metadata | json | Additional data |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record update time |

---

## API Endpoints

### Base URL
```
http://your-domain.com/api/repositories
```

### 1. Sync Repository

**Endpoint:** `POST /api/repositories/sync`

**Description:** Fetches repository structure from Git API, compares with database, detects changes, and triggers notifications.

**Request Body:**
```json
{
  "repo_url": "https://api.github.com/repos/owner/repo-name",
  "repo_name": "my-project",
  "branch": "main",
  "token": "your_git_api_token"
}
```

**Response (Success):**
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
    "directory_tree": {
      "src": {
        "components": {
          "Header.js": {
            "type": "file",
            "size": 1024,
            "change_status": "new",
            "record_id": 123
          }
        }
      },
      "README.md": {
        "type": "file",
        "size": 2048,
        "change_status": "unchanged",
        "record_id": 124
      }
    },
    "directory_tree_string": "📁 src/\n  📁 components/\n    📄 Header.js [new]\n📄 README.md\n",
    "changes": {
      "new": [
        {
          "path": "src/components/Header.js",
          "type": "file",
          "size": 1024
        }
      ],
      "modified": [
        {
          "path": "package.json",
          "type": "file",
          "size": 512
        }
      ],
      "deleted": [
        {
          "path": "old-file.js",
          "type": "file"
        }
      ]
    }
  }
}
```

---

### 2. Get Repository Structure

**Endpoint:** `GET /api/repositories/{repoName}/structure`

**Description:** Retrieves the complete directory tree for a repository.

**Response:**
```json
{
  "status": "success",
  "data": {
    "repo_name": "my-project",
    "total_files": 150,
    "directory_tree": { /* hierarchical structure */ },
    "directory_tree_string": "📁 src/\n  📄 index.js\n",
    "files": [
      {
        "record_id": 1,
        "file_path": "src/index.js",
        "folder_path": "src",
        "file_type": "file",
        "size": 2048,
        "change_status": "unchanged",
        "last_checked_at": "2025-12-22T03:00:00Z"
      }
    ]
  }
}
```

---

### 3. Get Changes

**Endpoint:** `GET /api/repositories/{repoName}/changes?status=new`

**Description:** Retrieves files with specific change status.

**Query Parameters:**
- `status` (optional): Filter by status ('new', 'modified', 'deleted')

**Response:**
```json
{
  "status": "success",
  "data": {
    "repo_name": "my-project",
    "filter": "new",
    "total": 5,
    "changes": [
      {
        "record_id": 123,
        "file_path": "src/components/Header.js",
        "file_type": "file",
        "change_status": "new",
        "size": 1024,
        "updated_at": "2025-12-22T03:00:00Z"
      }
    ]
  }
}
```

---

### 4. Get Notifications

**Endpoint:** `GET /api/repositories/{repoName}/notifications`

**Description:** Retrieves change notifications.

**Query Parameters:**
- `sent` (optional): Filter by sent status (true/false)
- `type` (optional): Filter by type ('email', 'ui_alert', 'message_log')
- `change_type` (optional): Filter by change type ('new', 'modified', 'deleted')

**Response:**
```json
{
  "status": "success",
  "data": {
    "repo_name": "my-project",
    "total": 10,
    "notifications": [
      {
        "id": 1,
        "notification_type": "email",
        "change_type": "new",
        "file_path": "src/components/Header.js",
        "message": "File 'src/components/Header.js' was added to the repository",
        "sent": false,
        "sent_at": null,
        "created_at": "2025-12-22T03:00:00Z"
      }
    ]
  }
}
```

---

### 5. Get Sync Logs

**Endpoint:** `GET /api/repositories/{repoName}/sync-logs?status=completed`

**Description:** Retrieves sync operation logs.

**Query Parameters:**
- `status` (optional): Filter by status ('pending', 'running', 'completed', 'failed')

**Response:**
```json
{
  "status": "success",
  "data": {
    "repo_name": "my-project",
    "total": 5,
    "logs": [
      {
        "id": 1,
        "status": "completed",
        "started_at": "2025-12-22T03:00:00Z",
        "completed_at": "2025-12-22T03:00:05Z",
        "runtime_seconds": 5,
        "files_scanned": 150,
        "new_files": 5,
        "modified_files": 3,
        "deleted_files": 2,
        "total_changes": 10,
        "error_message": null,
        "created_at": "2025-12-22T03:00:00Z"
      }
    ]
  }
}
```

---

### 6. Get Latest Sync

**Endpoint:** `GET /api/repositories/{repoName}/latest-sync`

**Description:** Retrieves the most recent sync operation.

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "repo_name": "my-project",
    "status": "completed",
    "started_at": "2025-12-22T03:00:00Z",
    "completed_at": "2025-12-22T03:00:05Z",
    "runtime_seconds": 5,
    "statistics": {
      "files_scanned": 150,
      "new_files": 5,
      "modified_files": 3,
      "deleted_files": 2,
      "total_changes": 10
    },
    "error_message": null,
    "created_at": "2025-12-22T03:00:00Z"
  }
}
```

---

### 7. Mark Notification as Sent

**Endpoint:** `PATCH /api/repositories/notifications/{notificationId}/mark-sent`

**Description:** Marks a notification as sent.

**Response:**
```json
{
  "status": "success",
  "message": "Notification marked as sent",
  "data": {
    "id": 1,
    "sent": true,
    "sent_at": "2025-12-22T03:05:00Z"
  }
}
```

---

## Git Platform Support

### GitHub
**API URL Format:** `https://api.github.com/repos/{owner}/{repo}`

**Authentication:** Bearer token in header

**Example:**
```json
{
  "repo_url": "https://api.github.com/repos/laravel/framework",
  "token": "ghp_xxxxxxxxxxxxx"
}
```

### GitLab
**API URL Format:** `https://gitlab.com/api/v4/projects/{project_id}`

**Authentication:** PRIVATE-TOKEN header

**Example:**
```json
{
  "repo_url": "https://gitlab.com/api/v4/projects/12345678",
  "token": "glpat-xxxxxxxxxxxxx"
}
```

### Bitbucket
**API URL Format:** `https://api.bitbucket.org/2.0/repositories/{workspace}/{repo_slug}`

**Authentication:** Basic auth with token

**Example:**
```json
{
  "repo_url": "https://api.bitbucket.org/2.0/repositories/myworkspace/myrepo",
  "token": "your_app_password"
}
```

---

## Change Detection Logic

The service detects changes by:

1. **New Files**: Files in Git API response but not in database
2. **Modified Files**: Files with different SHA hash or size
3. **Deleted Files**: Files in database but not in Git API response

---

## Notification System

When changes are detected, the service automatically creates notifications for three channels:

1. **Email**: For sending email alerts
2. **UI Alert**: For displaying in-app notifications
3. **Message Log**: For audit trail and logging

Each notification includes:
- Change type (new/modified/deleted)
- File path
- Human-readable message
- Metadata (file type, size, SHA)

---

## Example Usage

### 1. Initial Sync
```bash
curl -X POST http://localhost:8000/api/repositories/sync \
  -H "Content-Type: application/json" \
  -d '{
    "repo_url": "https://api.github.com/repos/laravel/framework",
    "repo_name": "laravel-framework",
    "branch": "main",
    "token": "your_github_token"
  }'
```

### 2. Check for New Files
```bash
curl http://localhost:8000/api/repositories/laravel-framework/changes?status=new
```

### 3. Get Unsent Notifications
```bash
curl http://localhost:8000/api/repositories/laravel-framework/notifications?sent=false
```

### 4. View Directory Tree
```bash
curl http://localhost:8000/api/repositories/laravel-framework/structure
```

---

## Error Handling

All endpoints return consistent error responses:

```json
{
  "status": "error",
  "message": "Error description",
  "error": "Detailed error message"
}
```

HTTP Status Codes:
- `200`: Success
- `422`: Validation error
- `500`: Server error

---

## Performance Considerations

- **Pagination**: For large repositories, the service fetches all pages automatically
- **Indexing**: Database indexes on `repo_name`, `file_path`, and `change_status`
- **Timeout**: HTTP requests timeout after 30 seconds
- **Batch Processing**: Changes are processed in batches for efficiency

---

## Future Enhancements

- [ ] Webhook support for real-time updates
- [ ] Email notification sending
- [ ] Scheduled sync jobs
- [ ] Repository comparison between branches
- [ ] File content diffing
- [ ] User access control
- [ ] API rate limiting
- [ ] Caching layer

---

## Support

For issues or questions, please refer to the project documentation or contact the development team.
