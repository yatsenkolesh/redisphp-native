<?php
/**
  * Redis php native library without oop
  * @author Alex Yatsenko
  * @link https://github.com/yatsenkolesh/redisphp-native
  * @TODO think about save link on connection without oop
*/


/**
  * @param string $redisServer
  * @param int $redisPort
  * @param string $errCode
  * @param $errStr
  * @return resource
*/
function redisConnect($redisServer = null, $redisPort = null, $errCode = null, $errStr = null)
{
    $redisConnect = fsockopen($redisServer, $redisPort, $errCode, $errStr);
    return $redisConnect;
}

/**
  * @param string $key
  * @param string $value
  * @param int $expiration
  * @return boolean
*/
function redisWrite($key = null, $value = null, $expiration = null)
{
  // @TODO write
}
