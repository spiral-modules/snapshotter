# Snapshotter
Snapshots management module. Can store snapshot in database or in files, allows to view and easily manipulate them via vault panel. 

[![Latest Stable Version](https://poser.pugx.org/spiral/snapshotter/v/stable)](https://packagist.org/packages/spiral/snapshotter) 
[![Total Downloads](https://poser.pugx.org/spiral/snapshotter/downloads)](https://packagist.org/packages/spiral/snapshotter) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spiral-modules/snapshotter/badges/quality-score.png)](https://scrutinizer-ci.com/g/spiral-modules/snapshotter/) 
[![Coverage Status](https://coveralls.io/repos/github/spiral-modules/snapshotter/badge.svg)](https://coveralls.io/github/spiral-modules/snapshotter)
[![Build Status](https://travis-ci.org/spiral-modules/snapshotter.svg?branch=master)](https://travis-ci.org/spiral-modules/snapshotter)

## Installation
```
composer require spiral/snapshotter
spiral register spiral/snapshotter
```
### Include snapshots controller link into navigation menu like below (optional):

```php
'snapshots' => [
    'title' => 'Snapshots',
    'requires' => 'vault.snapshots'
],
```

### Include `SnapshotterBootloader`

```php
$this->getBootloader()->bootload([
    \Spiral\Snapshotter\Bootloaders\SnapshotterBootloader::class
]);
```

### Select one of provided handlers

Currently there are two supported handlers: `FileHandler` and `AggregationHandler`, choose onf of them and bind it:
```php
$this->getBootloader()->bootload([
    \Spiral\Snapshotter\Bootloaders\FileHandlerBootloader::class
]);
//OR:
$this->getBootloader()->bootload([
    \Spiral\Snapshotter\Bootloaders\AggregationHandlerBootloader::class
]);
```

Then you can remove standard `SnapshotInterface` binding (if included):
```php
//$this->container->bind(SnapshotInterface::class, Snapshotter\Debug\AggregatedSnapshot::class);
```

## File Handler
File handler stores snapshot files in runtime directory. 

## Aggregation Handler
Aggregation handler stores snapshots in database. Exception body is gzencoded

### Suppression

Aggregation handler aggregates similar snapshot incidents groping them by snapshot teaser message,
it allows you to easily manage snapshots if some of them occurred more than once.
Aggregation handler supports suppression feature:
it allows you to save space because new snapshot incidents will be stored with empty exception source.
You will see all incidents, no reason to store all sources if you can find it in the last incident.
If you want to store sources - just disable suppression.
> After suppression is enabled, only new incidents will be involved, old ones will be kept untouched. Same for disabled suppression.

### Define database connection.

Aggregation handler uses database, by default it is set as an alias to the `default` database

---

#TODO-list
1. Add charts/widgets
2. Add listing dependency