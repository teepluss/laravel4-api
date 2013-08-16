<?php namespace Teepluss\Api;

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
		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
		 	$loader->alias('API', 'Teepluss\Api\Facades\Api');
		});
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
			return new Api($app['router'], $app['request']);
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