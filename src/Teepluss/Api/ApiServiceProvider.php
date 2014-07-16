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
        $loader = AliasLoader::getInstance()->alias('API', 'Teepluss\Api\Facades\API');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register providers.
        $this->registerApi();

        // Register commands.
        $this->registerApiCallCommand();

        // Assign commands.
        $this->commands(
            'api.call'
        );
    }

    /**
     * Register Api.
     *
     * @return void
     */
    public function registerApi()
    {
        $this->app['api.request'] = $this->app->share(function($app)
        {
            $remoteClient = new Client();

            return new Api($app['config'], $app['router'], $app['request'], $remoteClient);
        });
    }

    /**
     * Register Api Call command.
     *
     * @return void
     */
    public function registerApiCallCommand()
    {
        $this->app['api.call'] = $this->app->share(function($app)
        {
            return new Commands\ApiCallCommand($app['api.request']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('api.request', 'api.call');
    }

}