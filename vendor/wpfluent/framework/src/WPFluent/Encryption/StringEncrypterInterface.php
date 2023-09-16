<?php

namespace FluentCalendar\Framework\Encryption;

interface StringEncrypterInterface
{
    /**
     * Encrypt a string without serialization.
     *
     * @param  string  $value
     * @return string
     *
     * @throws \FluentCalendar\Framework\Encryption\EncryptException
     */
    public function encryptString($value);

    /**
     * Decrypt the given string without unserialization.
     *
     * @param  string  $payload
     * @return string
     *
     * @throws \FluentCalendar\Framework\Encryption\DecryptException
     */
    public function decryptString($payload);
}
