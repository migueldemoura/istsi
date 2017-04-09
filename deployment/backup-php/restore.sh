#!/bin/bash

echo "[Note] Restoring backup from $1"

mkdir -p /backup/extracted && rm -rf /backup/extracted/*

if tar -xzf $1 -C /backup/extracted && rm -rf /data/* && mv /backup/extracted/data/* /data;
then
    echo "[Note] Restore succeeded"
else
    echo "[Note] Restore failed"
fi

rm -rf /backup/extracted

echo "[Note] Restore ended"
