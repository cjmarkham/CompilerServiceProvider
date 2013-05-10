<?php

namespace Cjmarkham\CompilerServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class CompilerServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['compiler'] = $app->share(function() use ($app) {
			$compiler = new Compiler();

			if (isset($app['compiler.short_values']) && $app['compiler.short_values'] === false)
			{
				$compiler->short_values = false;
			}

			if ($app['compiler.remove_empty'] === true)
			{
				$compiler->remove_empty = true;
			}

			if (isset($app['compiler.remove_units']) && $app['compiler.remove_units'] === false)
			{
				$compiler->remove_units = false;
			}

			if ($app['compiler.rgb_to_hex'] === true)
			{
				$compiler->rgb_to_hex = true;
			}

			if ($app['compiler.shorten_hex'] === true)
			{
				$compiler->shorten_hex = true;
			}

			if (!isset($app['compiler.input_dir']))
			{
				throw new \Exception('Please specify an input directory for the compiler');
			}

			$compiler->input_dir = $app['compiler.input_dir'];

			if (isset($app['compiler.output_path']))
			{
				$compiler->output_path = $app['compiler.output_path'];
			}
			else
			{
				$compiler->output_path = $app['compiler.input_dir'] . '/concat.css';
			}

			return $compiler;
		});
	}

	public function boot(Application $app) {}
}