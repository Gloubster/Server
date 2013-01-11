#!/bin/bash

command -v npm >/dev/null 2>&1  || { echo "I require npm but it's not installed. Aborting." >&2; exit 1; }
command -v git >/dev/null 2>&1  || { echo "I require git but it's not installed. Aborting." >&2; exit 1; }
command -v curl >/dev/null 2>&1 || { echo "I require curl but it's not installed. Aborting." >&2; exit 1; }
command -v php >/dev/null 2>&1  || { echo "I require php but it's not installed. Aborting." >&2; exit 1; }
command -v bower >/dev/null 2>&1 || { echo "Installing Bower http://twitter.github.com/bower/"; npm install bower -g; }
command -v bower >/dev/null 2>&1 || { echo "Unable to install bower. Aborting." >&2; exit 1; }

chmod +x bin/glouster
chmod +x vendor/phpexiftool/exiftool/exiftool

git submodule update --init
/usr/bin/env bower install
