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

class ping extends source
{
	private $hosts;
	private $data = array();
	
	private $ping_exec;
	private $ping_opts;
	
	public function __construct($hosts, $ping_exec = '/bin/ping', $ping_opts = '-c 1')
	{
		if (is_string($hosts))
		{
			$hosts = array($hosts);
		}
		if (!is_array($hosts))
		{
			throw new Exception('$hosts not an array');
		}
		$this->hosts = $hosts;
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
		foreach ($this->hosts as $host)
		{
			$command = escapeshellcmd($this->ping_exec) . $opt_string . ' ' . $host;
			exec($command . ' 2>&1', $output, $return);
			if ($return != 0)
			{
				throw new Exception('rrdtool ("' . $command . '") finished with exitcode ' . $return . "\n" . implode("\n", $output));
			}
			foreach ($output as $line)
			{
				if (preg_match('/^.*icmp_seq=.+ttl=.+time=([0-9\.]+) ms$/', $line, $matches))
				{
					$this->data[$host] = $matches[1];
					break;
				}
			}
		}
	}
	
	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('read', 'GAUGE', null, 0);
	}
	
	public function updateRRD(rrd $rrd)
	{
		$rrd->setValue('read', $this->stats_disk['sectors_read'] * $this->sector_size);
	}
}

?>
