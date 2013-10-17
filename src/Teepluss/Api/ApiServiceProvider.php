<?php namespace Teepluss\Api;

use Guzzle\Http\Client;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap classes for packages.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('teepluss/api');

		// Auto create app alias with boot method.
		$loader = AliasLoader::getInstance();
		$loader->alias('API', 'Teepluss\Api\Facades\Api');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['api'] = $this->app->share(function($app)
		{
			$remoteClient = new Client();

			return new Api($app['config'], $app['router'], $app['request'], $remoteClient);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('api');
	}

}