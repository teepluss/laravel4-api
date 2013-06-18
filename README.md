## API Response for Laravel 4

API is a useful to create response format and testing request.

### Installation

- [API on Packagist](https://packagist.org/packages/teepluss/api)
- [API on GitHub](https://github.com/teepluss/laravel-restful)

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

Theme also ships with a facade which provides the static syntax for creating collections. You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~
'aliases' => array(

    'API' => 'Teepluss\Api\Facades\Api'

)
~~~

## Usage

~~~php

~~~