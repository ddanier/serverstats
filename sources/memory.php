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

class memory extends source implements source_rrd
{
	private $meminfofile;
	private $data = array();
	private $show = array();
	
	public function __construct($show = null, $meminfofile = '/proc/meminfo')
	{
		$this->meminfofile = $meminfofile;
		if (!isset($show))
		{
			$show = array(
				'MemTotal',
				'MemFree',
				'Cached',
				'SwapCached',
				'SwapTotal',
				'SwapFree'
			);
		}
		if (!is_array($show))
		{
			$show = array($show);
		}
		$this->show = $show;
	}
	
	public function refreshData()
	{
		if (($temp = @file($this->meminfofile)) === false)
		{
			throw new Exception('Could not read "' . $this->meminfofile . '"');
		}
		foreach($temp as &$row)
		{
			if (preg_match('/^([a-zA-Z0-9_]{1,19})\s*:\s*([0-9\.]+)(\s*(.+))?$/', $row, $split))
			{
				if (isset($split[4]))
				{
					switch (strtolower($split[4]))
					{
						// without break!
						case 'gb':
							$split[2] *= 1024;
						case 'mb':
							$split[2] *= 1024;
						case 'kb':
							$split[2] *= 1024;
					}
				}
				$this->data[$split[1]] = $split[2];
			}
		}
	}
	
	public function initRRD(rrd $rrd)
	{
		foreach ($this->data as $name => $value)
		{
			if (in_array($name, $this->show))
			{
				$rrd->addDatasource($name, 'GAUGE', null, 0);
			}
		}
	}
	
	public function fetchValues()
	{
		$values = array();
		foreach ($this->data as $name => $value)
		{
			if (in_array($name, $this->show))
			{
				$values[$name] = $value;
			}
		}
		return $values;
	}
}

?>
