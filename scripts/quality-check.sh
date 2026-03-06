#!/usr/bin/env bash
set -euo pipefail

echo "[1/4] PHP lint"
for f in $(rg --files -g '*.php'); do
  php -l "$f" >/dev/null
  done

echo "[2/4] Check for hardcoded db credentials"
if rg -n "u854527538|0044Ki05@123" -g "!scripts/quality-check.sh" >/dev/null; then
  echo "Hardcoded credentials detected"
  exit 1
fi

echo "[3/4] Check ads.txt placeholder"
if rg -n "ca-pub-XXXXXXXXXXXXXXXX" ads.txt >/dev/null; then
  echo "ads.txt still has placeholder publisher id"
  exit 1
fi

echo "[4/4] Quick malformed closing tag scan"
if rg -n "</p\s*$" -g '*.php' >/dev/null; then
  echo "Potential malformed </p tag found"
  exit 1
fi

echo "Quality checks passed"
