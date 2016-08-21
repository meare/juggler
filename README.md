# Juggler

[![Latest Version on Packagist][ico-packagist]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-build]][link-build]
[![Code Coverage][ico-coverage]][link-coverage]
[![Scrutinizer Code Quality][ico-code-quality]][link-code-quality]

Juggler is a PHP client for [mountebank](http://www.mbtest.org/) - open source tool that provides test doubles over the wire. Juggler allows to:

* interact with mountebank API easily;
* verify mocks;
* alter and build imposters;

Only HTTP imposters are supported are supported at the moment.

## Install

Via Composer

``` bash
$ composer require meare/juggler:v1.0-beta@dev
```

## Usage
### API interactions
Juggler makes interactions with [mountebank API](http://www.mbtest.org/docs/api/overview) easy:
``` php
use Meare\Juggler\Juggler;

$juggler = new Juggler('mountebank');

// Delete active imposters before posting to avoid resource conflicts
$juggler->deleteImposters();
$port = $juggler->postImposterFromFile(__DIR__ . '/contract.json');

// Retrieve imposter contract and save it to file
$juggler->saveContract($port, __DIR__ . '/retrieved_contract.json');

$juggler->deleteImposter($port);
```
### Mock verification
[mountebank](http://www.mbtest.org/) remembers every request imposters get if `--mock` command line flag is set.

Here is how to verify mock with Juggler:
```php
use Meare\Juggler\Juggler;

$juggler = new Juggler('mountebank');

// Post imposter
$port = $juggler->postImposterFromFile(__DIR__ . '/contract.json');

// Do some requests
file_get_contents('http://mountebank:4545/foo?bar=1');
file_get_contents('http://mountebank:4545/foo?bar=2&zar=3');

// Retrieve imposter and verify it received requests
$imposter = $juggler->getHttpImposter($port);
$imposter->hasRequestsByCriteria([
    'method' => 'GET',
    'path'   => '/foo',
    'query'  => ['bar' => 1],
]); // Will return true
```
[Read more on mock verification](http://www.mbtest.org/docs/api/mocks)
### Imposter altering
Imagine you have stub for `GET /account/3` which returns account balance:
```json
{
  "port": 4545,
  "protocol": "http",
  "stubs": [
    {
      "responses": [
        {
          "is": {
            "statusCode": 200,
            "body": {
              "clientId": 3,
              "name": "Dmitar Ekundayo",
              "balance": 24.5
            },
            "_mode": "text"
          }
        }
      ],
      "predicates": [
        {
          "equals": {
            "method": "GET",
            "path": "/client/3"
          }
        }
      ]
    }
  ]
}
```
At some point you might not want to create separate stub to imitate negative balance. Altering imposter will do the trick:
```php
use Meare\Juggler\Juggler;

$juggler = new Juggler('mountebank');
$port = $juggler->postImposterFromFile(__DIR__ . '/contract.json');

// Find stub by predicates and alter response
$imposter = $juggler->getHttpImposter($port);
$imposter->findStubByPredicates([['equals' => ['method' => 'GET', 'path' => '/account/3']]])
    ->getIsResponse()
    ->modifyBody(function (array $body) {
        $body['balance'] = -5.75;

        return $body;
    });

// Delete imposter and post again
$juggler->updateImposter($imposter);
```
## Building imposter
```php
use Meare\Juggler\Imposter\HttpImposter;
use Meare\Juggler\Imposter\Stub\Predicate\IPredicate;
use Meare\Juggler\Imposter\Stub\Predicate\Predicate;
use Meare\Juggler\Imposter\Stub\Response\IsResponse;
use Meare\Juggler\Juggler;

$juggler = new Juggler('mountebank');

// Create imposter with a stub for GET /test-endpoint
$imposter = new HttpImposter;
$imposter->createStub(
    [new IsResponse(200, ['Content-type' => 'application/json'], '{"status":200}')],
    [new Predicate(IPredicate::EQUALS, ['method' => 'GET', 'path' => '/test-endpoint'])]
);

// Post it!
$juggler->postImposter($imposter);

```
## Testing

``` bash
$ composer test
```

## Credits

- [Andrejs Mironovs][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-packagist]: https://img.shields.io/packagist/v/meare/juggler.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-build]: https://scrutinizer-ci.com/g/meare/juggler/badges/build.png?b=master
[ico-coverage]: https://scrutinizer-ci.com/g/meare/juggler/badges/coverage.png?b=master
[ico-code-quality]: https://scrutinizer-ci.com/g/meare/juggler/badges/quality-score.png?b=master

[link-packagist]: https://packagist.org/packages/meare/juggler
[link-build]: https://scrutinizer-ci.com/g/meare/juggler/build-status/master
[link-coverage]: https://scrutinizer-ci.com/g/meare/juggler/?branch=master
[link-code-quality]: https://scrutinizer-ci.com/g/meare/juggler/?branch=master

[link-author]: https://github.com/meare
[link-contributors]: ../../contributors