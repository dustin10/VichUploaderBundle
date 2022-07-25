#/bin/bash

TARGET?=81

.PHONY: tests
tests:
	make vichuploader-image
	docker run dustin10/vichuploader_php${TARGET} -d date.timezone='UTC' vendor/bin/phpunit

# Makes it easy to run a single test file. Example to run IndexTest.php: make test TEST="IndexTest.php"
.PHONY: test
test:
	make vichuploader-image
	docker run dustin10/vichuploader_php${TARGET} -d date.timezone='UTC' vendor/bin/phpunit -c ./ ${TEST}

# Stops and removes all containers and removes all images
.PHONY: destroy-environment
destroy-environment:
	make remove-containers
	-docker rmi $(shell docker images -q)

.PHONY: remove-containers
remove-containers:
	-docker stop $(shell docker ps -a -q)
	-docker rm -v $(shell docker ps -a -q)

## DOCKER IMAGES
.PHONY: vichuploader-image
vichuploader-image:
	docker build -t dustin10/vichuploader_php${TARGET} -f ./docker/Dockerfile${TARGET} .

# Builds all image locally. This can be used to use local images if changes are made locally to the Dockerfiles
.PHONY: build-images
build-images:
	make vichuploader-image

# Removes all local images
.PHONY: clean-images
clean-images:
	docker rmi dustin10/vichuploader_php${TARGET}
