<?php

namespace JordJD\LaravelPasswordSecurityAudit\Objects;

use Illuminate\Contracts\Support\Arrayable;

class CrackedUser implements Arrayable
{
    private $key;
    private $password;
    private $hash;

    public function __construct($key, $password, $hash)
    {
        $this->key = $key;
        $this->password = $password;
        $this->hash = $hash;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function toArray()
    {
        return [
            'key' => $this->key,
            'password' => $this->password,
            'hash' => $this->hash,
        ];
    }
}