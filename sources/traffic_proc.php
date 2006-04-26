<?php
/**
 * Author: Michael Richter, mr@osor.de
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 *
 * Copyright (C) 2005 Michael Richter
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

class traffic_proc extends source implements source_rrd
{
	private $ifs;
	private $data;
	private $procfile;
	
	public function __construct($ifs = 'eth0', $procfile = '/proc/net/dev')
	{
		if (is_string($ifs))
		{
			$ifs = array($ifs);
		}
		if (!is_array($ifs))
		{
			throw new Exception('Parameter must be an array');
		}
		$this->ifs = $ifs;
		$this->data = array();
		$this->procfile = $procfile;
	}
	
	public function refreshData()
	{
		if (($lines = @file($this->procfile)) === false)
		{
			throw new Exception('Could not read ' . $this->procfile);
		}
		foreach($lines as $line)
		{
			if (preg_match('/^\s*([\w\d\.\-]+):\s*(\d+)\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+/', $line, $m))
			{
				if (in_array($m[1], $this->ifs))
				{
					$this->data[$m[1]] = array(
						'rbytes'   => $m[2],
						'rpackets' => $m[3],
						'tbytes'   => $m[4],
						'tpackets' => $m[5]
					);
				}
			}
		}
	}
	
	public function initRRD(rrd $rrd)
	{
		foreach ($this->ifs as $if)
		{
			$rrd->addDatasource($if . '_rbytes', 'COUNTER', null, 0, 4294967295);
			$rrd->addDatasource($if . '_rpackets', 'COUNTER', null, 0, 4294967295);
			$rrd->addDatasource($if . '_tbytes', 'COUNTER', null, 0, 4294967295);
			$rrd->addDatasource($if . '_tpackets', 'COUNTER', null, 0, 4294967295);
		}
	}
	
	public function fetchValues()
	{
		$values = array();
		foreach ($this->ifs as $if)
		{
			if (isset($this->data[$if]))
			{
				$values[$if . '_rbytes'] = $this->data[$if]['rbytes'];
				$values[$if . '_rpackets'] = $this->data[$if]['rpackets'];
				$values[$if . '_tbytes'] = $this->data[$if]['tbytes'];
				$values[$if . '_tpackets'] = $this->data[$if]['tpackets'];
			}
		}
		return $values;
	}
}

?>
