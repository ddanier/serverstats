<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 *
 * Copyright (C) 2005 David Danier
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class config implements ArrayAccess, IteratorAggregate
{
	private $vars;
	private $dir;
	static public $loaded = array();

	public function __construct(&$vars, $dir)
	{
		$this->vars = &$vars;
		$this->dir = $dir;
	}
	
	private function checkOffset($offset)
	{
		if (!preg_match('/[a-z0-9]+/i', $offset))
		{
			throw new Exception('Offsetname "' . $offset . '" is not allowed for external files');
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
			self::$loaded[] = $file;
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
	
	public function getIterator()
	{
		return new ArrayIterator($this->vars);
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
