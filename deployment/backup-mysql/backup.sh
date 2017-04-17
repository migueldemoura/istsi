#!/bin/bash

BACKUP_NAME=$(date +\%Y.\%m.\%d.\%H\%M\%S).sql
BACKUP_CMD="mysqldump -h${MYSQL_HOST} -P${MYSQL_PORT} -u${MYSQL_USER} -p${MYSQL_PASSWORD} -r /backup/${BACKUP_NAME} --all-databases"

while ! mysqladmin ping -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" --silent; do
    echo "[Note] Waiting for the mysql container"
    sleep 5
done

echo "[Note] Creating backup ${BACKUP_NAME}"

if ${BACKUP_CMD}; then
    echo "[Note] Backup succeeded"
else
    echo "[Error] Backup failed"
    rm -rf /backup/${BACKUP_NAME}
fi

if [ -n "${MAX_BACKUPS}" ]; then
    while [ $(ls /backup -N1 | wc -l) -gt "${MAX_BACKUPS}" ];
    do
        BACKUP_TO_BE_DELETED=$(ls /backup -N1 | sort | head -n 1)
        echo "[Note] Backup ${BACKUP_TO_BE_DELETED} is deleted"
        rm -rf /backup/"${BACKUP_TO_BE_DELETED}"
    done
fi

echo "[Note] Backup ended"
