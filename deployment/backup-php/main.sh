#!/bin/bash

if [ -n "${BACKUP_ON_INIT}" ]; then
    /backup.sh
elif [ -n "${RESTORE_LATEST_ON_INIT}" ]; then
    # Find the last modified file
    find /backup/* -maxdepth 1 -type f | xargs -d '\n' ls -t | head -n1 | xargs /restore.sh
fi

echo "0 0 * * * /backup.sh" > /crontab.conf
crontab  /crontab.conf
echo "[Note] Running cron job"
exec cron -f
