#!/usr/bin/env bash
# -*- coding: utf-8 -*-

echo "Trigger scan by one-time call..."
clamscan  \
    --database="${PLATFORM_APP_DIR}/var/lib" \
    --log="${PLATFORM_APP_DIR}/var/log/scan.log" \
    --move="${PLATFORM_APP_DIR}/mydata/quarantine" \
    --recursive \
    "${PLATFORM_APP_DIR}/mydata/folder2scan"