VichUploaderBundle
==================

[![Build Status](https://secure.travis-ci.org/dustin10/VichUploaderBundle.png?branch=master)](http://travis-ci.org/dustin10/VichUploaderBundle)

The VichUploaderBundle is a simple Symfony2 bundle that attempts to ease file 
uploads that are attached to an entity. The bundle will automatically name and 
save the uploaded file according to the configuration specified on a per property
basis. The bundle also provides templating helpers for generating URLs to the 
file as well. The file can also be configured to be removed from the file system 
upon removal of the entity.

Current limitations:

- Doctrine ORM and MongoDB
- Saving/deleting files to the local filesystem only
- Generating a relative url for the file only

## Documentation

For usage documentation, see:

[Resources/doc/index.md](https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/index.md)
    

For license, see:

[Resources/meta/LICENSE](https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/meta/LICENSE)