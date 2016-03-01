# snapshotter
Snapshots management module. Snapshot files are now stored in database, this allows to view and easily manipulate them. 

After installation you only need to do next steps:

1. add binding to `\Spiral\Snapshotter\Debug\AggregatedSnapshot::class`
```php
$this->container->bind(
    Debug\SnapshotInterface::class,
    \Spiral\Snapshotter\Debug\AggregatedSnapshot::class
);
```

2. To include snapshots link into navigation menu be sure that you have `navigation.vault` placeholder in `modules/vault` config like this
```php
'vault'    => [
    'title' => 'Vault',
    'icon'  => 'power_settings_new',
    'items' => [
        /*{{navigation.vault}}*/
    ]
]
```

3. Do not forget to define `vault` database connection.
Snapshotter is an addition to the vault module, so it uses database `vault`

#todo-list
1. Add charts
2. Add listing dependency