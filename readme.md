# Redis Cache Provider

This Cache Provider uses Redis as backend to store the cached items.

<blockquote>Phapi has one important rule regarding cache: A working cache should **not** be a requirement for the application to work. So if Phapi is unable to connect to the cache backend it wont stop the execution. Instead the configured cache will be replaced with a dummy cache, <code>new NullCache()</code>.</blockquote>

## Installation
The package is **not** installed by default by the Phapi framework. Add the package as a dependency in composer to install the package.

```shell
$ composer require phapi/cache-redis:1.*
```

## Configuration
Configure the package and add it to the container to enable it.

```php
<?php
$container['cache'] = function ($container) {
    return new \Phapi\Cache\Redis\Redis($servers = [
        [
            'host' => 'localhost',
            'port' => 6379,
        ]
    ]);
};
```
The Redis cache provider does currently **not** support clusters.

See the [configuration documentation](http://phapi.github.io/docs/started/configuration/) for more information about how to configure the integration with the Phapi Framework.

## General cache usage
```php
<?php
// Add something to the cache
$cache->set('test', 'value');

// Read something from the cache
echo $cache->get('test'); // Will echo "value"

// Check if something exists in the cache
$bool = $cache->has('test');

// Remove from cache
$cache->clear('test');

// Flush the cache
$cache->flush();
```

## License
Redis Cache Provider is licensed under the MIT License - see the [license.md](https://github.com/phapi/cache-redis/blob/master/license.md) file for details

## Contribute
Contribution, bug fixes etc are [always welcome](https://github.com/phapi/cache-memcache/issues/new).
