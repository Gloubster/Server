#!/bin/bash

command -v npm >/dev/null 2>&1  || { echo "I require npm but it's not installed. Aborting." >&2; exit 1; }
command -v git >/dev/null 2>&1  || { echo "I require git but it's not installed. Aborting." >&2; exit 1; }
command -v curl >/dev/null 2>&1 || { echo "I require curl but it's not installed. Aborting." >&2; exit 1; }
command -v php >/dev/null 2>&1  || { echo "I require php but it's not installed. Aborting." >&2; exit 1; }
command -v bower >/dev/null 2>&1 || { echo "Installing Bower http://twitter.github.com/bower/"; npm install bower -g; }
command -v bower >/dev/null 2>&1 || { echo "Unable to install bower. Aborting." >&2; exit 1; }

git submodule update --init

if [ !  -f composer.phar ]
then
    curl -s http://getcomposer.org/installer | /usr/bin/env php
fi

/usr/bin/env php composer.phar install
/usr/bin/env bower install
