#!/bin/bash

while ! mysqladmin ping -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" --silent; do
    echo "[Note] Waiting for the mysql container"
    sleep 1
done

echo "[Note] Restoring backup from $1"

if mysql -h"${MYSQL_HOST}" -P"${MYSQL_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" < "$1" ;then
    echo "[Note] Restore succeeded"
else
    echo "[Note] Restore failed"
fi

echo "[Note] Restore ended"
