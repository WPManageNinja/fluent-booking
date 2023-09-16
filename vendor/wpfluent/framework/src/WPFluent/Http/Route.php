<?php

namespace FluentCalendar\Framework\Http;

use Closure;
use Exception;
use WP_REST_Request;
use WP_REST_Response;
use InvalidArgumentException;
use FluentCalendar\Framework\Validator\ValidationException;
use FluentCalendar\Framework\Database\Orm\ModelNotFoundException;

class Route
{
    /**
     * Application Instance
     * @var \FluentCalendar\Framework\Foundation\Application
     */
    protected $app = null;

    /**
     * Rest namespace from config
     * @var string
     */
    protected $restNamespace = null;

    /**
     * Full URI
     * @var string
     */
    protected $uri = null;
    
    /**
     * Compiled rest endpoint
     * @var string
     */
    protected $compiled = null;

    /**
     * Route meta data
     * @var array
     */
    protected $meta = [];

    /**
     * Rest Handler/Callback before parsing
     * @var string
     */
    protected $handler = null;
    
    /**
     * Rest Handler/Callback after parsing
     * @var callable|string
     */
    protected $action = null;
    
    /**
     * Policy Handler/Callback after parsing
     * @var string
     */
    protected $permissionHandler = [];

    /**
     * HTTP Methods
     * @var string
     */
    protected $method = null;
    
    /**
     * Rest options
     * @var array
     */
    protected $options = [];

    /**
     * Route where constraints
     * @var array
     */
    protected $wheres = [];

    /**
     * Rest namespace
     * @var string
     */
    protected $namespace = null;
    
    /**
     * Policy Handler/Callback after parsing
     * @var callable|string
     */
    protected $policyHandler = null;

    /**
     * Predefined Regex foe where constraints
     * @var array
     */
    protected $predefinedNamedRegx = [
        'int' => '[0-9]+',
        'alpha' => '[a-zA-Z]+',
        'alpha_num' => '[a-zA-Z0-9]+',
        'alpha_num_dash' => '[a-zA-Z0-9-_]+'
    ];

    /**
     * Construct the route instance
     * 
     * @param \FluentCalendar\Framework\Foundation\Application $app
     * @param string $restNamespace
     * @param string $uri
     * @param string $handler
     * @param string $method
     */
    public function __construct($app, $restNamespace, $uri, $handler, $method)
    {
        $this->app = $app;
        $this->restNamespace = $restNamespace;
        $this->uri = $uri;
        $this->handler = $handler;
        $this->method = $method;
    }

    /**
     * Alternative constructor
     * 
     * @param \FluentCalendar\Framework\Foundation\Application $app
     * @param string $restNamespace
     * @param string $uri
     * @param string $handler
     * @param string $method
     * @return self
     */
    public static function create($app, $namespace, $uri, $handler, $method)
    {
        return new static($app, $namespace, $uri, $handler, $method);
    }

