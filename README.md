# This will send to textract for scanning image.

This package allows you to scan document with laravel.[Amazon Simple Notification Service (Amazon SNS)](https://aws.amazon.com/sns/) is used for scan document. It is using offical [AWS SDK for PHP](https://github.com/aws/aws-sdk-php) and [Amazon SNS Message Validator for PHP](https://github.com/aws/aws-php-sns-message-validator).

## Installation

You can install the package via composer:

```bash
composer require webmavens/laravelscandocument
```

## Usage

- Please create SNS topic in your amazon account.

- How to create one ? 

* Please create IAM Role for textract. Follow this [link](https://docs.aws.amazon.com/textract/latest/dg/api-async-roles.html#api-async-roles-all-topics).

* Please create SNS topic by searching SNS in your aws account.

* After creating topic, please add subscribe url to SNS topic below.

**Note :- Please do not set up raw message delivery for callback url.**

```php
https://{YOUR_DOMAIN_NAME}/textractCallback
```

- Please add below parameters to your .env file.

```php
AWS_DEFAULT_REGION = 'YOUR_AWS_DEFAULT_REGION',
AWS_ACCESS_KEY_ID = 'YOUR_AWS_ACCESS_KEY_ID',
AWS_SECRET_ACCESS_KEY = 'YOUR_AWS_SECRET_ACCESS_KEY',
AWS_BUCKET = 'YOUR_AWS_BUCKET',
AWS_ARN_TOPIC_ID = 'YOUR_AWS_ARN_TOPIC_ID',
AWS_SNS_TOPIC_ID = 'YOUR_AWS_SNS_TOPIC_ID',
```

- Please publish migrate file.

```php
php artisan vendor:publish --tag="laravelscandocument-migrations"
```

- Send document to scan

```php
$laravelScandocument = new Webmavens\LaravelScandocument();
// $path = File path
// $jobtag = Type of document
$response = $laravelScandocument->sendDocToScan($path,$jobtag); //$jobtag is optional.It should be string.
```

- You will find scan document text in **laravel_scandocument_data** table.

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
