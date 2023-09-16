<?php

namespace FluentCalendar\Framework\Encryption;

use RuntimeException;
use FluentCalendar\Framework\Encryption\EncryptException;
use FluentCalendar\Framework\Encryption\DecryptException;
use FluentCalendar\Framework\Encryption\EncrypterInterface;
use FluentCalendar\Framework\Encryption\StringEncrypterInterface;

class Encrypter implements EncrypterInterface, StringEncrypterInterface
{
    /**
     * The application instance.
     *
     * @var \FluentCalendar\Framework\Foundation\Application
     */
    protected $app;

    /**
     * The encryption key.
     *
     * @var string
     */
    protected $key;

    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    protected $cipher;

    /**
     * The supported cipher algorithms and their properties.
     *
     * @var array
     */
    private static $supportedCiphers = [
        'aes-128-cbc' => ['size' => 16, 'aead' => false],
        'aes-256-cbc' => ['size' => 32, 'aead' => false],
        'aes-128-gcm' => ['size' => 16, 'aead' => true],
        'aes-256-gcm' => ['size' => 32, 'aead' => true],
    ];

    /**
     * Create a new encrypter instance.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return void
     *
     * @throws \RuntimeException
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Check if the key & cipher is valid.
     * 
     * @throws \RuntimeException
     */
    public function checkIfSupported()
    {
        if (!static::supported($this->getKey(), $this->getCipher())) {
            $ciphers = implode(', ', array_keys(self::$supportedCiphers));

            throw new RuntimeException(
                "Unsupported cipher or incorrect key length. Supported ciphers are: {$ciphers}."
            );
        }
    }

    /**
     * Determine if the given key and cipher combination is valid.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return bool
     */
    public static function supported($key, $cipher)
    {
        if (!isset(self::$supportedCiphers[strtolower($cipher)])) {
            return false;
        }

        return mb_strlen($key, '8bit') === self::$supportedCiphers[strtolower($cipher)]['size'];
    }

    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     * @return string
     *
     * @throws \FluentCalendar\Framework\Encryption\EncryptException
     */
    public function encrypt($value, $serialize = true)
    {
        $this->checkIfSupported();

        $iv = random_bytes(openssl_cipher_iv_length(strtolower($this->getCipher())));

        $tag = '';

        $value = self::$supportedCiphers[strtolower($this->getCipher())]['aead']
            ? \openssl_encrypt(
                $serialize ? serialize($value) : $value,
                strtolower($this->getCipher()), $this->getKey(), 0, $iv, $tag
            )
            : \openssl_encrypt(
                $serialize ? serialize($value) : $value,
                strtolower($this->getCipher()), $this->getKey(), 0, $iv
            );

        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }

        $iv = base64_encode($iv);
        $tag = base64_encode($tag);

        $mac = self::$supportedCiphers[strtolower($this->getCipher())]['aead']
            ? '' // For AEAD-algoritms, the tag / MAC is returned by openssl_encrypt...
            : $this->hash($iv, $value);

