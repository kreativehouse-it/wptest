#!/usr/bin/env bash
# Builds medgemmaParser.tar.gz, ready for "Upload a new plugin" in OJS.
set -euo pipefail
cd "$(dirname "$0")"
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
# Never ship VCS metadata or test fixtures from dependencies.
find vendor -name .git -type d -prune -exec rm -rf {} +
rm -rf vendor/smalot/pdfparser/samples vendor/smalot/pdfparser/tests
tmp=$(mktemp -d)
trap 'rm -rf "$tmp"' EXIT
mkdir "$tmp/medgemmaParser"
cp -r MedgemmaParserPlugin.php MedgemmaParserHandler.php MedgemmaParserSettingsForm.php \
    version.xml README.md classes jobs templates css locale vendor "$tmp/medgemmaParser/"
tar -czf medgemmaParser.tar.gz -C "$tmp" medgemmaParser
echo "Created $(pwd)/medgemmaParser.tar.gz"
