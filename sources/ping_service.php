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

class ping_service extends source
{
	private $host;
	private $port;
	private $command;
	private $timeout;
	
	private $ping_time;
	
	public function __construct($host, $port, $command = null, $timeout = 1)
	{
		$this->host = $host;
		$this->port = $port;
		$this->command = $command;
		$this->timeout = $timeout;
	}
	
	public function refreshData()
	{
		$start = microtime(true);
		$socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
		if (!is_resource($socket))
		{
			// don't log time, use default-value (undefined)
			$this->ping_time = 'U';
			return;
		}
		socket_set_timeout($socket, $this->timeout);
		if (isset($this->command))
		{
			socket_set_blocking($socket, true); // just to be sure
			if (feof($socket))
			{
				throw new Exception('Connection closed while sending command');
			}
			fwrite($socket, $this->command);
			fgetc($socket); // read one char
		}
		fclose($socket);
		$this->ping_time = microtime(true) - $start;
	}
	
	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('time', 'GAUGE', null, 0);
	}
	
	public function updateRRD(rrd $rrd)
	{
		$rrd->setValue('time', $this->ping_time);
	}
	
	static public function httpCommand($host, $path = '/')
	{
		return "GET $path HTTP/1.1\r\nHost: $host\r\n\r\n";
	}
}

?>