        $json = json_encode(compact('iv', 'value', 'mac', 'tag'), JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * Encrypt a string without serialization.
     *
     * @param  string  $value
     * @return string
     *
     * @throws \FluentCalendar\Framework\Encryption\EncryptException
     */
    public function encryptString($value)
    {
        return $this->encrypt($value, false);
    }

    /**
     * Decrypt the given value.
     *
     * @param  string  $payload
     * @param  bool  $unserialize
     * @return mixed
     *
     * @throws \FluentCalendar\Framework\Encryption\DecryptException
     */
    public function decrypt($payload, $unserialize = true)
    {
        $this->checkIfSupported();

        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        $this->ensureTagIsValid(
            $tag = empty($payload['tag']) ? null : base64_decode($payload['tag'])
        );

        // Here we will decrypt the value. If we are able to successfully decrypt it
        // we will then unserialize it and return it out to the caller. If we are
        // unable to decrypt this value we will throw out an exception message.
        $decrypted = \openssl_decrypt(
            $payload['value'], strtolower($this->getCipher()), $this->getKey(), 0, $iv, $tag ?: ''
        );

        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Decrypt the given string without unserialization.
     *
     * @param  string  $payload
     * @return string
     *
     * @throws \FluentCalendar\Framework\Encryption\DecryptException
     */
    public function decryptString($payload)
    {
        return $this->decrypt($payload, false);
    }

    /**
     * Create a MAC for the given value.
     *
     * @param  string  $iv
     * @param  mixed  $value
     * @return string
     */
    protected function hash($iv, $value)
    {
        return hash_hmac('sha256', $iv.$value, $this->getKey());
    }

    /**
     * Get the JSON array from the given payload.
     *
     * @param  string  $payload
     * @return array
     *
     * @throws \FluentCalendar\Framework\Encryption\DecryptException
     */
    protected function getJsonPayload($payload)
    {
        $payload = json_decode(base64_decode($payload), true);

        // If the payload is not valid JSON or does not have the proper keys set we will
        // assume it is invalid and bail out of the routine since we will not be able
        // to decrypt the given value. We'll also check the MAC for this encryption.
        if (! $this->validPayload($payload)) {
            throw new DecryptException('The payload is invalid.');
        }

        if (! self::$supportedCiphers[strtolower($this->getCipher())]['aead'] && ! $this->validMac($payload)) {
            throw new DecryptException('The MAC is invalid.');
        }

        return $payload;
    }

    /**
     * Verify that the encryption payload is valid.
     *
     * @param  mixed  $payload
     * @return bool
     */
    protected function validPayload($payload)
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']) &&
            strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length(strtolower($this->getCipher()));
    }

    /**
     * Determine if the MAC for the given payload is valid.
     *
     * @param  array  $payload
     * @return bool
     */
    protected function validMac(array $payload)
    {
        return hash_equals(
            $this->hash($payload['iv'], $payload['value']), $payload['mac']
        );
    }

    /**
     * Ensure the given tag is a valid tag given the selected cipher.
     *
     * @param  string  $tag
     * @return void
     */
    protected function ensureTagIsValid($tag)
    {
        if (self::$supportedCiphers[strtolower($this->getCipher())]['aead'] && strlen($tag) !== 16) {
            throw new DecryptException('Could not decrypt the data.');
        }

        if (! self::$supportedCiphers[strtolower($this->getCipher())]['aead'] && is_string($tag)) {
            throw new DecryptException('Unable to use tag because the cipher algorithm does not support AEAD.');
        }
    }

    /**
     * Get the encryption key.
     *
     * @return string
     */
    public function getKey()
    {
        if (!$this->key) {

            $slug = $this->app->config->get('app.slug');

            $this->key = $this->app->applyFilters(
                $slug . '_encryption_key', $this->getDefaultKey()
            );

            // If base64_encoded string then decode it.
            if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $this->key)) {
                $this->key = base64_decode($this->key);
            }
        }

        return $this->key;
    }

    /**
     * Get the default/fallback encryption key
     * 
     * @return string A 16 characters long string
     */
    public function getDefaultKey()
    {
        return $this->app->config->get('app.key');
    }

    /**
     * Get the encryption cipher.
     *
     * @return string
     */
    public function getCipher()
    {
        if (!$this->cipher) {
            $slug = $this->app->config->get('app.slug');

            $this->cipher = $this->app->applyFilters(
                $slug . '_encryption_cipher', $this->getDefaultCipher()
            );
        }

        return $this->cipher;
    }

    /**
     * Get the default/fallback encryption cipher
     * 
     * @return string aes-256-cbc
     */
    public function getDefaultCipher()
    {
        return $this->app->make('config')->get('app.cipher', 'aes-256-cbc');
    }

    /**
     * Create a new encryption key for the given cipher.
     *
     * @param  string  $cipher
     * @return string
     */
    public static function generateKey($cipher)
    {
        return random_bytes(self::$supportedCiphers[strtolower($cipher)]['size'] ?: 32);
    }
}
