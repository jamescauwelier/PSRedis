An example of how I think this will work

```php
$redisLibraryAdapter = new PredisClientAdaptor();

$sentinel1 = new SentinelNode('127.0.0.1', 1212, $redisLibraryAdapter);
$sentinel2 = new SentinelNode('127.0.0.1', 1213, $redisLibraryAdapter);
$sentinel3 = new SentinelNode('127.0.0.1', 1214, $redisLibraryAdapter);

$monitor = new MonitorSet('test');
$monitor->addNode($sentinel1);
$monitor->addNode($sentinel2);
$monitor->addNode($sentinel3);

try {
    $redisConnection = $monitor->getMaster()->connect();
    $redisConnection->set('kyename', 'value');
    $redisConenction->get('keyname');
} catch (\Exception $e ) {
    die('something wrong');
}
```

# Recover connections #

1. Do master discovery again (maybe with incremental backoff)
2. Retry failed command

# Master discovery #

1. connect to first/next sentinel
2. if successfull, ask with SENTINEL command who the master is
3. if not, connect to next sentinel (back to 1)
4. connect to found master