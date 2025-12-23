# Git Repository Monitoring Service

A comprehensive backend service for monitoring Git repositories with automatic change detection, notification system, and detailed tracking.

## 🚀 Features

### Core Functionality
- ✅ **Multi-Platform Git Support**: GitHub, GitLab, Bitbucket, and generic Git APIs
- ✅ **Complete Repository Scanning**: Fetches full directory hierarchy including all nested folders
- ✅ **Automatic Change Detection**: Identifies new, modified, and deleted files/folders
- ✅ **Database Storage**: Stores complete repository structure with comprehensive metadata
- ✅ **Smart Notifications**: Triggers email, UI alerts, and message logs for all changes
- ✅ **Sync Logging**: Tracks every sync operation with detailed statistics and runtime metrics
- ✅ **Directory Tree Visualization**: Builds and formats hierarchical folder structure

### Change Detection
The service automatically detects:
- **New Files/Folders**: Items present in repository but not in database
- **Modified Files**: Files with changed SHA hash or different size
- **Deleted Files/Folders**: Items in database but removed from repository

### Notification Channels
When changes are detected, notifications are automatically created for:
1. **Email** - For sending email alerts to stakeholders
2. **UI Alert** - For displaying in-app notifications
3. **Message Log** - For audit trail and system logging

## 📋 Requirements

- PHP 8.2+
- Laravel 12.x
- MySQL 8.0+
- Composer
- Git API access token (GitHub, GitLab, or Bitbucket)

## 🛠️ Installation

### 1. Database Setup

Run migrations to create required tables:

```bash
php artisan migrate
```

This creates three tables:
- `repository_files` - Stores all files and folders
- `repository_change_notifications` - Tracks change notifications
- `repository_sync_logs` - Logs sync operations

### 2. Configuration

No additional configuration needed! The service auto-detects the Git platform from the repository URL.

## 📖 Usage

### Basic Sync Example

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

### Response Example

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
    "directory_tree_string": "📁 src/\n  📁 components/\n    📄 Header.js [new]\n📄 README.md\n",
    "changes": {
      "new": [...],
      "modified": [...],
      "deleted": [...]
    }
  }
}
```

## 🔌 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/repositories/sync` | Sync repository and detect changes |
| GET | `/api/repositories/{repo}/structure` | Get complete directory tree |
| GET | `/api/repositories/{repo}/changes` | Get files with specific change status |
| GET | `/api/repositories/{repo}/notifications` | Get change notifications |
| GET | `/api/repositories/{repo}/sync-logs` | Get sync operation logs |
| GET | `/api/repositories/{repo}/latest-sync` | Get most recent sync status |
| PATCH | `/api/repositories/notifications/{id}/mark-sent` | Mark notification as sent |

## 🌐 Supported Git Platforms

### GitHub
```json
{
  "repo_url": "https://api.github.com/repos/{owner}/{repo}",
  "token": "ghp_xxxxxxxxxxxxx"
}
```

### GitLab
```json
{
  "repo_url": "https://gitlab.com/api/v4/projects/{project_id}",
  "token": "glpat-xxxxxxxxxxxxx"
}
```

### Bitbucket
```json
{
  "repo_url": "https://api.bitbucket.org/2.0/repositories/{workspace}/{repo}",
  "token": "your_app_password"
}
```

## 📊 Database Schema

### repository_files
Stores complete repository structure with metadata:
- File/folder paths and hierarchy
- File sizes and Git SHA hashes
- Change status tracking
- Last sync timestamps

### repository_change_notifications
Tracks all change notifications:
- Notification type (email/UI/log)
- Change type (new/modified/deleted)
- Sent status and timestamps
- Additional metadata

### repository_sync_logs
Logs every sync operation:
- Sync status and runtime
- Files scanned and changes detected
- Error messages if failed
- Detailed statistics

## 🧪 Testing

Run the comprehensive test script:

```bash
./tests/api_test.sh
```

This tests all endpoints and displays formatted output.

## 📚 Documentation

For complete API documentation, see:
- [API Documentation](docs/REPOSITORY_API_DOCUMENTATION.md)

## 🔍 Example Workflows

### 1. Initial Repository Setup
```bash
# Sync repository for the first time
curl -X POST http://localhost:8000/api/repositories/sync \
  -H "Content-Type: application/json" \
  -d '{
    "repo_url": "https://api.github.com/repos/myorg/myrepo",
    "repo_name": "my-project",
    "branch": "main",
    "token": "your_token"
  }'
```

### 2. Check for New Files
```bash
curl http://localhost:8000/api/repositories/my-project/changes?status=new
```

### 3. Get Unsent Notifications
```bash
curl http://localhost:8000/api/repositories/my-project/notifications?sent=false
```

### 4. View Directory Tree
```bash
curl http://localhost:8000/api/repositories/my-project/structure
```

### 5. Monitor Sync Status
```bash
curl http://localhost:8000/api/repositories/my-project/latest-sync
```

## 🎯 Use Cases

1. **CI/CD Pipeline Monitoring**: Track changes to deployment configurations
2. **Code Review Automation**: Detect new files requiring review
3. **Compliance Tracking**: Monitor changes to critical files
4. **Team Notifications**: Alert team members of repository changes
5. **Audit Trail**: Maintain complete history of repository modifications
6. **Backup Verification**: Ensure all files are properly tracked

## 🔧 Advanced Features

### Change Detection Logic
- **SHA Comparison**: Primary method for detecting file modifications
- **Size Comparison**: Fallback when SHA is unavailable
- **Path Tracking**: Monitors complete file paths for deletions

### Performance Optimization
- Database indexing on frequently queried fields
- Batch processing for large repositories
- Automatic pagination for API requests
- 30-second timeout for API calls

## 🚦 Status Codes

- `200` - Success
- `422` - Validation error
- `500` - Server error

## 📝 Models

### RepositoryFile
- Tracks individual files and folders
- Provides helper methods for status checks
- Includes scopes for filtering

### RepositoryChangeNotification
- Manages change notifications
- Supports multiple notification types
- Tracks sent status

### RepositorySyncLog
- Records sync operations
- Calculates runtime statistics
- Provides status management

## 🔐 Security

- API tokens are never stored in database
- All requests use HTTPS (in production)
- Input validation on all endpoints
- SQL injection protection via Eloquent ORM

## 🎨 Directory Tree Format

The service generates a visual directory tree:

```
📁 root/
  📁 src/
    📁 components/
      📄 Header.js [new]
      📄 Footer.js
    📄 index.js [modified]
  📁 tests/
    📁 unit/
      📄 example.test.js
  📄 README.md
  📄 package.json [modified]
```

## 🤝 Contributing

This is part of the CICD-Manager project. For contributions, please follow the project's contribution guidelines.

## 📄 License

This service is part of the CICD-Manager project and follows the same license.

## 🆘 Support

For issues or questions:
1. Check the [API Documentation](docs/REPOSITORY_API_DOCUMENTATION.md)
2. Review the test script examples
3. Contact the development team

## 🔮 Future Enhancements

- [ ] Real-time webhook support
- [ ] Actual email sending integration
- [ ] Scheduled sync jobs via Laravel scheduler
- [ ] Branch comparison features
- [ ] File content diffing
- [ ] User access control and permissions
- [ ] API rate limiting
- [ ] Redis caching layer
- [ ] Slack/Discord notification integration
- [ ] Custom notification templates

---

**Built with Laravel 12 and ❤️**
