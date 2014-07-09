# php-redis-sentinel

A PHP client for redis sentinel connections as a wrapper on other redis clients

## Installation

The easiest way to install is by using composer.  The package is available on
[packagist](https://packagist.org/packages/sparkcentral/predis-sentinel) so installing should be as easy as putting
the following in your composer file:

```
"require": {
    "sparkcentral/predis-sentinel": "dev-master"
},
```

## Testing

For testing, we still use PHPUnit 3.7 due to a bug in PhpStorm not allowing us to run unit tests from our IDE.  See
http://youtrack.jetbrains.com/issue/WI-21666


