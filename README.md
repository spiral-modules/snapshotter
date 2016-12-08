# snapshotter
Snapshots management module. Snapshot files are now stored in database, this allows to view and easily manipulate them. 

[![Latest Stable Version](https://poser.pugx.org/spiral/snapshotter/v/stable)](https://packagist.org/packages/spiral/snapshotter) 
[![Total Downloads](https://poser.pugx.org/spiral/snapshotter/downloads)](https://packagist.org/packages/spiral/snapshotter) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spiral-modules/snapshotter/badges/quality-score.png)](https://scrutinizer-ci.com/g/spiral-modules/snapshotter/) 

## Installation
```
composer require spiral/snapshotter
spiral register spiral/snapshotter
```

After installation you only need to do next steps:

### Add binding to aggregator class

```php
$this->container->bind(
    Debug\SnapshotInterface::class,
    \Spiral\Snapshotter\Debug\AggregatedSnapshot::class
);
```

### Include snapshots link into navigation menu (optional)

Be sure that you have `navigation.vault` placeholder in `modules/vault` config like this
```php
'vault'    => [
    'title' => 'Vault',
    'icon'  => 'power_settings_new',
    'items' => [
        /*{{navigation.vault}}*/
    ]
]
```

### Define database connection.
Snapshotter is an addition to the vault module, so it uses database `vault`

#todo-list
1. Add charts/widgets
2. Add listing dependency
3. Tests
