# Easily debug Laravel apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-ray.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-ray)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-ray/run-tests?label=tests)](https://github.com/spatie/laravel-ray/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-ray.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-ray)

This package can send messages to the Ray app. 

```php
ray('hi there!')
ray('I am big and green')->green()->large()
```

Here's how that looks like in the app.

TODO: add screenshot

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/package-laravel-ray-laravel.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/package-laravel-ray-laravel)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-ray
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Spatie\Ray\RayServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    /*
     * When enabled, all things logged to the application log
     * will be sent to Ray as well.
     */
    'send_log_calls_to_ray' => true,

    /*
     * The port number to communicate with Ray.
     */
    'port' => 23517,
];
```

## Usage

You can pass any variable you want to `ray`:

```php
ray('a string', ['an array'], new MyClass)
```

All arguments will be converted to strings and will be sent to Ray.

## Enabling and disabling logging

You can disable sending to Ray by calling `disable`.

```php
ray('foo'); // will be sent to Ray

ray()->disable();

ray('bar') // will not be sent to Ray

ray()->enable();

ray('baz'); // will be sent to Ray
```

You can pass a boolean to `enable`. This can be handy when you want to log only one iteration of a loop.

```php
foreach (range(1, 3) as $i) {
   // only things in the third iteration will be sent to Ray
   ray()->enable($i === 3);
    
   ray('we are in the third iteration');
}
```

## Logging queries

You can send all queries to Ray using `logQueries`.

````php
ray()->logQueries(); // all queries after this call will be sent to Ray
````

If you wish to stop logging queries, call `stopLoggingQueries`.

````php
ray()->stopLoggingQueries(); // all queries after this call will not be sent to Ray anymore
````

Alternatively to manually starting and stopping listening for queries, you can also pass a closure to `logQueries`. Only the queries executed inside the closure will be sent to Ray.

````php
ray()->logQueries(function() {
    $this->mailAllUsers(); // all queries executed in this closure will be sent to Ray
}); 

User::get(); // this query will not be sent to Ray
````

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
