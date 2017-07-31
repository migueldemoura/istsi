#!/bin/bash

export PGPASSWORD="${POSTGRES_PASSWORD}"

BACKUP_NAME=$(date +\%Y.\%m.\%d.\%H\%M\%S).sql

until psql -h "${POSTGRES_HOST}" -p "${POSTGRES_PORT}" -U postgres -w -c '\l' > /dev/null 2>&1; do
    echo "[Note] Waiting for postgres container"
    sleep 1
done

echo "[Note] Creating backup ${BACKUP_NAME}"

if pg_dumpall -c -h "${POSTGRES_HOST}" -p "${POSTGRES_PORT}" -U postgres -w -f /backup/"${BACKUP_NAME}" > /dev/null; then
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
