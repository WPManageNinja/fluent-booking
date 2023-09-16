<?php

namespace FluentBooking\Framework\Encryption;

interface StringEncrypterInterface
{
    /**
     * Encrypt a string without serialization.
     *
     * @param  string  $value
     * @return string
     *
     * @throws \FluentBooking\Framework\Encryption\EncryptException
     */
    public function encryptString($value);

    /**
     * Decrypt the given string without unserialization.
     *
     * @param  string  $payload
     * @return string
     *
     * @throws \FluentBooking\Framework\Encryption\DecryptException
     */
    public function decryptString($payload);
}
