Contributing
============

First of all, **thank you** for contributing, **you are awesome**!

Here are a few rules to follow in order to ease code reviews, and discussions before
maintainers accept and merge your work.

You MUST follow the [PSR-1](//www.php-fig.org/psr/psr-1/) and
[PSR-2](//www.php-fig.org/psr/psr-2/). If you don't know about any of them, you
should really read the recommendations. Can't wait? Use the [PHP-CS-Fixer
tool](//cs.sensiolabs.org/).

You MUST run the test suite.

You MUST write (or update) unit tests.

You SHOULD write documentation.

Please, write [commit messages that make
sense](//tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html),
and [rebase your branch](//git-scm.com/book/en/v2/Git-Branching-Rebasing)
before submitting your Pull Request.

One may ask you to [squash your
commits](http://gitready.com/advanced/2009/02/10/squashing-commits-with-rebase.html)
too. This is used to "clean" your Pull Request before merging it (we don't want
commits such as `fix tests`, `fix 2`, `fix 3`, etc.).

Thank you!

## Running the test suite

Ensure that the required vendors are installed by running `composer install`.
The test suite requires the `php5-mongo` and `php5-sqlite` extensions to be installed.

PHPUnit should be installed by composer. Run the tests with the
`./vendor/bin/phpunit` command.

Alternatively you can use the `runTests.sh` bash script present at the project root.
Default usage example (This runs all tests with your current PHP version against
all supported Symfony versions.):

```bash
./runTest.sh
```

You can also set a specific Symfony version to test against and/or pass the arguments for PHPUnit
 as arguments to the script. Usage example:

```bash
SYMFONY_VERSION=3.4.1 ./runTests.sh --filter testCustomFileNameProperty
```

**Note:** The script was prepared to run under Ubuntu and using Bash so it might need further validation for other OS.
