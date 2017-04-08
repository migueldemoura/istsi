#!/bin/bash

[ -z "${MYSQL_HOST}" ] && { echo "=> MYSQL_HOST cannot be empty" && exit 1; }
[ -z "${MYSQL_PORT}" ] && { export MYSQL_PORT=3306; }
[ -z "${MYSQL_USER}" ] && { echo "=> MYSQL_USER cannot be empty" && exit 1; }
[ -z "${MYSQL_PASSWORD}" ] && { echo "=> MYSQL_PASSWORD cannot be empty" && exit 1; }

if [ -n "${BACKUP_ON_INIT}" ]; then
    /backup.sh
elif [ -n "${RESTORE_LATEST_ON_INIT}" ]; then
    until nc -z ${MYSQL_HOST} ${MYSQL_PORT}
    do
        echo "[Note] Waiting for the mysql container"
        sleep 1
    done
    ls -d -1 /backup/* | tail -1 | xargs /restore.sh
fi

echo "0 0 * * * /backup.sh" > /crontab.conf
crontab  /crontab.conf
echo "[Note] Running cron job"
exec cron -f
