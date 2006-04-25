<?php
/**
 * $Id$
 *
 * Author: Andreas Korthaus, akorthaus@web.de
 * Enhancements/Bugfixes: David Danier, david.danier@team23.de
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
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

class cpu extends source implements source_cached, source_rrd
{
	private $path_stat;
	
	private $fieldList = array(
		'user',
		'nice',
		'system',
		'idle',
		'iowait',
		'irq',
		'softirq'
	);
	
	private $stats;
	private $oldstats;
	
	public function __construct($path_stat = '/proc/stat')
	{
		$this->path_stat = $path_stat;
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
	
	public function fetchValues()
	{
		$returnValues = array();
		foreach ($this->stats as $cpu => $values)
		{
			$cpuUsage = array();
			foreach ($values as $key => $value)
			{
				if (isset($this->oldstats[$cpu][$key]))
				{
					$cpuUsage[$key] = $value - $this->oldstats[$cpu][$key];
				}
				else
				{
					$cpuUsage[$key] = 0;
				}
			}
			$sumUsage = array_sum($cpuUsage);
			if ($sumUsage > 0)
			{
				$factor = (100 / $sumUsage);
				foreach ($cpuUsage as $key => $value)
				{
					$returnValues[$cpu . '_' . $key] = $value * $factor;
				}
			}
		}
		return $returnValues;
	}
	
	private function getStats()
	{
		if (isset($this->stats))
		{
			return;
		}
		
		if (($lines = @file($this->path_stat)) === false)
		{
			throw new Exception('Could not read "' . $this->path_stat . '"');
		}
		
		$this->stats = array();
		foreach ($lines as $line)
		{
			if (preg_match('/^(cpu[0-9]*)((\s+([0-9]+))+)\s*$/i', $line, $parts))
			{
				$this->stats[$parts[1]] = array();
				$cpustats = preg_split('/\s+/', trim($parts[2]));
				$i = 0;
				foreach ($this->fieldList as $fieldName)
				{
					if (!isset($cpustats[$i]))
					{
						break;
					}
					$this->stats[$parts[1]][$fieldName] = $cpustats[$i];
					$i++;
				}
			}
		}
	}
	
	public function initCache()
	{
		$this->getStats();
		$this->oldstats = $this->stats;
	}
	
	public function loadCache($cachedata)
	{
		$this->oldstats = $cachedata['stats'];
	}
	
	public function getCache()
	{
		return array(
			'stats' => $this->stats,
		);
	}
}

?>
