Beanstalk Migrate
=================

[![Total Downloads](https://img.shields.io/packagist/dt/pulkitjalan/beanstalk-migrate.svg?style=flat-square)](https://packagist.org/packages/pulkitjalan/beanstalk-migrate)

Migrate beanstalk jobs from one server to another.

## Installation

Clone this repo and run `composer install`

Or

Run `composer global require "pulkitjalan/beanstalk-migrate=dev-master"` and make sure to place the `~/.composer/vendor/bin` directory in your `PATH` with this approach

## Basic Usage

To migrate all jobs from one beanstalk server to another:

```
migrate all <source:port> <destination:port>
```
`Source` and `Destination` are required, `Port` is optional. `Port` defaults to `11300` (beanstalks default port).

## Credits

The structure of this package is inspired by the awesome [laravel](https://github.com/laravel/laravel) framework.
