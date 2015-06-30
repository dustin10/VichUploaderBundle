#!/bin/bash
#

#export SYMFONY_VERSION="~2.7.0"
SUPPORTED_SYMFONY_VERSIONS=('~2.3.0' '~2.5.0' '~2.6.0' '~2.7.0' '2.8.x-dev')
export DOCTRINE_VERSION=">=2.2.3,<2.5-dev"

sudo apt-get -qq update
sudo apt-get install -y php5 php5-mongo php5-sqlite
php --version

# Install/upgrade composer.phar
curl -sS https://getcomposer.org/installer | php
php composer.phar --version

# Save a copy of the current composer.json file so we can restore it later
cp composer.json composer.json.bck

# Clear temp folder before the tests run
rm -rf /tmp/VichUploaderBundle/

# If SYMFONY_VERSION is set run the tests only for that version
if [ "${SYMFONY_VERSION}" != "" ]; then
  SUPPORTED_SYMFONY_VERSIONS=(${SYMFONY_VERSION})
fi

# Run the tests for each supported symfony version
for sf_version in ${SUPPORTED_SYMFONY_VERSIONS[@]}; do
  php composer.phar require --no-update "symfony/symfony":"${sf_version}"
  php composer.phar require --no-update "doctrine/orm":"${DOCTRINE_VERSION}"

  php composer.phar update symfony/symfony
  php composer.phar install --prefer-source

  vendor/bin/phpunit $@
done

# Restore the composer.json file
rm composer.json && mv composer.json.bck composer.json
