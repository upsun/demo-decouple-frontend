#!/usr/bin/env bash
# -*- coding: utf-8 -*-

echo "Update Virus database..."
freshclam --config-file="${PLATFORM_APP_DIR}/etc/freshclam.conf"