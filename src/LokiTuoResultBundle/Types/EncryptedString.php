<?php

namespace LokiTuoResultBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use LokiTuoResultBundle\Util\CryptoInterface;

class EncryptedString extends StringType
{
    /** @var CryptoInterface */
    protected static $crypto;

    public static function setCrypto(CryptoInterface $crypto)
    {
        static::$crypto = $crypto;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $value = parent::convertToDatabaseValue($value, $platform);
        return static::$crypto->encrypt($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $value = parent::convertToPHPValue($value, $platform);
        return static::$crypto->decrypt($value);
    }

    public function getName()
    {
        return 'encrypted_string';
    }
}
