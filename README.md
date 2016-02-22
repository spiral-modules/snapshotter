# snapshotter
Snapshots management

After installation, need to bing `\Spiral\Snapshotter\Debug\AggregatedSnapshot`
```php
if (env('AGGREGATE_SNAPSHOTS')) {
    $this->container->bind(
        Debug\SnapshotInterface::class,
        \Spiral\Snapshotter\Debug\AggregatedSnapshot::class
    );
}
```

Do not forget to define `vault` database connection