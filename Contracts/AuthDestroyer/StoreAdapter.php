<?php
/**
 * Created by PhpStorm.
 * User: pyatseme
 * Date: 11.10.2018
 * Time: 10:06
 */

namespace App\Contracts\AuthDestroyer;
use App\Db\Main\User;

interface StoreAdapter
{
    public function save($user, string $session);
    public function remove($user, string $session);

}