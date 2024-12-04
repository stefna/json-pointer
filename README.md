# JSONPointer

[![Build Status](https://github.com/stefna/json-pointer/actions/workflows/continuous-integration.yml/badge.svg?branch=main)](https://github.com/stefna/json-pointer/actions/workflows/continuous-integration.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/stefna/json-pointer.svg)](https://packagist.org/packages/json-pointer)
[![Software License](https://img.shields.io/github/license/stefna/json-pointer.svg)](LICENSE.md)

JSON Pointer implementation

Inspired by https://github.com/gamringer/JSONPointer

## Requirements

PHP 8.2 or higher.

## Installation

```
composer require stefna/json-pointer
```

# Usage

## Test if document has pointer

```php

$document = [
	"foo" => ["bar", "baz"],
	"qux" => "quux"
];

$document = new \JsonPointer\BasicDocument('test', $document);

var_dump($document->has('/foo'));

var_dump($document->has('/foo/bar'));

/* Results:

bool(true)
bool(false)

*/
```

## Retrieving value form document

```php

$document = [
	"foo" => ["bar", "baz"],
	"qux" => "quux"
];

$document = new \JsonPointer\BasicDocument('test', $document);

var_dump($document->get('/foo'));

var_dump($document->get('/foo/bar'));

/* Result

array(2) {
  [0] =>
  string(3) "bar"
  [1] =>
  string(3) "baz"
}

Throws JSONPointer\Exceptions\Reference - Referenced element does not exist: bar 

*/
```


## Contribute

We are always happy to receive bug/security reports and bug/security fixes

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

