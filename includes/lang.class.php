<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
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

class lang
{
	static private $isLoaded = false;
	static private $translations = array();
	
	static public function load()
	{
		global $config;
		if (!isset($config) || !isset($config['main']['language']))
		{
			self::$isLoaded = true;
			return;
		}
		$langfile = LANGPATH . $config['main']['language'] . '.php';
		if (!file_exists($langfile))
		{
			self::$isLoaded = true;
			return;
		}
		include_once($langfile);
		if (isset($lang))
		{
			self::$translations = $lang;
		}
		self::$isLoaded = true;
	}
	
	static public function translate($search)
	{
		if (!self::$isLoaded)
		{
			self::load();
		}
		if (isset(self::$translations[$search]))
		{
			return self::$translations[$search];
		}
		else
		{
			return $search;
		}
	}
	
	static public function t($search)
	{
		return self::translate($search);
	}
}

?>
