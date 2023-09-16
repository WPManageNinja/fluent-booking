<?php

namespace FluentBooking\Framework\Request;

use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Support\Helper;
use FluentBooking\Framework\Foundation\Application;
use FluentBooking\Framework\Validator\ValidationException;

class Request
{
    use FileHandler, Cleaner, InputHelperMethodsTrait;

    /**
     * The application instance
     * @var \FluentBooking\Framework\Foundation\Application
     */
    protected $app = null;

    /**
     * PHP header variables
     * @var array
     */
    protected $headers = [];

    /**
     * PHP server variables
     * @var array
     */
    protected $server = [];

    /**
     * PHP cookie variables
     * @var array
     */
    protected $cookie = [];

    /**
     * The JSON payload of the request
     * @var array
     */
    protected $json = [];

    /**
     * PHP $_GET Superglobal
     * @var array
     */
    protected $get = [];


    /**
     * PHP $_POST Superglobal
     * @var array
     */
    protected $post = [];

    /**
     * PHP $_FILES Superglobal
     * @var array
     */
    protected $files = [];

    /**
     * PHP $_GET and $_POST Superglobals
     * @var array
     */
    protected $request = [];

    /**
     * WP_REST_Request instance
     * @var WP_REST_Request
     */
    protected $wpRestRequest = false;

    /**
     * Construct the request instance
     * @param \FluentBooking\Framework\Foundation\Application $app
     * @param array/$_GET $get
     * @param array/$_POST $post
     * @param array/$_FILES $files
     */
    public function __construct(Application $app, $get, $post, $files)
    {
        $this->app = $app;
        $this->server = $_SERVER;
        $this->cookie = $_COOKIE;
        $this->files = $this->prepareFiles($files);

        $this->request = array_merge(
            $this->get = $this->clean($get),
            $this->post = $this->clean($post)
        );
    }

    /**
     * Variable exists
     * @param  string $key
     * @return bool
     */
    public function exists($key)
    {
        return Arr::has($this->inputs(), $key);
    }

    /**
     * Variable exists and has truthy value
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->exists($key) && !empty(Arr::get($this->inputs(), $key));
    }

    /**
     * Set an item into the request inputs
     * @param string $key
     * @param mixed
     */
    public function set($key, $value)
    {
        Arr::set($this->request, $key, $value);

        return $this;
    }

    /**
     * Retrive all the items from the request inputs
     * @return array
     */
    public function all()
    {
        return $this->get();
    }

    /**
     * Retrieve an item from the request inputs
     * @param  string|null $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return Arr::get($this->inputs(), $key, $default);
    }

    /**
     * Get an item from the request filtering by the callback
     * 
     * @param  string|null $key
     * @param  callable $callback
     * @param  mixed $default
     * @return mixed
     */
    public function getSafe($key = null, $callback = null, $default = null)
    {
        $value = $this->get($key, $default);

        $value = $callback ? $callback($value) : $value;

        return $value;
    }

    /**
     * Check the content-type for JSON
     * 
     * @return boolean
     */
    public function isJson()
    {
        return $this->is_json_content_type();
    }

    /**
     * Retrieve an item from the json payload of the request
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function json($key = null, $default = null)
    {
        if (!$this->isJson()) return;
        
        if (!isset($this->json)) {
            $this->json = (array) json_decode($this->getContent(), true);
        }

        if (is_null($key)) {
            return $this->json;
        }

        return Helper::dataGet($this->json, $key, $default);
    }

    /**
     * Retrieve an item from the PHP $_SERVER array
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function server($key = null, $default = null)
    {
        return $key ? Arr::get($this->server, $key, $default) : $this->server;
    }

    /**
     * Retrieve an item from the PHP headers
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function header($key = null, $default = null)
    {
        if (!$this->headers) {
            $this->headers = $this->setHeaders();
        }

        return $key ? Arr::get($this->headers, $key, $default) : $this->headers;
    }

    /**
     * Retrieve an item from the cookie
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function cookie($key = null, $default = null)
    {
        return $key ? Arr::get($this->cookie, $key, $default) : $this->cookie;
    }

    /**
     * Get the files from the request.
     *
     * @return array
     */
    public function files()
    {
        return $this->files;
    }

    /**
     * Get an item from the PHP $_GET array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function query($key = null, $default = null)
    {
        return $key ? Arr::get($this->get, $key, $default) : $this->get;
    }

    /**
     * Get an item from the PHP $_POST array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        return $key ? Arr::get($this->post, $key, $default) : $this->post;
    }

    /**
     * Return the only items given in the args
     * @param  array $keys
     * @return array
     */
    public function only($keys)
    {
        return Arr::only($this->inputs(), $keys);
    }

    /**
     * Return a subset of the request inputs except the given args
     * @param  array $args
     * @return array
     */
    public function except($args)
    {
        return Arr::except($this->inputs(), $args);
    }

