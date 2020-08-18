# Map

An iterable key-value collection class for PHP.

## Installation

With [Composer](http://getcomposer.org) installed on your computer and initialized for your project, run this command in your project’s root directory:

```bash
composer require lamansky/map
```

Requires PHP 7.1 or above.

The library consists of a single class: `Lamansky\Map\Map`.

## Basic Usage

```php
<?php
use Lamansky\Map\Map;

$map = new Map();

// Set
$map->set('key', 'value');

// Has
var_dump($map->has('key')); // bool(true)

// Get
echo $map->get('key'); // 'value'

// Keys & Values
print_r($map->keys()); // Array ( [0] => key )
print_r($map->values()); // Array ( [0] => value )

// Iteration
foreach ($map as $key => $value) {
}

// Count
var_dump($map->count()); // int(1)

// Delete
var_dump($map->delete('key')); // bool(true) // found
var_dump($map->delete('key')); // bool(false) // not found
```

## Methods

For an understanding of what each of these methods does, refer to the unit tests in `tests/MapTest.php`.

* `get ($key)`
* `has ($key)`
* `set ($key, $value)`
* `mergeSet ($key, $value, callable $merge)`
* `edit ($key, callable $edit)`
* `increment ($key, $value)`
* `decrement ($key, $value)`
* `delete ($key)`
* `count ()`
* `entries ()`
* `keys ()`
* `values ()`
* `map (callable $callback)`
* `walk (callable $callback)`
* `sort (callable $sort)`
* `sortValuesAsc ()`
* `sortValuesDesc ()`
* `sortKeysAsc ()`
* `sortKeysDesc ()`
* `keepOnlyTheFirst (int $number)`
* `keepOnlyTheLast (int $number)`

### Static Methods

* `fromList (iterable $items, callable $get_key)`
* `merge (self ...$maps)`
* `deepMerge (callable $merge, self ...$maps)`

## Unit Tests

To run the development test suite, execute this command:

```bash
./vendor/phpunit/phpunit/phpunit tests
```
