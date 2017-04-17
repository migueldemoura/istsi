#!/bin/bash

[ -z "${MYSQL_HOST}" ] && { echo "[Error] MYSQL_HOST cannot be empty" && exit -1; }
[ -z "${MYSQL_PORT}" ] && { export MYSQL_PORT=3306; }
[ -z "${MYSQL_USER}" ] && { echo "[Error] MYSQL_USER cannot be empty" && exit -1; }
[ -z "${MYSQL_PASSWORD}" ] && { echo "[Error] MYSQL_PASSWORD cannot be empty" && exit -1; }

envsubst '${MYSQL_HOST} ${MYSQL_PORT} ${MYSQL_USER} ${MYSQL_PASSWORD} ${MAX_BACKUPS}' < /backup.sh > /backup.sh.new && mv /backup.sh.new /backup.sh
envsubst '${MYSQL_HOST} ${MYSQL_PORT} ${MYSQL_USER} ${MYSQL_PASSWORD}' < /restore.sh > /restore.sh.new && mv /restore.sh.new /restore.sh
chmod +x /backup.sh /restore.sh

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
