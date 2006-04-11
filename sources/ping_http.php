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

class ping_http extends source implements source_rrd
{
	private $host;
	private $port;
	private $path;
	private $command;
	private $timeout;
	
	private $ping_time;
	
	public function __construct($host, $port = 80, $path = '/', $command = 'HEAD', $timeout = 1)
	{
		$this->host = $host;
		$this->port = $port;
		$this->path = $path;
		$this->command = $command;
		$this->timeout = $timeout;
	}
	
	public function refreshData()
	{
		$timer = array();
		$timer['start'] = microtime(true);
		$socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
		if (!is_resource($socket))
		{
			throw new Exception('Could not open connection');
		}
		$timer['open'] = microtime(true);
		socket_set_timeout($socket, $this->timeout);
		socket_set_blocking($socket, true); // just to be sure
		if (feof($socket))
		{
			throw new Exception('Connection closed while sending command');
		}
		$http_request = $this->command . ' ' . $this->path . " HTTP/1.1\r\n" . 
			'Host: ' . $this->host . "\r\n" . 
			"User-Agent: Serverstats::ping_http\r\n" . 
			"Connection: close\r\n\r\n";
		fputs($socket, $http_request);
		$timer['send'] = microtime(true);
		while(!feof($socket))
		{
			if (fread($socket, 4096) === false)
			{
				throw new Exception('Communication error, could not fetch result');
			}
		}
		$timer['receive'] = microtime(true);
		fclose($socket);
		$timer['finish'] = microtime(true);
		$this->ping_time = array(
				'time' => $timer['finish'] - $timer['start'],
				'open' => $timer['open'] - $timer['start'],
				'send' => $timer['send'] - $timer['open'],
				'receive' => $timer['receive'] - $timer['send'],
				'close' => $timer['finish'] - $timer['receive']
			);
	}
	
	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('time', 'GAUGE', null, 0);
		$rrd->addDatasource('open', 'GAUGE', null, 0);
		$rrd->addDatasource('send', 'GAUGE', null, 0);
		$rrd->addDatasource('receive', 'GAUGE', null, 0);
		$rrd->addDatasource('close', 'GAUGE', null, 0);
	}
	
	public function fetchValues()
	{
		return $this->ping_time;
	}
}

?>
