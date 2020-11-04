# Easily debug Laravel apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-timber.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-timber)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-timber/run-tests?label=tests)](https://github.com/spatie/laravel-timber/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-timber.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-timber)

This package can send messages to the Timber app. 

```php
timber('hi there!')
timber('I am big and green')->green()->large()
```

Here's how that looks like in the app.

TODO: add screenshot

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/package-laravel-timber-laravel.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/package-laravel-timber-laravel)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-timber
```

You can publish and run the migrations with:


You can publish the config file with:
```bash
php artisan vendor:publish --provider="Spatie\Timber\TimberServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    /*
     * When enabled, all things logged to the application log
     * will be sent to Timber as well.
     */
    'send_log_calls_to_timber' => true,

    /*
     * The port number to communicate with Timber.
     */
    'port' => 23517,
];
```

## Usage

You can pass any variable you want to `timber`:

```php
timber('a string', ['an array'], new MyClass)
```

All arguments will be converted to strings and will be sent to Timber.

## Enabling and disabling logging

You can disable sending to Timber by calling `disable`.

```php
timber('foo'); // will be sent to Timber

timber()->disable();

timber('bar') // will not be sent to Timber

timber()->enable();

timber('baz'); // will be sent to Timber
```

You can pass a boolean to `enable`. This can be handy when you want to log only one iteration of a loop.

```php
foreach (range(1, 3) as $i) {
   // only things in the third iteration will be sent to Timber
   timber()->enable($i === 3);
    
   timber('we are in the third iteration');
}
```

## Logging queries

You can send all queries to Timber using `logQueries`.

````php
timber()->logQueries(); // all queries after this call will be sent to Timber
````

If you wish to stop logging queries, call `stopLoggingQueries`.

````php
timber()->stopLoggingQueries(); // all queries after this call will not be sent to Timber anymore
````

Alternatively to manually starting and stopping listening for queries, you can also pass a closure to `logQueries`. Only the queries executed inside the closure will be sent to Timber.

````php
timber()->logQueries(function() {
    $this->mailAllUsers() // all queries executed in this closure will be sent to Timber
}); 

User::get(); // this query will not be sent to Timber
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
