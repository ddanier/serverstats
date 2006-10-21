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
                                                  
class ping extends source implements source_rrd
{
	private $host;
	private $ping_time;
	
	private $ping_exec;
	private $ping_opts;
	
	public function __construct($host, $ping_opts = '-c 1 -W 1', $ping_exec = '/bin/ping')
	{
		$this->host = $host;
		$this->ping_exec = $ping_exec;
		$this->ping_opts = $ping_opts;
	}
	
	public function refreshData()
	{
		$opts = preg_split('/\s+/', $this->ping_opts);
		$opt_string = '';
		foreach ($opts as $opt)
		{
			$opt_string .= ' ' . escapeshellarg($opt);
		}
		$command = escapeshellcmd($this->ping_exec) . $opt_string . ' ' . escapeshellarg($this->host);
		$output = array();
		$return = 0;
		exec($command . ' 2>&1', $output, $return);
		if ($return != 0)
		{
			throw new Exception('rrdtool ("' . $command . '") finished with exitcode ' . $return . "\n" . implode("\n", $output));
		}
		foreach ($output as $line)
		{
			$matches = array();
			if (preg_match('/^.*icmp_seq=.+ttl=.+time=([0-9\.]+) ms$/', $line, $matches))
			{
				$this->ping_time = $matches[1];
				break;
			}
		}
	}
	
	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('time', 'GAUGE', null, 0);
	}
	
	public function fetchValues()
	{
		$values = array();
		$values['time'] = $this->ping_time;
		return $values;
	}
}

?>
