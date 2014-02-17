<?php namespace Teepluss\Api\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ApiCallCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'api:call';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Call another controller via CLI.';

	/**
	 * Api.
	 *
	 * @var \Teepluss\Api\Api
	 */
	protected $api;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($api)
	{
		parent::__construct();

		$this->api = $api;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$url = $this->argument('url');

		// Remote request.
		$invoke = ($this->option('remote') === true) ? 'invokeRemote' : 'invoke';

		// Error on remote request.
		if ($invoke == 'invokeRemote' and ! preg_match('/^http(s)?:/', $url))
		{
			return $this->error('The remore request must begin with http(s).');
		}

		// Method to call.
		$method = $this->option('request');
		$method = strtolower($method);
		$method = (in_array($method, array('get', 'post', 'put', 'delete', 'patch', 'head'))) ? $method : 'get';

		// Parameters.
		$parameters = $this->option('data');

		if ($parameters)
		{
			parse_str($parameters, $parameters);
		}

		print $this->api->$invoke($url, $method, $parameters);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('url', InputArgument::REQUIRED, 'URL to call'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('request', 'X', InputOption::VALUE_OPTIONAL, 'Specifies a custom request method.', 'GET'),
			array('data', 'd', InputOption::VALUE_OPTIONAL, 'Sends the specified data in a POST request to the HTTP server.', array()),
			array('remote', 'r', InputOption::VALUE_NONE, 'Remote enable.', null)
		);
	}

}
