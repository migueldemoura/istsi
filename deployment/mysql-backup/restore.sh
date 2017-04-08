#!/bin/bash

echo "[Note] Restoring database from $1"

if mysql -h${MYSQL_HOST} -P${MYSQL_PORT} -u${MYSQL_USER} -p${MYSQL_PASSWORD} < $1 ;then
    echo "[Note] Restore succeeded"
else
    echo "[Note] Restore failed"
fi

echo "[Note] Restore ended"
