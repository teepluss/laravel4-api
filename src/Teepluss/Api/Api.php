<?php namespace Teepluss\Api;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class Api {

	/**
     * @var array HTTP response codes and messages
     */
	protected $statuses = array(
        //Informational 1xx
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        //Successful 2xx
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        //Redirection 3xx
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 (Unused)',
        307 => '307 Temporary Redirect',
        //Client Error 4xx
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        422 => '422 Unprocessable Entity',
        423 => '423 Locked',
        //Server Error 5xx
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported'
    );

    /**
     * Create API response.
     *
     * @param  mixed   $messages
     * @param  integer $code
     * @return string
     */
	public function createResponse($messages, $code = 200)
	{
		return  $this->make($messages, $code);
	}

    /**
     * Make json data format.
     *
     * @param  mixed   $data
     * @param  integer $code
     * @return string
     */
	public function make($data, $code)
	{
		// Status returned.
		$status = (preg_match('/^2/', $code)) ? 'success' : 'error';

		// Change object to array.
		if (is_object($data))
		{
			$data = $data->toArray();
		}

		// Available data response.
		$response = array(
			'status'     => $status,
			'data'       => $data,
			'pagination' => null,
			'message'    => $this->statuses[$code]
		);

		// Merge if data has anything else.
		if (isset($data['data']))
		{
			$response = array_merge($response, $data);
		}

		// Remove empty array.
		$response = array_filter($response, function($value)
		{
			return ! is_null($value);
		});

		// Always return 200 header.
		return Response::json($response, 200);
	}

    /**
     * Call internal URI with parameters.
     *
     * @param  string $uri
     * @param  string $request
     * @param  array  $parameters
     * @return mixed
     */
    public function call($uri, $request, $parameters = array())
    {
        $uri = '/'.ltrim($uri, '/');

        // Parameters for GET, POST
        $parameters = ($parameters) ? current($parameters) : array();

        // Make request.
        $request = Request::create($uri, strtoupper($request));

        // Replacce input with parameters.
        Request::replace($parameters);

        // Dispatch.
        return Route::dispatch($request)->getContent();
    }

    /**
     * Alias call.
     *
     * @return call
     */
    public function __call($method, $parameters = array())
    {
        if (in_array($method, array('get', 'post', 'put', 'delete')))
        {
            $uri = array_shift($parameters);

            return $this->call($uri, $method, $parameters);
        }
    }

}