<?php

namespace App\Service\AuthDestroyer;

use App\Contracts\AuthDestroyer\StoreAdapter;
use App\Db\Main\User;
use App\Service\AuthDestroyer\StoreAdapters\RedisStoreAdapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Class AuthDestroyer
 * @package App\Service\AuthDestroyer
 */
class AuthDestroyer
{
    /**
     * @var mixed
     */
    private $config = null;

    /**
     * @var StoreAdapter
     */
    private $store = null;

    /**
     * @var mixed
     */
    private $userId = null;

    /**
     * @var mixed
     */
    private $userSession = null;

    /**
     * @var array
     */
    private $availableMethods = [
        'saveSessionByWeb',
        'removeSessionsByWeb',
        'removeSessionsByApi',
    ];

    /**
     * @var bool
     */
    private $checkReadyServiceInCall = true;

    /**
     * AuthDestroyer constructor.
     */
    public function __construct()
    {
        $this->getAppConfig();
        $this->setStoreDriver();
    }

    /**
     * @param null $userId
     * @param null $session
     */
    private function saveSessionByWeb($userId = null, $session = null)
    {
        //$this->userId = $this->getCurrentUserId();
        $this->userSession = $this->getCurrentSession();

        if ($userId) {
            $this->userId = $userId;
        }

        if ($session) {
            $this->userSession = $session;
        }

        if ($this->userId && $this->userSession) {
            $this->store->save($this->userId, $this->userSession);
        }
    }

    /**
     * @param null $userId
     */
    private function removeSessionsByWeb($userId = null)
    {
        //$this->userId = $this->getCurrentUserId();

        if ($userId) {
            $this->userId = $userId;
        }

        if ($this->userId) {
            $this->store->remove($this->userId);
            $this->revokeUserTokens($this->userId);
        }

        Session::flush();
    }

    /**
     * @param null $userId
     */
    private function removeSessionsByApi($userId = null)
    {
        //$this->userId = $this->getCurrentUserId();

        if ($userId) {
            $this->userId = $userId;
        }

        if ($this->userId) {
            $this->store->remove($this->userId);
            $this->revokeUserTokens($this->userId);
        }

        Session::flush();
    }

    /**
     * @param null $userId
     */
    private function revokeUserTokens($userId = null)
    {
        //$this->userId = $this->getCurrentUserId();

        if ($userId) {
            $this->userId = $userId;
        }

        if ($this->userId) {
            $user = User::find($this->userId);

            if ($user) {
                $tokens = $user->tokens;
                if ($tokens) {
                    foreach ($tokens as $token) {
                        $token->revoke();
                    }
                }
            }
        }
    }

    /**
     * @return bool|string
     */
    private function getCurrentSession()
    {
        $session = Session::getId();

        if (!empty($session)) {
            return $session;
        }

        return false;
    }

    /**
     * @return \App\Db\Main\User|bool|\Illuminate\Contracts\Auth\Authenticatable|null
     */
    private function getCurrentUserId()
    {
        if (Auth::check()) {
            if (isset(Auth::user()->id)) {
                return Auth::user()->id;
            }
        }

        return false;
    }

    private function getAppConfig()
    {
        $config = [
            'cachePrefix' => config('cache.prefix'),
            'sessionDriver' => config('session.driver'),
        ];

        $this->config = $config;
    }

    /**
     * @return bool
     */
    private function configCheck()
    {
        if (!isset($this->config['cachePrefix']) && empty($this->config['cachePrefix'])) {
            return false;
        }

        if (!isset($this->config['sessionDriver']) && !isset($this->availableDrivers[$this->config['sessionDriver']])) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function storeCheck()
    {
        if (!isset($this->store) && empty($this->store)) {
            return false;
        }

        return true;
    }

    private function setStoreDriver()
    {
        $storeAdapter = $this->config['sessionDriver'];
        $storeAdapterClass = null;

        switch ($storeAdapter) {

            case "redis":
                $storeAdapterClass = RedisStoreAdapter::class;
                break;

            default:
                $storeAdapterClass = null;
                break;
        }

        if ($storeAdapterClass) {
            $this->store = new $storeAdapterClass($this->config);
        }
    }

    /**
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $execute = true;

        if ($this->checkReadyServiceInCall) {
            if (!$this->checkReadyService()) {
                $execute = false;
            }
        }

        if ($this->isAvailableMethods($method)) {
            if (method_exists($this, $method) && $execute) {
                return call_user_func_array([$this, $method], $arguments);
            } else {

            }
        } else {

        }
    }

    /**
     * @return bool
     */
    private function checkReadyService()
    {
        $params = [$this->config, $this->store];

        if ($this->configCheck() && $this->storeCheck()) {

            if (in_array(null, $params, true) === false) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @param $method
     *
     * @return bool
     */
    public function isAvailableMethods($method)
    {
        if (in_array($method, $this->availableMethods)) {
            return true;
        }

        return false;
    }

    private function getUserById($id)
    {
        $user = User::where('id', '=', $id)->first();

        if ($user) {
            return $user;
        }

        return false;
    }
}