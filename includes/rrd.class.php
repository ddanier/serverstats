<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: LGPL v2.1 or later (http://www.gnu.org/licenses/lgpl.html)
 *
 * Copyright (C) 2005 David Danier
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

class rrd
{
	private $rrdtoolbin;
	private $rrdfile;
	private $created = false;
	
	private $step = 300;
	private $datasources = array();
	private $archives = array();
	private $values = array();
	
	public function __construct($rrdtoolbin, $rrdfile)
	{
		$this->rrdtoolbin = $rrdtoolbin;
		$this->rrdfile = $rrdfile;
		if (file_exists($this->rrdfile))
		{
			$this->restoreOptions();
		}
	}
	
	private function restoreOptions()
	{
		$output = array();
		$return = 0;
		$params = ' info ' . escapeshellarg($this->rrdfile);
		$command = escapeshellcmd($this->rrdtoolbin) . $params;
		exec($command . ' 2>&1', $output, $return);
		if ($return != 0)
		{
			throw new Exception('rrdtool ("' . $command . '") finished with exitcode ' . $return . "\n" . implode("\n", $output));
		}
		$matches = array();
		foreach ($output as $line)
		{
			if (preg_match('/^(.*?)\s*=\s*(.*?)$/', $line, $matches))
			{
				$key = $matches[1];
				$value = $matches[2];
				if ($key == 'step')
				{
					$this->step = $value;
				}
				elseif (preg_match('/^ds\[([a-zA-Z0-9_]+)\]\.(.*?)$/', $key, $matches))
				{
					$dsname = $matches[1];
					$dsattribute = $matches[2];
					if (!isset($this->datasources[$dsname]))
					{
						$this->datasources[$dsname] = array();
					}
					switch ($dsattribute)
					{
						case 'type':
							$value = trim($value, '"');
							$this->datasources[$dsname]['type'] = $value;
							switch ($value)
							{
								case 'GAUGE':
								case 'COUNTER':
								case 'DERIVE':
								case 'ABSOLUTE':
									$this->values[$dsname] = 'U';
									break;
								case 'COMPUTE':
									break;
								default:
									throw new Exception('Unknown Datasource-type: ' . $value);
									break;
							}
							break;
						case 'cdef':
							$value = trim($value, '"');
							$this->datasources[$dsname]['expression'] = $value;
							break;
						case 'min':
							if ($value == 'NaN')
							{
								$this->datasources[$dsname]['min'] = 'U';
							}
							else
							{
								$this->datasources[$dsname]['min'] = floatval($value);
							}
							break;
						case 'max':
							if ($value == 'NaN')
							{
								$this->datasources[$dsname]['max'] = 'U';
							}
							else
							{
								$this->datasources[$dsname]['max'] = floatval($value);
							}
							break;
						case 'minimal_heartbeat':
							$this->datasources[$dsname]['heartbeat'] = intval($value);
							break;
					}
				}
				/* Not all values can be imported this way, but the
				   RRA-definition is not needed for rrdupdate,
				   so we may skip this */
				/*
				elseif (preg_match('/^rra\[([0-9]+)\]\.(.*?)$/', $key, $matches))
				{
					$rranum = $matches[1];
					$rraattribute = $matches[2];
					if (!isset($this->archives[$rranum]))
					{
						$this->archives[$rranum] = array();
					}
					switch ($rraattribute)
					{
						case 'cf':
							$value = trim($value, '"');
							$this->archives[$rranum]['cf'] = $value;
							break;
						case 'rows':
							$this->archives[$rranum]['rows'] = intval($value);
							break;
						case 'xff':
							$this->archives[$rranum]['xff'] = floatval($value);
							break;
						case 'pdp_per_row':
							$this->archives[$rranum]['steps'] = intval($value);
							break;
						case 'alpha':
							$this->archives[$rranum]['alpha'] = floatval($value);
							break;
						case 'beta':
							$this->archives[$rranum]['beta'] = floatval($value);
							break;
						case 'gamma':
							$this->archives[$rranum]['gamma'] = floatval($value);
							break;
						case 'failure_threshold':
							$this->archives[$rranum]['threshold'] = $value;
							break;
						case 'window_length':
							$this->archives[$rranum][''] = $value;
							break;
					}
				}
				*/
			}
		}
		$this->created = true;
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
			switch($ds['type'])
			{
				case 'GAUGE':
				case 'COUNTER':
				case 'DERIVE':
				case 'ABSOLUTE':
					// Params: heartbeat, min, max
					$dsstring = 'DS:' . $name . ':' . $ds['type'] . ':' . $ds['heartbeat'] . ':' . $ds['min'] . ':' . $ds['max'];
					break;
				case 'COMPUTE':
					$dsstring = 'DS:' . $name . ':' . $ds['type'] . ':' . $ds['expression'];
					break;
				default:
					throw new Exception('Unknown Datasource-type: ' . $type);
					break;
			}
			$params .= ' ' . escapeshellarg($dsstring);
		}
		foreach ($this->archives as $rra)
		{
			switch ($rra['cf'])
			{
				case 'AVERAGE':
				case 'MIN':
				case 'MAX':
				case 'LAST':
					// Params: xff, steps, rows
					$rrastring = 'RRA:' . $rra['cf'] . ':' . $rra['xff'] . ':' . $rra['steps'] . ':' . $rra['rows'];
					break;
				case 'HWPREDICT':
					// Params: rows, alpha, beta, seasonal_period, rra-num
					$rrastring = 'RRA:' . $rra['cf'] . ':' . $rra['rows'] . ':' . $rra['alpha'] . ':' . $rra['beta'] . ':' . $rra['seasonal_period'] . ':' . $rra['rra-num'];
					break;
				case 'SEASONAL':
				case 'DEVSEASONAL':
					// Params: seasonal_period, gamma, rra-num
					$rrastring = 'RRA:' . $rra['cf'] . ':' . $rra['seasonal_period'] . ':' . $rra['gamma'] . ':' . $rra['rra-num'];
					break;
				case 'DEVPREDICT':
					// Params: rows, rra-num
					$rrastring = 'RRA:' . $rra['cf'] . ':' . $rra['rows'] . ':' . $rra['rra-num'];
					break;
				case 'FAILURES':
					// Params: rows, threshold, window_length, rra-num
					$rrastring = 'RRA:' . $rra['cf'] . ':' . $rra['rows'] . ':' . $rra['threshold'] . ':' . $rra['window_length'] . ':' . $rra['rra-num'];
					break;
				default:
					throw new Exception('Unknown CF: ' . $cf);
					break;
			}
			$params .= ' ' . escapeshellarg($rrastring);
		}
		$output = array();
		$return = 0;
		$command = escapeshellcmd($this->rrdtoolbin) . $params;
		exec($command . ' 2>&1', $output, $return);
		if ($return != 0)
		{
			throw new Exception('rrdtool ("' . $command . '") finished with exitcode ' . $return . "\n" . implode("\n", $output));
		}
		$this->created = true;
	}
	
	public function addDatasource($name, $type, $p1 = null, $p2 = null, $p3 = null)
	{
		if ($this->created)
		{
			throw new Exception('RRD is created, you cannot add any datasources');
		}
		if (isset($this->datasources[$name]))
		{
			throw new Exception('Datasourcename "' . $name .'" already taken');
		}
		switch($type)
		{
			case 'GAUGE':
			case 'COUNTER':
			case 'DERIVE':
			case 'ABSOLUTE':
				// Params: heartbeat, min, max
				if (!isset($p1))
				{
					$p1 = $this->getDefaultHeartbeat();
				}
				if (!isset($p2))
				{
					$p2 = 'U';
				}
				if (!isset($p3))
				{
					$p3 = 'U';
				}
				$this->datasources[$name] = array(
					'min' => $p2,
					'max' => $p3,
					'type' => $type,
					'heartbeat' => $p1
				);
				$this->values[$name] = 'U';
				break;
			case 'COMPUTE':
				// Params: expression
				if (!isset($p1))
				{
					throw new Exception('Wrong Paramcount for DST ' . $type);
				}
				$this->datasources[$name] = array(
					'type' => $type,
					'expression' => $p1
				);
				break;
			default:
				throw new Exception('Unknown Datasource-type: ' . $type);
				break;
		}
	}
	
	public function addArchive($cf, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null)
	{
		if ($this->created)
		{
			throw new Exception('RRD is created, you cannot add any archives');
		}
		switch ($cf)
		{
			case 'AVERAGE':
			case 'MIN':
			case 'MAX':
			case 'LAST':
				// Params: xff, steps, rows
				if (!(isset($p1) && isset($p2) && isset($p3)))
				{
					throw new Exception('Wrong Paramcount for CF ' . $cf);
				}
				$this->archives[] = array(
					'cf' => $cf,
					'xff' => $p1,
					'steps' => $p2,
					'rows' => $p3
				);
				break;
			case 'HWPREDICT':
				// Params: rows, alpha, beta, seasonal_period, rra-num
				if (!(isset($p1) && isset($p2) && isset($p3) && isset($p4) && isset($p5)))
				{
					throw new Exception('Wrong Paramcount for CF ' . $cf);
				}
				$this->archives[] = array(
					'cf' => $cf,
					'rows' => $p1,
					'alpha' => $p2,
					'beta' => $p3,
					'seasonal_period' => $p4,
					'rra-num' => $p5
				);
				break;
			case 'SEASONAL':
			case 'DEVSEASONAL':
				// Params: seasonal_period, gamma, rra-num
				if (!(isset($p1) && isset($p2) && isset($p3)))
				{
					throw new Exception('Wrong Paramcount for CF ' . $cf);
				}
				$this->archives[] = array(
					'cf' => $cf,
					'seasonal_period' => $p1,
					'gamma' => $p2,
					'rra-num' => $p3
				);
				break;
			case 'DEVPREDICT':
				// Params: rows, rra-num
				if (!(isset($p1) && isset($p2)))
				{
					throw new Exception('Wrong Paramcount for CF ' . $cf);
				}
				$this->archives[] = array(
					'cf' => $cf,
					'rows' => $p1,
					'rra-num' => $p2
				);
				break;
			case 'FAILURES':
				// Params: rows, threshold, window_length, rra-num
				if (!(isset($p1) && isset($p2) && isset($p3) && isset($p4)))
				{
					throw new Exception('Wrong Paramcount for CF ' . $cf);
				}
				$this->archives[] = array(
					'cf' => $cf,
					'rows' => $p1,
					'threshold' => $p2,
					'window_length' => $p3,
					'rra-num' => $p4
				);
				break;
			default:
				throw new Exception('Unknown CF: ' . $cf);
				break;
		}
	}
	
	public function setValue($dsname, $value)
	{
		if (!isset($this->values[$dsname]))
		{
			throw new Exception('Datasource "' . $dsname . '" unknown or computed');
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
		$precision = ini_get('precision');
		if (empty($precision))
		{
			$precision = 12;
		}
		else
		{
			$precision = intval($precision);
		}
		foreach ($this->values as $dsname => $dsvalue)
		{
			$templatestr .= $dsname . ':';
			if (is_double($dsvalue))
			{
				$stringrep = strval($dsvalue);
				$parts = array();
				if (preg_match('/^([+\-]?[0-9]+)(\.([0-9]+))?[eE]([+\-]?[0-9]+)$/', $stringrep, $parts))
				{
					$exponent = intval($parts[4]);
					$decimals = $precision - $exponent;
					if ($decimals < 0)
					{
						$decimals = 0;
					}
					$dsvalue = number_format($dsvalue, $decimals, '.', '');
				}
			}
			$updatestr .= ':' . $dsvalue;
		}
		$templatestr = substr($templatestr, 0, -1);
		$params .= ' -t  ' . escapeshellarg($templatestr);
		$params .= ' ' . escapeshellarg($updatestr);
		$output = array();
		$return = 0;
		$command = escapeshellcmd($this->rrdtoolbin) . $params;
		exec($command . ' 2>&1', $output, $return);
		if ($return != 0)
		{
			throw new Exception('rrdtool ("' . $command . '") finished with exitcode ' . $return . "\n" . implode("\n", $output));
		}
	}
	
	public function checkVersion($compare, $neededversion)
	{
		$output = array();
		$command = escapeshellcmd($this->rrdtoolbin);
		exec($command . ' 2>&1', $output);
		if (isset($output[0]) && preg_match('/^rrdtool\s+(\d+\.\d+\.\d+)/i', $output[0], $parts))
		{
			$rrdversion = $parts[1];
			return version_compare($rrdversion, $neededversion, $compare);
		}
		else
		{
			throw new Exception('rrdtool version check failed!');
		}
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
	
	public static function escapeDsName($name)
	{
		return preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
	}
}

?>
