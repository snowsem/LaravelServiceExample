<?php

namespace App\Service\AuthDestroyer\StoreAdapters;

use App\Contracts\AuthDestroyer\StoreAdapter;
use App\Service\AuthDestroyer\Store\RedisStore;

/**
 * Class RedisStoreAdapter
 * @package App\Service\AuthDestroyer\StoreAdapters
 */
class RedisStoreAdapter implements StoreAdapter
{
    /**
     * @var RedisStore
     */
    public $store;

    /**
     * @var mixed
     */
    public $config;

    /**
     * RedisStoreAdapter constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->store = new RedisStore($config);
    }

    /**
     * @param $userId
     * @param string $session
     */
    public function save($userId, string $session)
    {
        $this->store->saveSession($userId, $session);
    }

    /**
     * @param $userId
     * @param string|null $session
     */
    public function remove($userId, string $session = null)
    {
        $this->store->removeSession($userId, $session);
    }
}