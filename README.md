# redisphp-native

Get value from key:
```php
Redis\connect('127.0.0.1', 6379);
Redis\get('mykey');
```

After connection to redis this connection will be used for all commands.

Delete key:
```php
Redis\del('mykey');
```

Set key:
```php
  Redis\set('mykey', 'HELLO', 360);
```

Check exists key:
```php
  Redis\exists('mykey);
```
