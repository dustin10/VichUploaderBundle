#!/bin/bash

SUPPORTED_SYMFONY_VERSIONS=('~2.3.0' '~2.7.0' '~2.8.0' '~3.0.0')
GREEN='\033[0;32m'
NC='\033[0m'

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
  echo -e "\n${GREEN}Removing previous composer.lock file${NC}"
  rm composer.lock

  echo -e "\n${GREEN}Setting requirements version constraints: Symfony (${sf_version})${NC}"
  php composer.phar require --dev --no-update symfony/symfony:${sf_version}

  echo -e "\n${GREEN}Installing dependencies${NC}"
  php composer.phar update --prefer-dist --ignore-platform-reqs || { restore_composer && exit 1; }

  echo -e "\n${GREEN}Launching tests${NC}"
  vendor/bin/phpunit $@
done

restore_composer
