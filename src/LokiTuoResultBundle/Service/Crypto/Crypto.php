<?php

namespace LokiTuoResultBundle\Service\Crypto;

class Crypto implements CryptoInterface
{
    private $method = "AES-256-CBC";
    private $key;
    private $iv;

    public function __construct($method, $key, $iv)
    {
        $this->method = $method;
        $this->key = $key;
        $this->iv = $iv;
    }

    /**
     * @param string $text
     * @return string
     */
    public function encrypt(string $text): string
    {
        $options = 0;
        return openssl_encrypt($text, $this->method, $this->key, $options, $this->iv);
    }

    /**
     * @param string $text
     * @return string
     */
    public function decrypt(string $text): string
    {
        $options = 0;
        return openssl_decrypt($text, $this->method, $this->key, $options, $this->iv);
    }
}
