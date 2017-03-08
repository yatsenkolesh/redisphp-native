# redisphp-native

This library is analogous of Redis class without OOP.

Get value by key:
```php
Redis\connect('127.0.0.1', 6379);
Redis\get('mykey');
```

After connection to redis this connection will be used for all commands.

Delete:
```php
Redis\del('mykey');
```

Set key value:
```php
  Redis\set('mykey', 'HELLO', 360);
```

Check exists key:
```php
  Redis\exists('mykey);
```
