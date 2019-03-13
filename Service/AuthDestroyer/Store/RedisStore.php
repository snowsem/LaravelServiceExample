<?php

namespace App\Service\AuthDestroyer\Store;

use Illuminate\Support\Facades\Redis;

/**
 * Class RedisStore
 * @package App\Service\AuthDestroyer\Store
 */
class RedisStore
{
    /**
     * @var \Illuminate\Redis\Connections\Connection
     */
    private $connection;

    /**
     * @var mixed
     */
    private $config;

    /**
     * @var string
     */
    private $prefix = 'default';

    /**
     * @var string
     */
    private $key = ':user:sessions:';

    /**
     * RedisStore constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->connection = Redis::connection();
        $this->prefix = $config['cachePrefix'];
    }

    /**
     * @param $userId
     * @param $session
     */
    public function saveSession($userId, $session)
    {
        $this->connection->sadd($this->prefix . $this->key . $userId, $session);
    }

    /**
     * @param $userId
     * @param null $session
     */
    public function removeSession($userId, $session = null)
    {
        $sessions = $this->getAllSessionByUserId($userId);
        //$currentSession = $session;

        if ($sessions) {

            foreach ($sessions as $sessionId) {
                //if ($currentSession == $sessionId) {
                //continue;
                //}
                //print $this->prefix . $this->key . $userId;
                $this->connection->srem($this->prefix . $this->key . $userId, $sessionId);
                $this->connection->del($this->prefix . ':' . $sessionId);
            }
        }
    }

    /**
     * @param $userId
     *
     * @return array|bool
     */
    public function getAllSessionByUserId($userId)
    {
        $sessions = $this->connection->smembers($this->prefix . $this->key . $userId);

        if ($sessions) {
            return $sessions;
        }

        return false;
    }
}