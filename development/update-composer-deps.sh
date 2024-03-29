#!/usr/bin/env bash

cd /data
composer self-update

# Update the composer dependencies.
composer update --no-scripts

# Make sure all our simplesamlphp modules are still there. (They can be removed
# from the vendor folder if simpelsamlphp was updated and the modules weren't).
composer install --no-scripts

# Update our list of what packages are currently installed.
composer show --direct --format=json > installed-packages.json