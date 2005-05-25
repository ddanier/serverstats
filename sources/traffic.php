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

class traffic extends source
{
	private $logdir;
	private $chain;
	private $cache;

	private $traffic;
	
	public function __construct($chain, $logdir = null)
	{
		if (!isset($logdir))
		{
			$logdir = SOURCEPATH . 'traffic';
		}
		$this->logdir = $logdir;
		$this->chain = $chain;
	}

	public function refreshData()
	{
		if (!($traffic = @file_get_contents($this->logdir . DIRECTORY_SEPARATOR . $this->chain)))
		{
			throw new Exception('Could not get current data');
		}
		else
		{
			$traffic = trim($traffic);
		}
		$this->traffic = $traffic;
	}

	public function initRRD(rrd $rrd)
	{
		$rrd->addDatasource('traffic', 'GAUGE', null, 0);
		$rrd->addDatasource('bps', 'DERIVE', null, 0);
	}

	public function updateRRD(rrd $rrd)
	{
		$rrd->setValue('traffic', $this->traffic);
		$rrd->setValue('bps', $this->traffic);
	}
}

?>
