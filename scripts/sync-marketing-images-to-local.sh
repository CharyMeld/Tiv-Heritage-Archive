#!/usr/bin/env bash
# Pulls generated marketing images for already-published Facebook posts
# down from the VPS into a local archive. Deletes the VPS copy only once
# the post has been published for 24+ hours (list-published-images.php
# decides this server-side) — the grace period keeps recently-published
# images live on the VPS so the Publishing Queue's WhatsApp share button
# can still attach them. Safe to re-run: already-archived-and-deleted
# images simply stop appearing in the list.
#
# Intended to run on a schedule via this machine's own crontab (the VPS
# can't reach back into this machine to push files, so the pull direction
# has to originate here).

set -euo pipefail

VPS_HOST="root@72.62.119.237"
VPS_APP_PATH="/var/www/tiv"
LOCAL_ARCHIVE="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/storage/marketing_images_archive"

mkdir -p "$LOCAL_ARCHIVE"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Checking for published images to archive..."

ROWS=$(ssh "$VPS_HOST" "php $VPS_APP_PATH/bin/list-published-images.php")

if [ -z "$ROWS" ]; then
    echo "Nothing to sync."
    exit 0
fi

while IFS=$'\t' read -r rel_path deletable; do
    [ -z "$rel_path" ] && continue

    remote_full="$VPS_APP_PATH/uploads/$rel_path"
    local_full="$LOCAL_ARCHIVE/$(basename "$rel_path")"

    mkdir -p "$(dirname "$local_full")"

    if rsync -az -e ssh "$VPS_HOST:$remote_full" "$local_full" </dev/null; then
        if [ "$deletable" = "1" ]; then
            ssh "$VPS_HOST" "rm -f '$remote_full'" </dev/null
            echo "Archived and removed from VPS (24h+ old): $rel_path"
        else
            echo "Archived, kept on VPS for now (published <24h ago): $rel_path"
        fi
    else
        echo "Failed to sync $rel_path — leaving it on the VPS for next run." >&2
    fi
done <<< "$ROWS"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Done."
