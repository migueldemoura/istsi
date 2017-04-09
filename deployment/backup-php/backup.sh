#!/bin/bash

# Remove leading / from PATH_TO_BACKUP so as to use "-C /"
PATH_TO_BACKUP=$(echo ${PATH_TO_BACKUP} | sed -e 's/^\///')

BACKUP_NAME=$(date +\%Y.\%m.\%d.\%H\%M\%S).tar.gz
BACKUP_CMD=$(tar -czf /backup/${BACKUP_NAME} -C / ${PATH_TO_BACKUP})

echo "[Note] Creating backup ${BACKUP_NAME}"

if ${BACKUP_CMD}; then
    echo "[Note] Backup succeeded"
else
    echo "[Error] Backup failed"
    rm -rf /backup/${BACKUP_NAME}
fi

echo "[Note] Backup ended"