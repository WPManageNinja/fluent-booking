<?php

namespace FluentBooking\Framework\Encryption;

interface EncrypterInterface
{
    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     * @return string
     *
     * @throws \FluentBooking\Framework\Encryption\EncryptException
     */
    public function encrypt($value, $serialize = true);

    /**
     * Decrypt the given value.
     *
     * @param  string  $payload
     * @param  bool  $unserialize
     * @return mixed
     *
     * @throws \FluentBooking\Framework\Encryption\DecryptException
     */
    public function decrypt($payload, $unserialize = true);
}
