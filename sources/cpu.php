<?php
/**
 * $Id$
 *
 * Author: Andreas Korthaus, akorthaus@web.de
 * Enhancements/Bugfixes: David Danier, david.danier@team23.de
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
 */

class cpu extends source
{
	private $path_stat;
	private $user_hz;
	
	private $stats;
	private $time;
	
	private $oldstats;
	private $oldtime;
	
	public function __construct($path_stat = '/proc/stat', $user_hz = 100)
	{
		$this->path_stat = $path_stat;
		$this->user_hz = $user_hz;
	}
	
	public function refreshData()
	{
		$this->getStats();
	}
	
	public function initRRD(rrd $rrd)
	{
		$this->getStats();
		foreach ($this->stats as $cpu => $values)
		{
			foreach ($values as $key => $value)
			{
				$rrd->addDatasource($cpu . '_' . $key, 'GAUGE', null, 0, 100);
			}
		}
	}
	
	public function updateRRD(rrd $rrd)
	{
		foreach ($this->stats as $cpu => $values)
		{
			$sumProc = array_sum($values) - array_sum($this->oldstats[$cpu]);
			if ($sumProc > 0)
			{
				foreach ($values as $key => $value)
				{
					if ($sumProc == 0)
					{
						$rrd->setValue($cpu . '_' . $key, 0);
					}
					else
					{
						$rrd->setValue($cpu . '_' . $key, (($value - $this->oldstats[$cpu][$key]) * $this->user_hz) / $sumProc);
					}
				}
			}
		}
	}
	
	private function getStats()
	{
		if (isset($this->stats))
		{
			return;
		}
		
		if (!($lines = file($this->path_stat)))
		{
			throw new Exception('Could not read "' . $this->path_stat . '"');
		}
		$this->stats = array();
		
		foreach ($lines as $line)
		{
			if (preg_match('/^(cpu[0-9]*)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)/i', $line, $parts))
			{
				$this->stats[$parts[1]] = array(
					'user' => $parts[2],
					'nice' => $parts[3],
					'system' => $parts[4],
					'idle' => $parts[5]
				);
			}
		}
		
		$this->time = microtime(true);
	}
	
	public function useCache()
	{
		return true;
	}
	
	public function initCache()
	{
		$this->getStats();
		$this->oldstats = $this->stats;
		$this->oldtime = 0;
	}
	
	public function loadCache($cachedata)
	{
		$this->oldstats = $cachedata['stats'];
		$this->oldtime = $cachedata['time'];
	}
	
	public function getCache()
	{
		return array(
			'stats' => $this->stats,
			'time' => $this->time
		);
	}
}

?>
