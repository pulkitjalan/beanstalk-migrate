Beanstalk Migrate
=================

Migrate beanstalk jobs from one server to another.

## Installation

Clone this repo and run `composer install`

## Basic Usage

To migrate all jobs from one beanstalk server to another:

```
php migrate all <source:port> <destination:port>
```
`Source` and `Destination` are required, `Port` is optional. `Port` defaults to `11300` (beanstalks default port).

## Credits

The structure of this package is inspired by the awesome [laravel](https://github.com/laravel/laravel) framework.
