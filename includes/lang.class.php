<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 */

class lang
{
	static private $isLoaded = false;
	static private $translations = array();
	
	static public function load()
	{
		global $config;
		if (!isset($config) || !isset($config['language']))
		{
			self::$isLoaded = true;
			return;
		}
		$langfile = LANGPATH . $config['language'] . '.php';
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
