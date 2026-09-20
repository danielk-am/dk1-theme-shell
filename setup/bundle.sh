#!/bin/sh
set -eu
cd "$(dirname "$0")/.."
version=$(sed -n 's/^Version: //p' style.css)
mkdir -p dist
stage=$(mktemp -d)
trap 'rm -rf "$stage"' EXIT
mkdir "$stage/dk1-theme-shell"
rsync -a --exclude-from=.distignore ./ "$stage/dk1-theme-shell/"
out="$PWD/dist/dk1-theme-shell-$version.zip"
rm -f "$out"
(cd "$stage" && zip -qr "$out" dk1-theme-shell)
printf '%s\n' "$out"
