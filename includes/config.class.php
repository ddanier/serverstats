<?php

class config implements ArrayAccess
{
	private $vars;
	private $dir;

	public function __construct(&$vars, $dir)
	{
		$this->vars = &$vars;
		$this->dir = $dir;
	}
	
	private function checkOffset($offset)
	{
		if (!preg_match('/[a-z0-9]+/i'))
		{
			throw new Exception('Offsetname "' . $offset . '" is not allowed');
		}
	}
	
	public function offsetExists($offset)
	{
		if (isset($this->vars[$offset]))
		{
			return true;
		}
		else
		{
			$this->checkOffset($offset);
			if (file_exists($this->dir . $offset . '.php'))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	public static function loadConfig($file)
	{
		if (file_exists($file) && include_once($file))
		{
			return $config;
		}
		else
		{
			throw new Exception('File ' . $file . ' could not be loaded as configuration');
		}
	}
	
	public function offsetGet($offset)
	{
		if (isset($this->vars[$offset]))
		{
			if (is_array($this->vars[$offset]))
			{
				return new config($this->vars[$offset], $this->dir . $offset . DIRECTORY_SEPARATOR);
			}
			else
			{
				return $this->vars[$offset];
			}
		}
		else
		{
			$this->checkOffset($offset);
			$this->vars[$offset] = self::loadConfig($this->dir . $offset . '.php');
			return $this->offsetGet($offset);
		}
	}
	
	public function offsetSet($offset, $value)
	{
		throw new Exception('Config may not be changed');
	}

	public function offsetUnset($offset)
	{
		throw new Exception('Config may not be changed');
	}
}

?>
