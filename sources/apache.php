<?php
/**
 * $Id$
 *
 * Author: Andreas Korthaus, akorthaus@web.de
 * Enhancements: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
 *
 * Copyright (C) 2005 Andreas Korthaus
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
 *
 *
 * Requirements:
 *
 * In order to use this source you have to configure Apache/mod_status 
 * like that:
 *
 * <Location /server-status>
 *     SetHandler server-status
 *     Order deny,allow
 *     Deny from all
 *     Allow from 127.0.0.1
 * </Location>
 * ExtendedStatus On
 */

class apache extends source
{
	private $url_serverstatus;
	private $show;
	
	private $data;
	
	public function __construct($url_serverstatus = 'http://localhost/server-status?auto', $show = null, $psshow = null)
	{
		// What lines should be saved
		// (and how to call them)
		if (!isset($show))
		{
			$this->show = array(
				'Total Accesses' => 'requests',
				'Total kBytes' => 'kilobytes',
				'BytesPerReq' => 'bytesperreq',
				'CPULoad' => 'cpuload',
				'BusyWorkers' => 'busyprocs',
				'IdleWorkers' => 'idleprocs'
			);
		}
		// What values should be saved as per second fields
		// (and the changed name)
		if (!isset($psshow))
		{
			$this->psshow = array(
				'requests' => 'requestsps',
				'kilobytes' => 'kilobytesps'
			);
		}
		$this->url_serverstatus = $url_serverstatus;
	}
	
	public function refreshData()
	{
		if (!($lines = @file($this->url_serverstatus)))
		{
			throw new Exception('Error while reading Apache-status');
			return;
		}
		foreach ($lines as $line)
		{
			foreach ($this->show as $apachename => $dsname)
			{
				$apachename = quotemeta($apachename);
				if (preg_match('/^' . $apachename . '\s*:\s*(.*)$/', $line, $parts))
				{
					$this->data[$dsname] = $parts[1];
					break;
				}
			}
		}
	}
	
	public function initRRD(rrd $rrd)
	{
		foreach ($this->show as $dsname)
		{
			$rrd->addDatasource($dsname, 'GAUGE');
			if (isset($this->psshow[$dsname]))
			{
				$rrd->addDatasource($this->psshow[$dsname], 'DERIVE', null, 0);
			}
		}
	}
	
	public function updateRRD(rrd $rrd)
	{
		foreach ($this->data as $dsname => $value)
		{
			$rrd->setValue($dsname, $value);
			if (isset($this->psshow[$dsname]))
			{
				$rrd->setValue($this->psshow[$dsname], $value);
			}
		}
	}
}

?>