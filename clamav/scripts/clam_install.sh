#!/usr/bin/env bash
# -*- coding: utf-8 -*-

echo "Prepare folder for clamav..."
mkdir -p \
    "${PLATFORM_APP_DIR}/var/log" \
    "${PLATFORM_APP_DIR}/var/lib" \
    "${PLATFORM_APP_DIR}/var/etc" \
    "${PLATFORM_APP_DIR}/data/folder2scan" \
    "${PLATFORM_APP_DIR}/data/quarantine" 

echo "Move config on mount..."
cp "${PLATFORM_APP_DIR}/etc/clamd.conf" "${PLATFORM_APP_DIR}/var/etc/"