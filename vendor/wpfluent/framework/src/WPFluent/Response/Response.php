<?php

namespace FluentBooking\Framework\Response;

use WP_Error;
use WP_REST_Response;

class Response
{
    /**
     * Application Instance
     * @var \FluentBooking\Framework\Foundation\Application
     */
    protected $app = null;

    /**
     * Construct the response instance
     * 
     * @param \FluentBooking\Framework\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Send json response
     * @param  array  $data
     * @param  integer $code
     * @return string|false The JSON encoded string, or false if it cannot be encoded.
     */
    public function json($data = null, $code = 200)
    {
        return wp_send_json($data, $code);
    }

    /**
     * Send json response
     * @param  array  $data
     * @param  integer $code
     * @return \WP_REST_Response
     */
    public function send($data = null, $code = 200)
    {
        return new WP_REST_Response($data, $code);
    }

    /**
     * Send a success json response
     * @param  array  $data
     * @param  integer $code
     * @return \WP_REST_Response
     */
    public function sendSuccess($data = null, $code = 200)
    {
         return new WP_REST_Response($data, $code);
    }

    /**
     * Send an error json response
     * @param  array  $data
     * @param  integer $code
     * @return \WP_REST_Response
     */
    public function sendError($data = null, $code = 423)
    {
        if (!$code || $code < 400 ) {
            $code = 423;
        }

        return new WP_REST_Response($data, $code);
    }

    /**
     * Convert the WP_Error to WP_REST_Response
     * 
     * @param  \WP_Error $wpError
     * @return \WP_REST_Response
     */
    public function wpErrorToResponse(WP_Error $wpError)
    {
        return rest_convert_error_to_response($wpError);
    }
}
