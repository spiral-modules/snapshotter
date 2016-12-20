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

---

### Using PHP version < 7? 

Create your own inherited class for `SnapshotService` with `createFromException` method overriding and replace `$exception` type with `\Exception` instead of `\Throwable`

```php
class LegacySnapshotService extends SnapshotService
{
    /**
     * @param \Exception $exception
     * @param string     $filename
     * @param string     $teaser
     * @param string     $hash
     * @return Snapshot
     */
    public function createFromException(\Exception $exception, $filename, $teaser, $hash)
    {
        $fields = [
            'exception_hash'      => $hash,
            'filename'            => $filename,
            'exception_teaser'    => $teaser,
            'exception_classname' => get_class($exception),
            'exception_message'   => $exception->getMessage(),
            'exception_line'      => $exception->getLine(),
            'exception_file'      => $exception->getFile(),
            'exception_code'      => $exception->getCode(),
        ];

        return $this->getSource()->create($fields);
    }
}
```
 
After that bind your new class before binding `Debug\SnapshotInterface::class`

```php
if (version_compare(PHP_VERSION, '7.0') < 0) {
    $this->container->bind(
        \Spiral\Snapshotter\Models\SnapshotService::class,
        \LegacySnapshotService::class
    );
}

$this->container->bind(
    Debug\SnapshotInterface::class,
    \Spiral\Snapshotter\Debug\AggregatedSnapshot::class
);
```

---

#todo-list
1. Add charts/widgets
2. Add listing dependency
3. Tests