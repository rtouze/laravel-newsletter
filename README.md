# Manage newsletters in Laravel

[![Latest Version](https://img.shields.io/packagist/v/dansmaculotte/laravel-newsletter.svg?style=flat-square)](https://packagist.org/packages/dansmaculotte/laravel-newsletter)
[![Total Downloads](https://img.shields.io/packagist/dt/dansmaculotte/laravel-newsletter.svg?style=flat-square)](https://packagist.org/packages/dansmaculotte/laravel-newsletter)
[![Build Status](https://img.shields.io/travis/dansmaculotte/laravel-newsletter/master.svg?style=flat-square)](https://travis-ci.org/dansmaculotte/laravel-newsletter)
[![Quality Score](https://img.shields.io/scrutinizer/g/dansmaculotte/laravel-newsletter.svg?style=flat-square)](https://scrutinizer-ci.com/g/dansmaculotte/laravel-newsletter)
[![Code Coverage](https://img.shields.io/coveralls/github/dansmaculotte/laravel-newslteter.svg?style=flat-square)](https://coveralls.io/github/dansmaculotte/laravel-newsletter)

This package is a fork from spatie/laravel-newsletter. It provides an easy way to integrate different email services with Laravel.

There are 2 drivers available:

  - [Mailchimp](https://developer.mailchimp.com/documentation/mailchimp/)
  - [Mailjet](https://dev.mailjet.com/guides/#about-the-mailjet-api)
  
  
There is also and `log` and `null` driver for testing and debug purpose.

## Installation

You can install this package via composer using:

```bash
composer require dansmaculotte/laravel-newsletter
```

The package will automatically register itself.

To publish the config file to `config/newsletter.php` run:

```bash
php artisan vendor:publish --provider="DansMaCulotte\Newsletter\NewsletterServiceProvider"
```

This will publish a file `newsletter.php` in your config directory.

Finally, install the email service package needed:

- Mailchimp

```bash
composer require drewm/mailchimp-api
```

- Mailjet

```bash
composer require mailjet/mailjet-apiv3-php
```

## Usage

Configure your mail template driver and credentials in `config/newsletter.php`.

After you've installed the package and filled in the values in the config-file working with this package will be a breeze. All the following examples use the facade. Don't forget to import it at the top of your file.

```php
use Newsletter;
```

### Subscribing, updating and unsubscribing

Subscribing an email address can be done like this:

```php
use Newsletter;

Newsletter::subscribe('rincewind@discworld.com');
```

Add add email address to list but excluded from marketing emails

```php
use Newsletter;

Newsletter::addMember('rincewind@discworld.com');
```

Let's unsubscribe someone:

```php
Newsletter::unsubscribe('the.luggage@discworld.com');
```

You can pass options as the second argument:
```php
Newsletter::subscribe('rincewind@discworld.com', ['FNAME' => 'Rince', 'LNAME' => 'Wind']);
```

You can subscribe someone to a specific list by using the third argument:
```php
Newsletter::subscribe('rincewind@discworld.com', ['FNAME' => 'Rince', 'LNAME' => 'Wind'], 'subscribers');
```
That third argument is the name of a list you configured in the config file.

You can also subscribe and/or update someone. The person will be subscribed or updated if he/she is already subscribed:

 ```php
 Newsletter::subscribeOrUpdate('rincewind@discworld.com', ['FNAME' => 'Foo', 'lastname' => 'Bar']);
 ```
 
You can also unsubscribe someone from a specific list:
```php
Newsletter::unsubscribe('rincewind@discworld.com', 'subscribers');
```

### Deleting subscribers

Deleting is not the same as unsubscribing. Unlike unsubscribing, deleting a member will result in the loss of all history (add/opt-in/edits) as well as removing them from the list. In most cases you want to use `unsubscribe` instead of `delete`.

Here's how to perform a delete:

```php
Newsletter::delete('rincewind@discworld.com');
```

### Getting subscriber info

You can get information on a subscriber by using the `getMember` function:
```php
Newsletter::getMember('lord.vetinari@discworld.com');
```

This will return an array with information on the subscriber. If there's no one subscribed with that
e-mail address the function will return `false`

There's also a convenience method to check if someone is already subscribed:

```php
Newsletter::hasMember('nanny.ogg@discworld.com'); //returns a boolean
```

In addition to this you can also check if a user is subscribed to your list:

```php
Newsletter::isSubscribed('lord.vetinari@discworld.com'); //returns a boolean
```

### Need something else?

If you need more functionality you get an instance of the underlying Api with:

```php
$api = Newsletter::getApi();
```

### Mailjet notes

Connection timeouts may occur when using mailjet, as described
[here](https://github.com/mailjet/mailjet-apiv3-php/issues/157). You can
configure the value you want with configuration key
`mailjet.connection_timeout`, itself using environment variable
`MJ_CONNECTION_TIMEOUT`.


## Testing

Run the tests with:
```bash
vendor/bin/phpunit
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
