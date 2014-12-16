<?php namespace Teepluss\Api;

use Guzzle\Http\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Response;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Api {

    /**
     * Repository config.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Router
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * Request
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Remote client.
     *
     * @var \Guzzle\Http\Client
     */
    protected $remoteClient;

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
     * Instance API.
     *
     * @param Repository $config  $router
     * @param Router     $router
     * @param Request    $request
     * @param Client     $remote
     */
    public function __construct(Repository $config, Router $router, Request $request, Client $remoteClient)
    {
        $this->config = $config->get('api::config');

        $this->router = $router;

        $this->request = $request;

        $this->remoteClient = $remoteClient;
    }

    /**
     * Create API response.
     *
     * @param  mixed   $messages
     * @param  integer $code
     * @return string
     */
    public function createResponse($messages, $code = 200)
    {
        return $this->make($messages, $code);
    }

    /**
     * Custom API response.
     *
     * @param  mixed   $messages
     * @param  integer $code
     * @return string
     */
    public function deviseResponse($messages, $code = 200)
    {
        return $this->make($messages, $code, true);
    }

    /**
     * Make json data format.
     *
     * @param  mixed   $data
     * @param  integer $code
     * @param  boolean $overwrite
     * @return string
     */
    public function make($data, $code, $overwrite = false)
    {
        // Status returned.
        $status = (preg_match('/^(1|2|3)/', $code)) ? 'success' : 'error';

        // Change object to array.
        if (is_object($data))
        {
            $data = $data->toArray();
        }

        // Data as a string.
        if (is_string($data))
        {
            $data = array('message' => $data);
        }

        // Overwrite response format.
        if ($overwrite === true)
        {
            $response = $data;
        }
        else
        {
            $message = $this->statuses[$code];

            // Custom return message.
            if (isset($data['message']))
            {
                $message = $data['message'];

                unset($data['message']);
            }

            // Available data response.
            $response = array(
                'status'     => $status,
                'code'       => $code,
                'message'    => $message,
                'data'       => $data,
                'pagination' => null
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

            // Remove empty data.
            if ($this->config['removeEmptyData'] && empty($response['data']))
            {
                unset($response['data']);
            }
        }

        // Header response.
        $header = ($this->config['httpResponse']) ? $code : 200;

        return Response::make($response, $header);
    }

    /**
     * Remote client for http request.
     *
     * @return Client
     */
    public function getRemoteClient()
    {
        return $this->remoteClient;
    }

    /**
     * Configure remote client for http request.
     *
     * @param array $configuration
     *
     * array
     *(
     *   'verify' => false,                               //allows self signed certificates
     *   'verify', '/path/to/cacert.pem',                 //custom certificate
     *   'headers/X-Foo', 'Bar',                          //custom header
     *   'auth', array('username', 'password', 'Digest'), //custom authentication
     *)
     */
    public function configureRemoteClient($configurations)
    {
        foreach ($configurations as $option => $value)
        {
            call_user_func_array(array($this->remoteClient, 'setDefaultOption'), array($option, $value));
        }
    }

    /**
     * Call internal URI with parameters.
     *
     * @param  string $uri
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     */
    public function invoke($uri, $method, $parameters = array())
    {
        // Request URI.
        if ( ! preg_match('/^http(s)?:/', $uri))
        {
            $uri = '/'.ltrim($uri, '/');
        }

        try
        {
            // Store the original request data and route.
            $originalInput = $this->request->input();
            $originalRoute = $this->router->getCurrentRoute();

            // Masking route to allow testing with PHPUnit.
            if ( ! $originalRoute instanceof Route)
            {
                $originalRoute = new Route(new \Symfony\Component\HttpFoundation\Request());
            }

            // Create a new request to the API resource
            $request = $this->request->create($uri, strtoupper($method), $parameters);

            // Replace the request input...
            $this->request->replace($request->input());

            // Dispatch request.
            $dispatch = $this->router->dispatch($request);

            if (method_exists($dispatch, 'getOriginalContent'))
            {
                $response = $dispatch->getOriginalContent();
            }
            else
            {
                $response = $dispatch->getContent();
            }

            // Decode json content.
            if ($dispatch->headers->get('content-type') == 'application/json')
            {
                if (function_exists('json_decode') and is_string($response))
                {
                    $response = json_decode($response, true);
                }
            }

            // Restore the request input and route back to the original state.
            $this->request->replace($originalInput);


            // This method have been removed from Laravel.
            //$this->router->setCurrentRoute($originalRoute);

            return $response;
        }
        catch (NotFoundHttpException $e)
        {
            //trigger_error('Not found');
            var_dump($e->getMessage());
        }
        catch (FatalErrorException $e)
        {
            var_dump($e->getMessage());
        }
    }

    /**
     * Invoke with remote uri.
     *
     * @param  string $uri
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     */
    public function invokeRemote($uri, $method, $parameters = array())
    {
        $remoteClient = $this->getRemoteClient();

        // Make request.
        $request = call_user_func_array(array($remoteClient, $method), array($uri, null, $parameters));

        // Send request.
        $response = $request->send();

        // Body responsed.
        $body = (string) $response->getBody();

        // Decode json content.
        if ($response->getContentType() == 'application/json')
        {
            if (function_exists('json_decode') and is_string($body))
            {
                $body = json_decode($body, true);
            }
        }

        return $body;
    }

    /**
     * Alias call method.
     *
     * @return mixed
     */
    public function __call($method, $parameters = array())
    {
        if (in_array($method, array('get', 'post', 'put', 'delete')))
        {
            $uri = array_shift($parameters);

            $parameters = current($parameters);
            $parameters = is_array($parameters) ? $parameters : array();

            if (preg_match('/^http(s)?/', $uri))
            {
                return $this->invokeRemote($uri, $method, $parameters);
            }

            return $this->invoke($uri, $method, $parameters);
        }
    }

}
