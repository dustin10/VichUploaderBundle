# Contributing

First of all, **thank you** for contributing, **you are awesome**!

Here are a few rules to follow to facilitate code reviews and discussions before
maintainers accept and merge your work.

You MUST follow the [PSR-1](//www.php-fig.org/psr/psr-1/) and
[PSR-2](//www.php-fig.org/psr/psr-2/). If you don't know about any of them, you
should really read the recommendations. Can't wait? Use the [PHP-CS-Fixer
tool](//cs.sensiolabs.org/).

You MUST run the test suite.

You MUST write (or update) unit tests.

You SHOULD write documentation.

Please, write [commit messages that make sense](//tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html),
and [rebase your branch](//git-scm.com/book/en/v2/Git-Branching-Rebasing)
before submitting your Pull Request.

One may ask you to [squash your commits](http://gitready.com/advanced/2009/02/10/squashing-commits-with-rebase.html)
too. This is used to "clean" your Pull Request before merging it (we don't want
commits such as `fix tests`, `fix 2`, `fix 3`, etc.).

Thank you!

## Running the test suite

The test suite uses Docker environments to be idempotent to OS's. We use multiple
PHP versions in Docker, ensuring that the bundle is tested with
the same resources. There is no need to have PHP or Mongo installed.

You only need Docker set up.

To allow testing environments more smooth we implemented **Makefile**.
You have two commands available:

```bash
make tests
```

which will execute all tests inside the docker.

```bash
make test TEST="Tests/Util/FilenameUtilsTest.php"
```

will allow testing single Test classes.

Four environments are available: PHP 8.1, PHP 8.2, PHP 8.3, and PHP 8.4.
The default environment is *PHP 8.1*. If you want to execute tests against
other PHP versions, please use the environment variables as this:

```bash
make tests #PHP 8.1 env
TARGET=82 make tests #PHP 8.2 env

make test TEST="tests/Util/FilenameUtilsTest.php" #PHP 8.1 env
TARGET=82 make test TEST="tests/Util/FilenameUtilsTest.php" #PHP 8.2 env
```
