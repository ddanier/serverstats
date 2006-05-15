<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: LGPL v2.1 or later (http://www.gnu.org/licenses/lgpl.html)
 *
 * Copyright (C) 2005 David Danier
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

class config implements ArrayAccess, IteratorAggregate
{
	protected $dir;
	protected $vars = array();
	protected $rootConfig;
	
	public function __construct($dir, $rootConfig = null)
	{
		$this->dir = $dir;
		if (isset($rootConfig))
		{
			if ($rootConfig instanceof config)
			{
				$this->rootConfig = $rootConfig;
			}
			else
			{
				throw new Exception('Config: Param error');
			}
		}
		else
		{
			$this->rootConfig = $this;
		}
	}
	
	private function isOffsetValid($offset)
	{
		return preg_match('/^[a-zA-Z0-9_]+$/', $offset);
	}
	
	private function checkOffset($offset)
	{
		if (!$this->isOffsetValid($offset))
		{
			throw new Exception('Offset "' . $offset . '" is not allowed');
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
			if (is_readable($this->dir . $offset . '.php') || is_dir($this->dir . $offset))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	protected static function loadConfig(&$config, $file, $rootConfig)
	{
		if (require_once($file))
		{
			if (isset($config))
			{
				return $config;
			}
			else
			{
				throw new Exception('Error while loading "' . $file . '" (Not a valid config?)');
			}
		}
		else
		{
			throw new Exception('File "' . $file . '" could not be loaded');
		}
	}
	
	protected function loadOffset($offset)
	{
		$this->checkOffset($offset);
		if (is_readable($this->dir . $offset . '.php'))
		{
			$this->vars[$offset] = array();
			self::loadConfig($this->vars[$offset], $this->dir . $offset . '.php', $this->rootConfig);
		}
		elseif (is_dir($this->dir . $offset))
		{
			$this->vars[$offset] = array();
		}
		else
		{
			throw new Exception('Offset "' . $offset . '" does not exist');
		}
		return $this->offsetGet($offset);
	}
	
	public function offsetGet($offset)
	{
		if (!isset($this->vars[$offset]))
		{
			$this->loadOffset($offset);
		}
		if (is_array($this->vars[$offset]))
		{
			$subconfig = new
				config(
					$this->dir . $offset . DIRECTORY_SEPARATOR,
					$this->rootConfig);
			$subconfig->vars = &$this->vars[$offset];
			return $subconfig;
		}
		else
		{
			return $this->vars[$offset];
		}
	}
	
	public function getIterator()
	{
		if (is_dir($this->dir))
		{
			$files = scandir($this->dir);
			foreach ($files as $file)
			{
				if (preg_match('/^([a-zA-Z0-9_]+)(\.php)?$/', $file, $parts))
				{
					$offset = $parts[1];
					if (!isset($this->vars[$offset]))
					{
						$this->loadOffset($offset);
					}
				}
			}
		}
		$iteratorVars = array();
		foreach (array_keys($this->vars) as $offset)
		{
			$iteratorVars[$offset] = $this->offsetGet($offset);
		}
		return new ArrayIterator($iteratorVars);
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
