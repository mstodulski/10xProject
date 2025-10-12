#!/bin/bash

###############################################################################
# Refresh Demo Data Script
#
# This script refreshes the demo database with fresh fixtures data.
# Designed to be run every Wednesday to reset the demo environment.
#
# Usage:
#   ./refresh-demo-data.sh [environment]
#
# Examples:
#   ./refresh-demo-data.sh         # Runs in 'dev' environment
#   ./refresh-demo-data.sh prod    # Runs in 'prod' environment
#
# Cron example (every Wednesday at 00:00):
#   0 0 * * 3 /var/www/refresh-demo-data.sh prod >> /var/log/demo-refresh.log 2>&1
###############################################################################

set -e  # Exit on error

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV="${1:-dev}"
CONSOLE="php ${SCRIPT_DIR}/bin/console"

echo "=========================================="
echo "Demo Data Refresh Script"
echo "=========================================="
echo "Environment: ${ENV}"
echo "Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo "Day of week: $(date '+%A')"
echo ""

# Check if it's Wednesday (optional - remove if you want to run anytime)
DAY_OF_WEEK=$(date '+%u')
if [ "$DAY_OF_WEEK" != "3" ]; then
    echo "⚠️  WARNING: Today is not Wednesday!"
    echo "Fixtures are designed to be loaded on Wednesdays."
    echo ""
fi

# Step 1: Drop schema
echo "Step 1: Dropping database schema..."
$CONSOLE doctrine:schema:drop --force --env=$ENV
echo "✓ Schema dropped"
echo ""

# Step 2: Create schema
echo "Step 2: Creating database schema..."
$CONSOLE doctrine:schema:create --env=$ENV
echo "✓ Schema created"
echo ""

# Step 3: Load fixtures
echo "Step 3: Loading fixtures..."
$CONSOLE doctrine:fixtures:load --no-interaction --env=$ENV
echo "✓ Fixtures loaded"
echo ""

# Step 4: Verify
echo "Step 4: Verifying data..."
USER_COUNT=$($CONSOLE doctrine:query:dql "SELECT COUNT(u) FROM App\Entity\User u" --env=$ENV | grep -oP '\d+' | head -1)
INSPECTION_COUNT=$($CONSOLE doctrine:query:dql "SELECT COUNT(i) FROM App\Entity\Inspection i" --env=$ENV | grep -oP '\d+' | head -1)

echo "Users created: ${USER_COUNT}"
echo "Inspections created: ${INSPECTION_COUNT}"
echo ""

echo "=========================================="
echo "✓ Demo data refresh completed successfully!"
echo "=========================================="
echo ""
echo "You can now login with:"
echo "  - Username: konsultant1 (or konsultant2, 3, 4)"
echo "  - Username: inspektor1"
echo "  - Password: test (for all users)"
echo ""
