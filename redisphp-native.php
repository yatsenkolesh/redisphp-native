<?php
/**
  * Redis php native library without oop
  * @author Alex Yatsenko
  * @link https://github.com/yatsenkolesh/redisphp-native
*/
namespace Redis;

/**
  * May be I can use static vars, but i don't know what is work faster
*/
class RedisStorage
{
  /**
    * @var rosource $connect for save redis connection
  */
  public static $connect = false;
}

/**
  * Get connect resource to redis server with sockets and save connection in RedisStorage::$connect
  * @param string $redisServer
  * @param int $redisPort
  * @param string $errCode
  * @param $errStr
  * @return resource
*/
function connect($redisServer = null, $redisPort = null, $errCode = null, $errStr = null)
{
  //Register shutdown function, which close our connection to redis
  register_shutdown_function(function()
  {
    close();
  });

  $redisConnect = RedisStorage::$connect = fsockopen($redisServer, $redisPort, $errCode, $errStr);
  return $redisConnect;
}

/**
  * @param string $key
  * @param string $value
  * @param int $expiration
  * @return boolean
*/
function set($key = null, $value = null, $expiration = null)
{
  $value = '"'. str_replace('"', '\"', $value).'"';

  $q = query('set',
  [
    $key,
    $value,
    $expiration
  ], 0);

  //check answer
  return (str_split($q)[0] == '+');
}

/**
  * @param string $key
  * @return boolean answer
*/
function del($key)
{
  return query('del',
  [
    $key
  ], 0) == ':1';
}


/**
  * Get redis key
  * @param string $key
  * @return string
*/
function get($key = null)
{
  return query('get',
  [
    $key
  ],1);
}

/**
  * Select a redis db
  * @param int id
  * @return boolean
*/
function select($id = 0)
{
  return query('select',
  [
    $id,
  ], 0);
}

/**
  * Set a hash
  * @param string $hash
  * @param array $args
  * @return boolean
*/
function hmset($hash = null, $args = [])
{
  $commandArgs = [$hash];

  foreach($args as $key => $val)
    $commandArgs[] = $key. ' '. $val;

  return query('hmset', $commandArgs, 0);
}

/**
  * Get a all relations of hash
  * @param string $hash
  * @return string
*/
function hgetall($hash = null)
{
  $t = query('hgetall',
  [
    $hash
  ],3, 'array');
  die(var_dump($t));
  $t = '*0' ? null : $t;
  return $t;
}


/**
  * Format command in redis format
  * @param string $command command to redis
  * @param array $param param to command
  * @return string
*/
function command($command = null, $params = [])
{
  foreach($params as $key => $param)
    if(is_null($param))
      unset($params[$key]);

  return $command. " ". join(" ", $params). "\r\n";
}

/**
  * Generate command and send to redis server
  * @param string $command
  * @param array $params
  * @param int $sentNum sent once
  * @param string $expected string|array
  * @return string
*/
function query($command = null, $params = [], $sentNum = 0, $expected = 'string')
{
  return send(command($command, $params),$sentNum, $expected);
}

/**
  * Send command to redis server
  * @param string $command
  * @param int $sentNum sent once
  * @param string $expexted string|array
  * @return string answer
*/
function send($command = null, $sentNum = 0, $expected = 'string')
{
  fwrite(RedisStorage::$connect, $command);

  $ret = [];

  if($sentNum > 0)
  {
    $sentNum = ($expected == 'string' ? $sentNum : $sentNum+1);

    foreach(range(0, ($sentNum-1)) as $num)
    {
      $ret[] = ltrim(trim(fgets(RedisStorage::$connect)));

      //failed to handle request
      if(ltrim(trim(fgets(RedisStorage::$connect))) == '$-1')
        return false;
    }
  }

  $cb  = (function() use ($ret, $expected)
  {
    if($expected != 'array')
      throw new Exception('Failed to return type of response: '. $expected);
    return $ret;
  });

  return $expected == 'string' ? ltrim(rtrim(fgets(RedisStorage::$connect))) : $cb();
}

/**
  * @param string $key
  * @return boolean
*/
function exists($key = null)
{
  return query('exists',
  [
    $key
  ]) == ':1';
}

/**
  * Close redis connection
*/
function close()
{
  fclose(RedisStorage::$connect);
  return ;
}
