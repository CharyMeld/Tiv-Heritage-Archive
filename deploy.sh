#!/bin/bash
# Deploy to VPS: tiv.teamodigitalsolutions.com
# Usage: ./deploy.sh

set -e

VPS="root@72.62.119.237"
REMOTE="/var/www/tiv/"
LOCAL="/opt/lampp/htdocs/Tiv-Heritage-Archive/"

rsync -avz --progress \
  --exclude='.git' \
  --exclude='config/database.php' \
  --exclude='storage/cache/*' \
  --exclude='uploads/*' \
  --exclude='*.sql' \
  -e "ssh -o StrictHostKeyChecking=no" \
  "$LOCAL" "$VPS:$REMOTE"

echo ""
echo "Deploy complete → https://tiv.teamodigitalsolutions.com"
