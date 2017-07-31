#!/bin/bash

export PGPASSWORD="${POSTGRES_PASSWORD}"

until psql -h "${POSTGRES_HOST}" -p "${POSTGRES_PORT}" -U postgres -w -c '\l' > /dev/null 2>&1; do
    echo "[Note] Waiting for postgres container"
    sleep 1
done

echo "[Note] Restoring backup from $1"

if psql -q -h "${POSTGRES_HOST}" -p "${POSTGRES_PORT}" -U postgres -w -f "$1" > /dev/null; then
    echo "[Note] Restore succeeded"
else
    echo "[Error] Restore failed"
fi

echo "[Note] Restore ended"
