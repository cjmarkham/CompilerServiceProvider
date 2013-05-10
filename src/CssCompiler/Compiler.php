<?php

namespace CssCompiler;

class Compiler
{
	protected $raw_data;

	public $input_dir;

	public $output_path;

	protected $compiled;

	public $short_values = true;

	public $remove_empty = false;

	public $remove_units = true;

	public $rgb_to_hex = false;

	public $shorten_hex = false;

	public function compile_css()
	{
		if (empty($this->input_dir))
		{
			throw new \Exception('Please specify a directory for the compiler');
		}

		$this->loop($this->input_dir);

		if (!isset($this->raw_data) || strlen($this->raw_data) === 0)
		{
			throw new \Exception('Could not find the source file or no CSS was found');
		}

		$this->compiled = $this->raw_data;

		$this->compiled = preg_replace('/\/(\*{1,})(.*?)(\*{1,})\//si', '', $this->compiled);

		// Remove blocks with no rules
		if ($this->remove_empty) 
		{
			$this->compiled = preg_replace('/(\.|#)?([a-zA-Z0-9\-\_]+)\s*\{\s*\}/si', '', $this->compiled);
		}

		// Remove unneeded units Eg 0px -> 0
		if ($this->remove_units)
		{
			$this->compiled = preg_replace('/([\s:])(0)(px|em|%|in|cm|mm|pc|pt|ex)/', '$1$2', $this->compiled);
		}

		if ($this->rgb_to_hex)
		{
			if (preg_match_all('/rgb\((.*)\);/i', $this->compiled, $matches))
			{
				foreach ($matches[1] as $key => $match)
				{
					list ($r, $g, $b) = explode(',', $match);
					$hex = "#";
					$hex .= str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
					$hex .= str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
					$hex .= str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
					$hex .= ';';

					$this->compiled = str_replace($matches[0][$key], $hex, $this->compiled);
				}
			}
		}

		if ($this->shorten_hex)
		{
			if (preg_match_all('/#([a-f0-9]){3}(([a-f0-9]){3})?\b/i', $this->compiled, $matches))
			{
				foreach ($matches[0] as $k => $match)
				{
					$r = $g = $b = array();
					if (strlen($match) === 7)
					{
						list ($r[0], $r[1], $g[0], $g[1], $b[0], $b[1]) = str_split(str_replace('#', '', $match));
						
						if ($r[0] === $r[1] && $g[0] === $g[1] && $b[0] === $b[1])
						{
							$hex = '#' . $r[0].$g[0].$b[0];
						}
						else
						{
							$hex = '#' . $r[0].$r[1].$g[0].$g[1].$b[0].$b[1];
						}

						$this->compiled = str_replace($matches[0][$k], $hex, $this->compiled);
					}
				}
			}
		}

		$this->compiled = preg_replace('/(\\r\\n|\\r|\\n|\s{2,})/si', '', $this->compiled);
	
		$this->output();
	}

	protected function output()
	{
		if (!$this->output_path)
		{
			throw new \Exception('Please specify an output path for the compiler');
		}

		file_put_contents($this->output_path, $this->compiled);
	}

	protected function loop($directory)
	{
		if (!is_dir($directory))
		{
			throw new \Exception($directory . ' is not a valid directory');
		}

		foreach (glob($directory . '/*') as $file)
		{
			if ($file === '.' || $file === '..')
			{
				continue;
			}

			if (is_file($file))
			{
				$this->raw_data .= file_get_contents($file);
			}
			else
			{
				$this->loop($file);
			}
		}
	}
}