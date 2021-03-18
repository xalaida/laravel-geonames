# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests.


## Pull Requests

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

## Setup

The project setup is based upon [docker](https://docs.docker.com/engine/install).
For convenience, common tasks are wrapped up in the [Makefile](Makefile) for usage with [GNU make](https://www.gnu.org/software/make/).

1. Fork and clone the project

2. Run the installation command

```bash
# Using global composer
composer install

# Using docker & makefile
make install
```

## Running Tests

```bash
# Using global composer
composer test

# Using docker & makefile
make test
```

## Code Style

Formatting is automated through [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

```bash
# Using global composer
composer fix

# Using docker & makefile
make fix
```

**Happy coding**!
