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

class rrd
{
	private $rrdtoolbin;
	private $rrdfile;
	private $created = false;
	
	private $step = 60;
	private $datasources = array();
	private $archives = array();
	private $values = array();
	
	public function __construct($rrdtoolbin, $rrdfile)
	{
		$this->rrdtoolbin = $rrdtoolbin;
		$this->rrdfile = $rrdfile;
	}
	
	public function initOptions($temp)
	{
		$this->step = $temp['step'];
		$this->datasources = $temp['datasources'];
		$this->archives = $temp['archives'];
		foreach ($this->datasources as $name => $ds)
		{
			// Backwards compability
			if (isset($ds['name']))
			{
				$name = $ds['name'];
			}
			$this->values[$name] = 'U';
		}
		if (!file_exists($this->rrdfile))
		{
			$this->create();
		}
		else
		{
			$this->created = true;
		}
	}
	
	public function getOptions()
	{
		$temp = array();
		$temp['step'] = $this->step;
		$temp['datasources'] = $this->datasources;
		$temp['archives'] = $this->archives;
		return $temp;
	}

	public function create()
	{
		$params = ' create ' . escapeshellarg($this->rrdfile);
		$params .= ' -s ' . escapeshellarg($this->step);
		foreach ($this->datasources as $name => $ds)
		{
			// Backwards compability
			if (isset($ds['name']))
			{
				$name = $ds['name'];
			}
			$dsstring = 'DS:' . $name . ':' . $ds['type'] . ':' . $ds['heartbeat'] . ':' . $ds['min'] . ':' . $ds['max'];
			$params .= ' ' . escapeshellarg($dsstring);
		}
		foreach ($this->archives as $rra)
		{
			$rrastring = 'RRA:' . $rra['cf'] . ':' . $rra['xff'] . ':' . $rra['steps'] . ':' . $rra['rows'];
			$params .= ' ' . escapeshellarg($rrastring);
		}
		system(escapeshellcmd($this->rrdtoolbin) . $params);
		$this->created = true;
	}

	public function addDatasource($name, $type = 'GAUGE', $heartbeat = null, $min = 'U', $max = 'U')
	{
		if ($this->created)
		{
			throw new Exception('RRD is created, you cannot add any datasources');
		}
		if (isset($this->datasources[$name]))
		{
			throw new Exception('Datasourcename "' . $name .'" already taken');
		}
		if (!isset($heartbeat))
		{
			$heartbeat = $this->getDefaultHeartbeat();
		}
		$this->datasources[$name] = array(
			'min' => $min,
			'max' => $max,
			'type' => $type,
			'heartbeat' => $heartbeat
		);
		$this->values[$name] = 'U';
	}

	public function addArchive($cf, $xff, $steps, $rows)
	{
		if ($this->created)
		{
			throw new Exception('RRD is created, you cannot add any archives');
		}
		$this->archives[] = array(
			'steps' => $steps,
			'rows' => $rows,
			'cf' => $cf,
			'xff' => $xff
		);
	}

	public function setValue($dsname, $value)
	{
		if (!isset($this->values[$dsname]))
		{
			throw new Exception('Datasource unknown');
		}
		if ($this->values[$dsname] != 'U')
		{
			throw new Exception('Datasource already set');
		}
		if ($value == null)
		{
			// No need to update
			return;
		}
		$this->values[$dsname] = $value;
	}

	public function update()
	{
		$params = ' update ' . escapeshellarg($this->rrdfile);
		$updatestr = 'N';
		$templatestr = '';
		foreach ($this->values as $dsname => $dsvalue)
		{
			$templatestr .= $dsname . ':';
			$updatestr .= ':' . $dsvalue;
		}
		$templatestr = substr($templatestr, 0, -1);
		$params .= ' -t  ' . escapeshellarg($templatestr);
		$params .= ' ' . escapeshellarg($updatestr);
		system(escapeshellcmd($this->rrdtoolbin) . $params);

	}

	public function hasDatasource($name)
	{
		return isset($this->datasources[$name]);
	}
	
	public function getDatasources()
	{
		return $this->datasources;
	}

	public function getArchives()
	{
		return $this->archives;
	}

	public function getRRDFile()
	{
		return $this->rrdfile;
	}
	
	public function getRRDToolBin()
	{
		return $this->rrdtoolbin;
	}

	public function setStep($step)
	{
		$this->step = $step;
	}

	public function getStep()
	{
		return $this->step;
	}

	public function getDefaultHeartbeat()
	{
		return $this->step * 4;
	}
}

?>
