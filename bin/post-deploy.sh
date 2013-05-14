#!/bin/bash

echo "Startin post-deployment of Feikki.fi";
projectRoot=$(dirname $(dirname $(readlink -f $0)))

cd $projectRoot;
echo "Resetting access rights"
for needed in app/cache app/logs
do
    chmod -R 777 "$needed"
done

echo "Cleaning dev & test cache, warming up production";
rm -rf app/cache/*
php composer.phar install --optimize-autoloader;
php app/console cache:clear --env=prod --no-debug;

