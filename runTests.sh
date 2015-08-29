#!/bin/bash
#

SUPPORTED_SYMFONY_VERSIONS=('~2.3.0' '~2.5.0' '~2.6.0' '~2.7.0' '2.8.x-dev')

restore_composer () {
  # Restore the composer.json file
  rm composer.json && mv composer.json.bck composer.json
}

# Install/upgrade composer.phar
[ ! -e composer.phar ] && curl -sS https://getcomposer.org/installer | php
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
  echo -e "\n\nSetting requirements version constraints: Symfony (${sf_version})"
  php composer.phar require --dev --no-update symfony/symfony:${sf_version}

  echo -e "\nInstalling dependencies"
  php composer.phar update --prefer-source || { restore_composer && exit 1; }

  echo -e "\nLaunching tests"
  vendor/bin/phpunit $@
done

restore_composer
