# This will send to textract for scanning image.

This package allows you to scan document with laravel. It is using offical [AWS SDK for PHP](https://github.com/aws/aws-sdk-php) and [Amazon SNS Message Validator for PHP](https://github.com/aws/aws-php-sns-message-validator).

## Installation

You can install the package via composer:

```bash
composer require webmavens/laravelscandocument
```

## Usage

- Please add below parameters to your .env file.

```php
AWS_DEFAULT_REGION = 'YOUR_AWS_DEFAULT_REGION',
AWS_ACCESS_KEY_ID = 'YOUR_AWS_ACCESS_KEY_ID',
AWS_SECRET_ACCESS_KEY = 'YOUR_AWS_SECRET_ACCESS_KEY'),
AWS_BUCKET = 'YOUR_AWS_BUCKET',
AWS_ARN_TOPIC_ID = 'YOUR_AWS_ARN_TOPIC_ID',
AWS_SNS_TOPIC_ID = 'YOUR_AWS_SNS_TOPIC_ID',
```

- Send document to textract

```php
$laravelScandocument = new Webmavens\LaravelScandocument();
$response = $laravelScandocument->sendDocToScan($content,$jobtag); //$jobtag is optional.It should be string.
```

- Texract callback handle.

1. Create post route for callback to textract and add below method to controller.

```php
public function textractNotification()
{
    $postadata = file_get_contents("php://input"); // post content from textract
    $content = LaravelScandocument::scanDocument($postadata); // package method that scan document.
}
```

- Above method is also handling subscribe and unsubscribe callback url.

** Note :- Please do not set up raw message delivery for callback url. **

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [webmavens](https://github.com/webmavens)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
