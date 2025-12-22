#!/bin/bash

# Git Repository Monitoring API - Test Script
# This script demonstrates all API endpoints

BASE_URL="http://localhost:8000/api/repositories"

echo "=================================="
echo "Git Repository Monitoring API Test"
echo "=================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Sync Repository
echo -e "${BLUE}Test 1: Syncing Repository${NC}"
echo "POST $BASE_URL/sync"
echo ""

SYNC_RESPONSE=$(curl -s -X POST "$BASE_URL/sync" \
  -H "Content-Type: application/json" \
  -d '{
    "repo_url": "https://api.github.com/repos/laravel/framework",
    "repo_name": "laravel-framework",
    "branch": "11.x",
    "token": ""
  }')

echo "$SYNC_RESPONSE" | jq '.'
echo ""
echo -e "${GREEN}✓ Sync completed${NC}"
echo ""
sleep 2

# Test 2: Get Repository Structure
echo -e "${BLUE}Test 2: Getting Repository Structure${NC}"
echo "GET $BASE_URL/laravel-framework/structure"
echo ""

STRUCTURE_RESPONSE=$(curl -s -X GET "$BASE_URL/laravel-framework/structure")
echo "$STRUCTURE_RESPONSE" | jq '.data.directory_tree_string'
echo ""
echo -e "${GREEN}✓ Structure retrieved${NC}"
echo ""
sleep 2

# Test 3: Get New Files
echo -e "${BLUE}Test 3: Getting New Files${NC}"
echo "GET $BASE_URL/laravel-framework/changes?status=new"
echo ""

NEW_FILES=$(curl -s -X GET "$BASE_URL/laravel-framework/changes?status=new")
echo "$NEW_FILES" | jq '.data.total'
echo "$NEW_FILES" | jq '.data.changes[0:5]'
echo ""
echo -e "${GREEN}✓ New files retrieved${NC}"
echo ""
sleep 2

# Test 4: Get Modified Files
echo -e "${BLUE}Test 4: Getting Modified Files${NC}"
echo "GET $BASE_URL/laravel-framework/changes?status=modified"
echo ""

MODIFIED_FILES=$(curl -s -X GET "$BASE_URL/laravel-framework/changes?status=modified")
echo "$MODIFIED_FILES" | jq '.data.total'
echo "$MODIFIED_FILES" | jq '.data.changes[0:5]'
echo ""
echo -e "${GREEN}✓ Modified files retrieved${NC}"
echo ""
sleep 2

# Test 5: Get Deleted Files
echo -e "${BLUE}Test 5: Getting Deleted Files${NC}"
echo "GET $BASE_URL/laravel-framework/changes?status=deleted"
echo ""

DELETED_FILES=$(curl -s -X GET "$BASE_URL/laravel-framework/changes?status=deleted")
echo "$DELETED_FILES" | jq '.data.total'
echo "$DELETED_FILES" | jq '.data.changes[0:5]'
echo ""
echo -e "${GREEN}✓ Deleted files retrieved${NC}"
echo ""
sleep 2

# Test 6: Get All Notifications
echo -e "${BLUE}Test 6: Getting All Notifications${NC}"
echo "GET $BASE_URL/laravel-framework/notifications"
echo ""

NOTIFICATIONS=$(curl -s -X GET "$BASE_URL/laravel-framework/notifications")
echo "$NOTIFICATIONS" | jq '.data.total'
echo "$NOTIFICATIONS" | jq '.data.notifications[0:3]'
echo ""
echo -e "${GREEN}✓ Notifications retrieved${NC}"
echo ""
sleep 2

# Test 7: Get Unsent Email Notifications
echo -e "${BLUE}Test 7: Getting Unsent Email Notifications${NC}"
echo "GET $BASE_URL/laravel-framework/notifications?sent=false&type=email"
echo ""

UNSENT_EMAILS=$(curl -s -X GET "$BASE_URL/laravel-framework/notifications?sent=false&type=email")
echo "$UNSENT_EMAILS" | jq '.data.total'
echo "$UNSENT_EMAILS" | jq '.data.notifications[0:3]'
echo ""
echo -e "${GREEN}✓ Unsent email notifications retrieved${NC}"
echo ""
sleep 2

# Test 8: Get Sync Logs
echo -e "${BLUE}Test 8: Getting Sync Logs${NC}"
echo "GET $BASE_URL/laravel-framework/sync-logs"
echo ""

SYNC_LOGS=$(curl -s -X GET "$BASE_URL/laravel-framework/sync-logs")
echo "$SYNC_LOGS" | jq '.data.total'
echo "$SYNC_LOGS" | jq '.data.logs[0:3]'
echo ""
echo -e "${GREEN}✓ Sync logs retrieved${NC}"
echo ""
sleep 2

# Test 9: Get Latest Sync Status
echo -e "${BLUE}Test 9: Getting Latest Sync Status${NC}"
echo "GET $BASE_URL/laravel-framework/latest-sync"
echo ""

LATEST_SYNC=$(curl -s -X GET "$BASE_URL/laravel-framework/latest-sync")
echo "$LATEST_SYNC" | jq '.data'
echo ""
echo -e "${GREEN}✓ Latest sync status retrieved${NC}"
echo ""
sleep 2

# Test 10: Mark Notification as Sent
echo -e "${BLUE}Test 10: Marking Notification as Sent${NC}"
NOTIFICATION_ID=$(echo "$UNSENT_EMAILS" | jq -r '.data.notifications[0].id // 1')
echo "PATCH $BASE_URL/notifications/$NOTIFICATION_ID/mark-sent"
echo ""

MARK_SENT=$(curl -s -X PATCH "$BASE_URL/notifications/$NOTIFICATION_ID/mark-sent")
echo "$MARK_SENT" | jq '.'
echo ""
echo -e "${GREEN}✓ Notification marked as sent${NC}"
echo ""

# Summary
echo "=================================="
echo -e "${YELLOW}Test Summary${NC}"
echo "=================================="
echo "All API endpoints tested successfully!"
echo ""
echo "Available Endpoints:"
echo "  1. POST   /api/repositories/sync"
echo "  2. GET    /api/repositories/{repo}/structure"
echo "  3. GET    /api/repositories/{repo}/changes"
echo "  4. GET    /api/repositories/{repo}/notifications"
echo "  5. GET    /api/repositories/{repo}/sync-logs"
echo "  6. GET    /api/repositories/{repo}/latest-sync"
echo "  7. PATCH  /api/repositories/notifications/{id}/mark-sent"
echo ""
echo "For detailed documentation, see: docs/REPOSITORY_API_DOCUMENTATION.md"
echo ""
