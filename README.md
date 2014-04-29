## Laravel HMVC.

> This package is no longer updated anymore, Now I've splited this package into 2.
- [HMVC](https://github.com/teepluss/laravel4-hmvc)
- [RESTful format](https://github.com/teepluss/laravel4-restable)

API is a tool for making internal request (HMVC).

### Installation

- [API on Packagist](https://packagist.org/packages/teepluss/api)
- [API on GitHub](https://github.com/teepluss/laravel4-api)

To get the lastest version of Theme simply require it in your `composer.json` file.

~~~
"teepluss/api": "dev-master"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

Once Theme is installed you need to register the service provider with the application. Open up `app/config/app.php` and find the `providers` key.

~~~
'providers' => array(

    'Teepluss\Api\ApiServiceProvider'

)
~~~

API also ships with a facade which provides the static syntax for creating collections. You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~
'aliases' => array(

    'API' => 'Teepluss\Api\Facades\API'

)
~~~

Publish config using artisan CLI.

~~~
php artisan config:publish teepluss/api
~~~

## Usage

API helping you to work with internal request.

- [Internal testing request](#internal-testing-request)
- [Calling via artisan CLI](#calling-via-artisan-cli)
- [Create reponses format for RESTful](#create-reponses-format-for-restful)

### Internal testing request.

~~~php
// GET Request.
API::get('user/1');

// POST Request.
API::post('user', array('title' => 'Demo'));

// PUT Request.
API::put('user/1', array('title' => 'Changed'));

// DELETE Request.
API::delete('user/1');

// Internal request with domain route.
API::invoke('http://api.domain.com', 'post', array('param' => 1))

// You can make remote request without changing code also.
API::post('http://api.github.com', array('username' => 'teepluss'));

// Request remote with invokeRemote.
API::invokeRemote('http://api.github.com', 'post', array('username' => 'teepluss'));

// Get Guzzle to use other features.
$guzzle = API::getRemoteClient();
~~~
>> Remote request using [Guzzle](http://guzzlephp.org/) as an adapter.

### Calling via artisan CLI.

~~~lisp
// Internal GET.
$ php artisan api:call --request GET /some/route?param=value

// Internal POST.
$ php artisan api:call --request POST /some/form --data "name=Tee"

// Remote request.
$ php artisan api:call --request GET http://google.com
~~~
>> also work with DELETE, PATCH, HEAD

### Create reponses format for RESTful.

~~~php
// Response entries.
$users = User::all();
API::createResponse($users);

// Response entry.
$user = User::find($id);
return API::createResponse($user);

// Response Laravel error.
$errors = $validation->messages()->all(':message');
return API::createResponse(compact('errors'), 400);

// Response created data.
$user = Url::create($data);
return API::createResponse($user, 201);

// Response 404.
API::createResponse("User [$id] was not found.", 404);

//Response deleted.
API::createResponse(null, 204);
~~~
>> For RESTful response recommended to use [Restable](https://github.com/teepluss/laravel4-restable) instead.

## Changes

#### v1.0.1
- Add artisan CLI.

#### v1.0.0
- Release first master version.

## Support or Contact

If you have some problem, Contact teepluss@gmail.com

[![Support via PayPal](https://rawgithub.com/chris---/Donation-Badges/master/paypal.jpeg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9GEC8J7FAG6JA)