    /**
     * Set route meta
     * 
     * @param  string $key
     * @param  mixed $value
     * @return self
     */
    public function meta($key, $value = null)
    {
        $meta = is_array($key) ? $key : func_get_args();

        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    /**
     * Get route meta
     * 
     * @param  string $key
     * @return mixed
     */
    public function getMeta($key = '')
    {
        if ($key && isset($this->meta[$key])) {
            return $this->meta[$key];
        }
        
        return $this->meta;
    }

    /**
     * Get route options
     * 
     * @param  string $key
     * @return mixed
     */
    public function getOptions($key = null)
    {
        return $key ? $this->options[$key] : $this->options;
    }

    /**
     * Get route action information
     * @param  string $key
     * @return mixed
     */
    public function getAction($key = '')
    {
        $action = $this->getOptions('args')['action'];

        if ($key && array_key_exists($key, $action)) {
            return $action[$key];
        }
        
        return $action;
    }

    /**
     * Set a where constrain into the route
     * 
     * @param  string $identifier
     * @param  string $value
     * @return self
     */
    public function where($identifier, $value = null)
    {
        if (!is_null($value)) {
            $this->wheres[$identifier] = $this->getValue($value);
        } else {
            foreach ($identifier as $key => $value) {
                $this->wheres[$key] = $this->getValue($value);
            }
        }

        return $this;
    }

    /**
     * Add an integer type route constraint
     * 
     * @param  string $identifiers
     * @return self
     */
    public function int($identifiers)
    {
        $identifiers = is_array($identifiers) ? $identifiers : func_get_args();

        foreach ($identifiers as $identifier) {
            $this->wheres[$identifier] = '[0-9]+';
        }

        return $this;
    }

    /**
     * Add an alpha type route constraint
     * 
     * @param  string $identifiers
     * @return self
     */
    public function alpha($identifiers)
    {
        $identifiers = is_array($identifiers) ? $identifiers : func_get_args();

        foreach ($identifiers as $identifier) {
            $this->wheres[$identifier] = '[a-zA-Z]+';
        }

        return $this;
    }

    /**
     * Add an alphanum type route constraint
     * 
     * @param  string $identifiers
     * @return self
     */
    public function alphaNum($identifiers)
    {
        $identifiers = is_array($identifiers) ? $identifiers : func_get_args();

        foreach ($identifiers as $identifier) {
            $this->wheres[$identifier] = '[a-zA-Z0-9]+';
        }

        return $this;
    }

    /**
     * Add an alphanumdash type route constraint
     * 
     * @param  string $identifiers
     * @return self
     */
    public function alphaNumDash($identifiers)
    {
        $identifiers = is_array($identifiers) ? $identifiers : func_get_args();

        foreach ($identifiers as $identifier) {
            $this->wheres[$identifier] = '[a-zA-Z0-9-_]+';
        }

        return $this;
    }

    /**
     * Set the route policy
     * @param  string $handler
     * @return null
     */
    public function withPolicy($handler)
    {
        $this->policyHandler = $handler;
    }

    /**
     * Set the namespace for controller/action
     * @param  string $ns
     * @return null
     */
    public function withNamespace($ns)
    {
        $this->namespace = implode('\\', $ns);
    }

    /**
     * Register the rest endpoint
     * 
     * @return null
     */
    public function register()
    {
        $this->setOptions();

        $uri = $this->compileRoute($this->uri);

        return register_rest_route($this->restNamespace, "/{$uri}", $this->options);
    }

    /**
     * Set route options
     * 
     * @return null
     */
    protected function setOptions()
    {
        $this->options = [
            'args' => [
                '__meta__' => $this->meta
            ],
            'methods' => $this->method,
            'callback' => [$this, 'callback'],
            'permission_callback' => [$this, 'permissionCallback']
        ];
    }

    /**
     * Get item from predefined regex
     * @param  string $value
     * @return string
     */
    protected function getValue($value)
    {
        if (array_key_exists($value, $this->predefinedNamedRegx)) {
            return $this->predefinedNamedRegx[$value];
        }

        return $value;
    }

    /**
     * Compikle the rest route to regex
     * 
     * @param  string $uri
     * @return string compiled rest endpoint
     */
    protected function compileRoute($uri)
    {
        $params = [];

        $compiledUri = preg_replace_callback('#/{(.*?)}#', function($match) use (&$params, $uri) {
            // Default regx
            $regx = '[^\s(?!/)]+';
            
            $param = trim($match[1]);

            if ($isOptional = strpos($param, '?')) {
                $param = trim($param, '?');
            }

            if (in_array($param, $params)) {
                throw new InvalidArgumentException(
                    "Duplicate parameter name '{$param}' found in {$uri}.", 500
                );
            }
            
            $params[] = $param;

            if (isset($this->wheres[$param])) {
                $regx = $this->wheres[$param];
            }

            $pattern = "/(?P<" . $param . ">" . $regx . ")";

            if ($isOptional) {
                $pattern = "(?:" . $pattern . ")?";
            }
            
            $this->options['args'][$param]['required'] = !$isOptional;
            
            return $pattern;

        }, $uri);

        return $this->compiled = $compiledUri;
    }

    /**
     * Route handler
     * 
     * @return mixed
     */
    public function callback()
    {
        try {

            $response = $this->app->call(
                $this->action,
                $this->app->request->get_url_params()
            );

            if (!($response instanceof WP_REST_Response)) {
                if (is_wp_error($response)) {
                    $response = $this->app->response->wpErrorToResponse($response);
                } else {
                    $response = $this->app->response->sendSuccess($response);
                }
            }

            return $response;

        } catch (ValidationException $e) {
            return $this->app->response->sendError(
                $e->errors(), $e->getCode()
            );
        }  catch (ModelNotFoundException $e) {
            return $this->app->response->sendError([
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return $this->app->response->sendError([
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
     * Permission callback for route
     * @param  \WP_REST_Request $wpRestRequest
     * @return mixed
     */
    public function permissionCallback($wpRestRequest)
    {
        if (!$this->app->bound('wprestrequest')) {
            $this->app->instance('wprestrequest', $wpRestRequest);
            $this->app->request->mergeInputsFromRestRequest($wpRestRequest);

            if (method_exists($this, 'prepareCallbacks')) {
                $this->app->instance('route', $this);
                $this->prepareCallbacks($this->app->request);
            }
        }

        if ($this->permissionHandler) {
            return $this->app->call(
                $this->permissionHandler,
                $this->app->request->get_url_params()
            );
        }
    }

    /**
     * Resolve the policy handler
     * 
     * @param  string $policyHandler
     * @return mixed
     */
    protected function getPolicyHandler($policyHandler)
    {
        if ($policyHandler instanceof Closure) {
            return function() use ($policyHandler) {
                $policyHandler($this->app->request);
            };
        }

        if (strpos($policyHandler, '@') !== false) return $policyHandler;

        if (strpos($policyHandler, '::') !== false) return $policyHandler;

        if ($policyHandler && $this->handler instanceof Closure) {
            throw new InvalidArgumentException(
                'Explicit policy handler is required while using a closure as route callback.'
            );
        }
        
        if ($policyHandler && !function_exists($policyHandler)) {
            if (is_string($this->handler) && strpos($this->handler, '@') !== false) {
                list($_, $method) = explode('@', $this->handler);
                $policyHandler = $policyHandler . '@' . $method;
            } else if (is_array($this->handler)) {
                $policyHandler = $policyHandler . '@' . $this->handler[1];
            }
        }

        return $policyHandler ?: [$this, 'defaultPolicyHandler'];
    }

    /**
     * Default/Fallback policy handler for the route
     * 
     * @return bool
     */
    public function defaultPolicyHandler()
    {
        return true;
    }

    /**
     * Parse the rest and permission/policy handlers
     * 
     * @param  \WP_REST_Request $request
     * @return null
     */
    public function prepareCallbacks($request)
    {
        $handler = $this->app->parseRestHandler(
            $this->handler, $this->namespace
        );

        if ($handler instanceof Closure) {
            $action = 'Closure';
            $controller = null;
        } else {
            $handler = trim($handler, '\\');
            $action = explode('@', $handler);
            $pieces = explode('\\', $action[0]);
            $controller = end($pieces);
        }

        $policyHandler = $this->app->parsePolicyHandler(
            $this->getPolicyHandler($this->policyHandler)
        );

        $this->permissionHandler = $policyHandler;

        $policyHandler[0] = get_class($policyHandler[0]);

        $this->options['args']['action'] = [
            'handler' => is_object($handler) ? $action : $handler,
            'controller' => $controller,
            'method' => is_array($action) ? $action[1] : null,
            'path' => $this->uri,
            'http_method' => $request->get_method(),
            'full_uri' => $request->get_route(),
            'permission_callback' => $policyHandler
        ];


        return $this->action = $handler;
    }

    /**
     * Dynamically access a route parameter.
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $array = $this->app->request->get_url_params();

        if (isset($array[$key])) {
            return $array[$key];
        }
    }
}
