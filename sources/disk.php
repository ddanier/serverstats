<?php
/**
 * $Id: cpu.php 92 2005-07-15 11:00:26Z ddanier $
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

die('This is not ready!');
class disk extends source
{
	private $path_stat;
	private $filter_disk;
	private $filter_part;
	
	private $stats_disk;
	private $stats_part;
	private $time;
	
	private $oldstats_disk;
	private $oldstats_part;
	private $oldtime;
	
	public function __construct($path_stat = '/proc/diskstats', $filter_disk = null, $filter_part = null)
	{
		$this->path_stat = $path_stat;
		if (isset($filter_disk))
		{
			if (is_array($filter_disk))
			{
				$this->filter_disk = $filter_disk;
			}
			else
			{
				$this->filter_disk = array($filter_disk);
			}
		}
		else
		{
			$this->filter_disk = null;
		}
		if (isset($filter_part))
		{
			if (is_array($filter_part))
			{
				$this->filter_part = $filter_part;
			}
			else
			{
				$this->filter_part = array($filter_part);
			}
		}
		else
		{
			$this->filter_part = null;
		}
	}
	
	public function refreshData()
	{
		$this->getStats();
	}
	
	public function initRRD(rrd $rrd)
	{
		$this->getStats();
		foreach ($this->stats_disk as $disk => $values)
		{
			foreach ($values as $key => $value)
			{
				$rrd->addDatasource($disk . '_' . $key, 'GAUGE', null, 0);
			}
		}
		foreach ($this->stats_part as $part => $values)
		{
			foreach ($values as $key => $value)
			{
				$rrd->addDatasource($part . '_' . $key, 'GAUGE', null, 0);
			}
		}
	}
	
	public function updateRRD(rrd $rrd)
	{
		foreach ($this->stats_disk as $disk => $values)
		{
			$sumProc = array_sum($values) - array_sum($this->oldstats[$cpu]);
			if ($sumProc > 0)
			{
				foreach ($values as $key => $value)
				{
					if ($sumProc == 0)
					{
						$rrd->setValue($disk . '_' . $key, 0);
					}
					else
					{
						$rrd->setValue($disk . '_' . $key, (($value - $this->oldstats[$cpu][$key]) * 100) / $sumProc);
					}
				}
			}
		}
		foreach ($this->stats_part as $part => $values)
		{
			$sumProc = array_sum($values) - array_sum($this->oldstats[$cpu]);
			if ($sumProc > 0)
			{
				foreach ($values as $key => $value)
				{
					if ($sumProc == 0)
					{
						$rrd->setValue($part . '_' . $key, 0);
					}
					else
					{
						$rrd->setValue($part . '_' . $key, (($value - $this->oldstats[$cpu][$key]) * 100) / $sumProc);
					}
				}
			}
		}
	}
	
	private function getStats()
	{
		if (isset($this->stats_disk) && isset($this->filter_part))
		{
			return;
		}
		
		if (!($lines = file($this->path_stat)))
		{
			throw new Exception('Could not read "' . $this->path_stat . '"');
		}
		
		$this->stats_disk = array();
		$this->filter_part = array();
		foreach ($lines as $line)
		{
			if (preg_match('/^([a-z]+[0-9]*)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)/i', $line, $parts))
			{
				if (isset($this->filter_part))
				{
					$use = false;
					foreach ($this->filter_part as $test)
					{
						if (preg_match($test, $parts[1]))
						{
							$use = true;
							break;
						}
					}
					if (!$use)
					{
						continue;
					}
					$this->stats_part[$parts[1]] = array(
						'num_reads' => $parts[2],
						'merged_reads' => $parts[3],
						'sectors_read' => $parts[4],
						'ms_read' => $parts[5],
						'num_writes' => $parts[6],
						'merged_writes' => $parts[7],
						'sectors_written' => $parts[8],
						'ms_write' => $parts[9],
						'cur_io_procs' => $parts[10],
						'ms_io' => $parts[11],
						'idle' => $parts[12]
					);
				}
			}
			elseif (preg_match('/^([a-z]+[0-9]*)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)/i', $line, $parts))
			{
				if (isset($this->filter_disk))
				{
					$use = false;
					foreach ($this->filter_disk as $test)
					{
						if (preg_match($test, $parts[1]))
						{
							$use = true;
							break;
						}
					}
					if (!$use)
					{
						continue;
					}
				}
				$this->stats_disk[$parts[1]] = array(
					'num_reads' => $parts[2],
					'sectors_read' => $parts[3],
					'num_writes' => $parts[4],
					'sectors_written' => $parts[5]
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
		$this->oldstats_disk = $this->stats_disk;
		$this->oldstats_part = $this->stats_part;
		$this->oldtime = 0;
	}
	
	public function loadCache($cachedata)
	{
		$this->oldstats_disk = $cachedata['stats_disk'];
		$this->oldstats_part = $cachedata['stats_part'];
		$this->oldtime = $cachedata['time'];
	}
	
	public function getCache()
	{
		return array(
			'stats_disk' => $this->stats_disk,
			'stats_part' => $this->stats_part,
			'time' => $this->time
		);
	}
}

?>
