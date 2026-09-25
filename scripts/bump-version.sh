#!/usr/bin/env bash
# ==============================================================================
# Unified Version Bumping Script for Mpesa Analyzer Ecosystem
# Updates version across:
#   1. web/app/Config/version.json
#   2. ml/app/main.py
#   3. app/build.gradle.kts (Android companion app)
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

if [ $# -lt 1 ]; then
    echo "Usage: $0 <version> [title] [highlight1] [highlight2] ..."
    echo "Example: $0 3.5.0 \"AI Assistant & Mobile Gateway\" \"Unified v3.5.0 release\""
    exit 1
fi

NEW_VERSION="$1"
shift || true
TITLE="${1:-"Release ${NEW_VERSION}"}"
shift || true

TODAY=$(date +%Y-%m-%d)

echo "==> Bumping version to ${NEW_VERSION} (${TODAY})..."

# 1. Update web/app/Config/version.json
VERSION_JSON="${ROOT_DIR}/web/app/Config/version.json"
if [ -f "${VERSION_JSON}" ]; then
    echo "Updating ${VERSION_JSON}..."
    python3 -c "
import json, sys

path = '${VERSION_JSON}'
with open(path, 'r') as f:
    data = json.load(f)

new_version = '${NEW_VERSION}'
today = '${TODAY}'
title = '${TITLE}'
highlights = sys.argv[1:] if len(sys.argv) > 1 else ['Version ' + new_version + ' updates and stability enhancements']

data['version'] = new_version
data['release_date'] = today

new_release = {
    'version': new_version,
    'date': today,
    'title': title,
    'highlights': highlights
}

releases = data.get('releases', [])
releases = [r for r in releases if r.get('version') != new_version]
releases.insert(0, new_release)
data['releases'] = releases
data['changelog'] = highlights

with open(path, 'w') as f:
    json.dump(data, f, indent=4)
print(f'Successfully updated {path} to v{new_version}')
" "$@"
fi

# 2. Update ml/app/main.py
MAIN_PY="${ROOT_DIR}/ml/app/main.py"
if [ -f "${MAIN_PY}" ]; then
    echo "Updating ${MAIN_PY}..."
    sed -i -E "s/version=\"[0-9]+\.[0-9]+\.[0-9]+\"/version=\"${NEW_VERSION}\"/" "${MAIN_PY}"
    echo "Updated ${MAIN_PY}"
fi

# 3. Update Android app if directory exists
ANDROID_GRADLE="/home/niccher/AndroidStudioProjects/Mpesa_Analyzer_App/app/build.gradle.kts"
if [ -f "${ANDROID_GRADLE}" ]; then
    echo "Updating Android build.gradle.kts..."
    sed -i -E "s/versionName = \"[^\"]+\"/versionName = \"${NEW_VERSION}\"/" "${ANDROID_GRADLE}"
    echo "Updated Android versionName to ${NEW_VERSION}"
fi

echo "==> Version bump complete! New version: ${NEW_VERSION}"
