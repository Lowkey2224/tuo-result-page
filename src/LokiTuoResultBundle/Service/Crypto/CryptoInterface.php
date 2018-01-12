<?php


namespace LokiTuoResultBundle\Service\Crypto;


interface CryptoInterface
{
    /**
     * Encrypts the given String
     * @param string $text
     * @return string
     */
    public function encrypt(string $text): string;

    /**
     * decrypts the given string
     * @param string $text
     * @return string
     */
    public function decrypt(string $text): string;
}