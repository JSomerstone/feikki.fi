#!/bin/bash

# Absolute path this script is in, thus /home/user/bin
projectRoot=$(dirname $(dirname $(readlink -f $0)))
dateStamp=$(date +"%Y%m%d");

echo "Startin pre-deployment of Feikki.fi";

echo -n "Enter release tag [$dateStamp] > ";
read givenTag
if ["$givenTag" == ""]; then
    tag=$dateStamp;
else
    tag=$givenTag;
fi

echo "* Installing assets";
php app/console assets:install;

tempFolder="/tmp/feikki.fi.$tag";
echo "* Creting temporary folder $tempFolder";
mkdir $tempFolder

for needed in app src vendor web README.md bin composer.*
do
    echo " - copying $needed"
    cp -rf $projectRoot/$needed $tempFolder/
done

echo "* Cleaning up cache"
rm -rf $tempFolder/app/cache/*

echo "Version $givenTag on $(date +"%Y-%m-%d")" >> $tempFolder/web/version.txt
echo "* Creating $tempFolder.tar.gz packet"
cd /tmp;
tar -czf "feikki.fi.$tag.tar.gz" "feikki.fi.$tag"

echo "* Cleaning up";
rm -rf $tempFolder;

echo "You can now deploy feikki.fi from file /tmp/feikki.fi.$tag.tar.gz";