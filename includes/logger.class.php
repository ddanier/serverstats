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

// Layout for all loggers
abstract class logger
{
	const ALL = 0;
	const INFO = 1;
	const WARN = 2;
	const ERR = 3;
	const NONE = 99;
	
	static public function levelToString($loglevel)
	{
		switch ($loglevel)
		{
			case self::INFO:
				return lang::t('Information');
			case self::WARN:
				return lang::t('Warning');
			case self::ERR:
				return lang::t('Error');
			default:
				return lang::t('Unknown error');
		}
	}
	
	static protected function needsLogging($loglevel)
	{
		global $config;
		return ($loglevel >= $config['log']['level']);
	}
	
	abstract public function logString($loglevel, $string);
	abstract public function logException($loglevel, Exception $exception);
}

?>
