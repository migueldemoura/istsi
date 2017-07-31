#!/bin/bash

[ -z "${POSTGRES_HOST}" ] && { echo "[Error] POSTGRES_HOST cannot be empty" && exit 1; }
[ -z "${POSTGRES_PORT}" ] && { export POSTGRES_PORT=5432; }
[ -z "${POSTGRES_PASSWORD}" ] && { echo "[Error] POSTGRES_PASSWORD cannot be empty" && exit 1; }

envsubst '${POSTGRES_HOST} ${POSTGRES_PORT} ${POSTGRES_PASSWORD} ${MAX_BACKUPS}' < /backup.sh > /backup.sh.new && mv /backup.sh.new /backup.sh
envsubst '${POSTGRES_HOST} ${POSTGRES_PORT} ${POSTGRES_PASSWORD}' < /restore.sh > /restore.sh.new && mv /restore.sh.new /restore.sh
chmod +x /backup.sh /restore.sh

if [ -n "${BACKUP_ON_INIT}" ]; then
    /backup.sh
elif [ -n "${RESTORE_LATEST_ON_INIT}" ]; then
    # Restore last modified
    find /backup/* -maxdepth 1 -type f | xargs -d '\n' ls -t | head -n1 | xargs /restore.sh
fi

echo "0 0 * * * /backup.sh" > /crontab.conf
crontab  /crontab.conf
echo "[Note] Running cron job"
exec cron -f