    /**
     * Merge array with the request inputs
     * @param  array  $data
     * @return self
     */
    public function merge(array $data = [])
    {
        $this->request = array_replace($this->inputs(), $data);

        return $this;
    }

    /**
     * Returns the request body content.
     *
     * @param bool $asResource If true, a resource will be returned
     *
     * @return string|resource
     */
    public function getContent()
    {
        if (null === $this->content || false === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    public function mergeInputsFromRestRequest($wpRestRequest)
    {
        $this->request = array_merge(
            $this->request, $wpRestRequest->get_params()
        );

        $this->post = array_merge(
            $this->post, $this->clean($wpRestRequest->get_body_params())
        );

        $this->get = array_merge(
            $this->get, $this->clean($wpRestRequest->get_query_params())
        );

        $this->wpRestRequest = true;
    }

    /**
     * Get all inputs
     * @return array $this->request
     */
    protected function inputs()
    {
        if (!$this->wpRestRequest) {
            if ($this->app->bound('wprestrequest')) {
                $this->mergeInputsFromRestRequest($this->app->wprestrequest);
            }
        }

        return $this->request;
    }

    /**
     * Get user ip address
     * @return string
     */
    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $this->server('HTTP_CLIENT_IP');
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $this->server('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $this->server('REMOTE_ADDR');
        }

        return $ip;
    }

    /**
     * Taken and modified from Symfony
     */
    public function setHeaders()
    {
        $headers = array();
        $parameters = $this->server;
        $contentHeaders = array('CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true);
        foreach ($parameters as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } // CONTENT_* are not prefixed with HTTP_
            elseif (isset($contentHeaders[$key])) {
                $headers[$key] = $value;
            }
        }

        if (isset($parameters['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $parameters['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW'] = isset($parameters['PHP_AUTH_PW']) ? $parameters['PHP_AUTH_PW'] : '';
        } else {
            /*
             * php-cgi under Apache does not pass HTTP Basic user/pass to PHP by default
             * For this workaround to work, add these lines to your .htaccess file:
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             *
             * A sample .htaccess file:
             * RewriteEngine On
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             * RewriteCond %{REQUEST_FILENAME} !-f
             * RewriteRule ^(.*)$ app.php [QSA,L]
             */

            $authorizationHeader = null;
            if (isset($parameters['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $parameters['HTTP_AUTHORIZATION'];
            } elseif (isset($parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $parameters['REDIRECT_HTTP_AUTHORIZATION'];
            }

            if (null !== $authorizationHeader) {
                if (0 === stripos($authorizationHeader, 'basic ')) {
                    // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
                    $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)), 2);
                    if (count($exploded) == 2) {
                        list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                    }
                } elseif (empty($parameters['PHP_AUTH_DIGEST']) && (0 === stripos($authorizationHeader, 'digest '))) {
                    // In some circumstances PHP_AUTH_DIGEST needs to be set
                    $headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
                    $parameters['PHP_AUTH_DIGEST'] = $authorizationHeader;
                } elseif (0 === stripos($authorizationHeader, 'bearer ')) {
                    /*
                     * XXX: Since there is no PHP_AUTH_BEARER in PHP predefined variables,
                     *      I'll just set $headers['AUTHORIZATION'] here.
                     *      http://php.net/manual/en/reserved.variables.server.php
                     */
                    $headers['AUTHORIZATION'] = $authorizationHeader;
                }
            }
        }

        if (isset($headers['AUTHORIZATION'])) {
            return $headers;
        }

        // PHP_AUTH_USER/PHP_AUTH_PW
        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['AUTHORIZATION'] = 'Basic '.base64_encode($headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW']);
        } elseif (isset($headers['PHP_AUTH_DIGEST'])) {
            $headers['AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
        }

        return $headers;
    }

    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url()
    {
        return get_site_url() . rtrim(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']), '/');
    }

    /**
     * Validate the request.
     *
     * @param  string $key
     * @return mixed
     */
    public function validate(array $rules, array $messages = [])
    {
        $instance = $this->app->make('validator');

        $validator = $instance->make($this->all(), $rules, $messages);

        if ($validator->validate()->fails()) {
            throw new ValidationException(
                'Unprocessable Entity!', 422, null, $validator->errors()
            );
        }
    }

    /**
     * Get an input element from the request.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamyc method calls (specially for WP_rest_request)
     * @param  string $method
     * @param  array $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        if ($this->app->bound('wprestrequest')) {

            if ($method == 'route') {
                
                if ($params) {
                    return $this->app->route->{$params[0]};
                }

                return $this->app->route;
            }
            
            if (!method_exists($this->app->wprestrequest, $method)) {
                $method = strtolower(
                    preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $method)
                );
            }

            return call_user_func_array([$this->app->wprestrequest, $method], $params);
        }
    }
}
