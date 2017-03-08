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
  $value = '"'.$value.'"';

  $q = query('SET',
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
  return query('DEL',
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
  return query('GET',
  [
    $key
  ],1);
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
  * @return string
*/
function query($command = null, $params = [], $sentNum = 0)
{
  return send(command($command, $params),$sentNum);
}

/**
  * Send command to redis server
  * @param string $command
  * @param int $sentNum sent once
  * @return string answer
*/
function send($command = null, $sentNum = 0)
{
  fwrite(RedisStorage::$connect, $command);

  if($sentNum > 0)
    foreach(range(0, ($sentNum-1)) as $num)
    {
      if(ltrim(trim(fgets(RedisStorage::$connect))) == '$-1')
        return false;
    }

  return ltrim(rtrim(fgets(RedisStorage::$connect)));
}

/**
  * @param string $key
  * @return boolean
*/
function exists($key = null)
{
  return query('EXISTS',
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
