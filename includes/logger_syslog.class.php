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

class logger_syslog extends logger
{
	public function __construct($ident = 'Serverstats', $options = null, $facility = null)
	{
		define_syslog_variables();
		if (!isset($options))
		{
			$options = LOG_ODELAY || LOG_PID;
		}
		if (!isset($facility))
		{
			$facility = LOG_USER;
		}
		openlog($ident, $options, $facility);
	}
	
	public function __destruct()
	{
		// closelog();
		/*
		"The use of closelog() is optional."
		Using closelog() I get this error here:
		*** glibc detected *** double free or corruption (fasttop): 0xADDRESS ***
		Abgebrochen
		*/
	}
	
	static private function levelToSyslogLevel($loglevel)
	{
		switch ($loglevel)
		{
			case self::INFO:
				return LOG_INFO;
			case self::WARN:
				return LOG_WARNING;
			case self::ERR:
				return LOG_ERR;
			default:
				/* Unknown Error, should be logged critical */
				return LOG_CRIT;
		}
	}
	
	public function logString($loglevel, $string)
	{
		if (!logger::needsLogging($loglevel)) return;
		syslog(self::levelToSyslogLevel($loglevel), $string);
	}
	
	public function logException($loglevel, Exception $exception)
	{
		if (!logger::needsLogging($loglevel)) return;
		syslog(self::levelToSyslogLevel($loglevel), $exception->__toString());
	}
}

?>
