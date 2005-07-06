<?php
/**
 * Author: Andreas Korthaus, akorthaus@web.de
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
 *     allow from 127.0.0.1
 * </Location>
 * ExtendedStatus On
 */

class apache extends source
{
	private $url_serverstatus;
	private $lines;
	private $requests;
	private $kbytes;
	private $serv_busy;
	private $serv_idle;

	public function __construct($url_serverstatus = 'http://localhost/server-status?auto')
	{
		$this->url_serverstatus = $url_serverstatus;
	}

	public function refreshData()
	{
		$lines = file($this->url_serverstatus);
		$this->requests = trim(substr(strrchr($lines[0], ':'), 1));
		$this->kbytes = trim(substr(strrchr($lines[1], ':'), 1));
		$this->uptime = trim(substr(strrchr($lines[3], ':'), 1));
		$this->serv_busy = trim(substr(strrchr($lines[7], ':'), 1));
		$this->serv_idle = trim(substr(strrchr($lines[8], ':'), 1));
	}

	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('requests', 'DERIVE', null, 0);
		$rrd->addDatasource('kbytes', 'DERIVE', null, 0);
		$rrd->addDatasource('uptime', 'GAUGE', null, 0);
		$rrd->addDatasource('serv_busy', 'GAUGE', null, 0);
		$rrd->addDatasource('serv_idle', 'GAUGE', null, 0);
	}

	public function updateRRD(rrd $rrd)
	{
		$rrd->setValue('requests', $this->requests);
		$rrd->setValue('kbytes', $this->kbytes);
		$rrd->setValue('uptime', $this->uptime);
		$rrd->setValue('serv_busy', $this->serv_busy);
		$rrd->setValue('serv_idle', $this->serv_idle);
	}
}

?>
