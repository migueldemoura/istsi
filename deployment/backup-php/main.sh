#!/bin/bash

envsubst '${PATH_TO_BACKUP}' < /backup.sh > /backup.sh.new && mv /backup.sh.new /backup.sh
chmod +x /backup.sh

if [ -n "${BACKUP_ON_INIT}" ]; then
    /backup.sh
elif [ -n "${RESTORE_LATEST_ON_INIT}" ]; then
    # Find the last modified file
    find /backup/* -maxdepth 1 -type f | xargs -d '\n' ls -t | head -n1 | xargs /restore.sh
fi

echo -e "0 0 * * * root bash /backup.sh >> /var/log/cron.log 2>&1" > /etc/cron.d/backup-cron
touch /var/log/cron.log
echo "[Note] Running cron job"
exec cron -f
