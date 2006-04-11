#!/usr/bin/php
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

// Abort if the script is run by the webserver
if (!isset($_SERVER["argv"][0])) {
        die('<br /><strong>Read the README</strong>');
}

// Load all needed classes, function and everything else
require_once('init.php');

foreach ($config['sources'] as $sourcename => $sourcedata)
{
	echo "Working on $sourcename" . PHP_EOL;
	$source = $sourcedata['module'];
	$cachefile = CACHEPATH . $sourcename . '.sav';
	// The classes may throw exceptions, so we need to catch them
	try
	{
		if (!($source instanceof source))
		{
			throw new Exception('Source "' . $sourcename . '" not instanceof source');
		}
		// Init the source
		$source->init();
		// Init/load cache if needed
		if ($source instanceof source_cached)
		{
			if (file_exists($cachefile))
			{
				$cache = unserialize(file_get_contents($cachefile));
				$source->loadCache($cache);
				unset($cache);
			}
			else
			{
				$source->initCache();
			}
		}
		// Refresh data
		$source->refreshData();
		// Save cache (if needed)
		if ($source instanceof source_cached)
		{
			$cache = serialize($source->getCache());
			file_put_contents($cachefile, $cache);
			unset($cache);
		}
		// Receive values
		$sourcevalues = $source->fetchValues();
		// If the source needs an RRD: create and update it
		if ($source instanceof source_rrd)
		{
			// Needed vars
			$rrdfile = RRDPATH . $sourcename . '.rrd';
			$sourcerrd = new rrd($config['main']['rrdtool'], $rrdfile);
			// Create RRD-file if it does not exist
			if (!file_exists($rrdfile))
			{
				if ($sourcerrd->checkVersion('<', '1.2'))
				{
					throw new Exception('rrdtool >= 1.2 required');
				}
				echo "\tCreating RRD-file" . PHP_EOL;
				$config['log']['logger']->logString(logger::INFO, 'Creating RRD-file for source "' . $sourcename . '"');
				$source->initRRD($sourcerrd);
				$sourcerrd->setStep($config['main']['step']);
				if (isset($sourcedata['rra']))
				{
					$sourcerra = $sourcedata['rra'];
				}
				else
				{
					$sourcerra = 'default';
				}
				foreach ($config['rra'][$sourcerra] as $rra)
				{
					$sourcerrd->addArchive($rra['cf'], $rra['xff'], $rra['steps'], $rra['rows']);
				}
				$sourcerrd->create();
			}
			// Update RRD-file
			echo "\tUpdating RRD-file" . PHP_EOL;
			$config['log']['logger']->logString(logger::INFO, 'Updating source "' . $sourcename . '"');
			foreach ($sourcevalues as $valuename => $value)
			{
				$sourcerrd->setValue($valuename, $value);
			}
			$sourcerrd->update();
		}
		// If the source is monitored: check all values
		if (isset($config['monitor'][$sourcename]))
		{
			// Needed vars
			$monitorcachefile = CACHEPATH . $sourcename . '.monitor.sav';
			$monitorcache = array();
			// Load cache if needed
			if (file_exists($monitorcachefile))
			{
				$monitorcache = unserialize(file_get_contents($monitorcachefile));
			}
			// Check all values
			echo "\tMonitoring values" . PHP_EOL;
			foreach ($config['monitor'][$sourcename] as $rulenum => $rule)
			{
				// Init cache if needed
				if (!isset($monitorcache[$rulenum]))
				{
					$monitorcache[$rulenum] = false;
				}
				$matches = array();
				// Test if we have a valid test
				if (preg_match('/^([a-zA-Z0-9_]+)\s*(>=?|<=?|!?=)\s*(.*)$/', $rule, $matches))
				{
					$name = $matches[1];
					$operator = $matches[2];
					$limit = $matches[3];
					// Test if we know the datasource
					if (!isset($sourcevalues[$name]))
					{
						$config['log']['logger']->logString(logger::WARN, "Invalid or undefined datasource in rule: '$rule' (Source '$sourcename')");
						$value = 'U';
					}
					else
					{
						$value = $sourcevalues[$name];
					}
					// Test if limit is hit
					if (value_compare($value, $limit, $operator))
					{
						echo "\t\tLimit hit: $rule ($sourcename), current: $value" . PHP_EOL;
						if (!$monitorcache[$rulenum])
						{
							$config['log']['logger']->logString(logger::ERR, "Limit hit: '$rule' (Source '$sourcename'), current value: $value");
						}
						$monitorcache[$rulenum] = true;
					}
					else
					{
						$monitorcache[$rulenum] = false;
					}
				}
				// Handle invalid rules
				else
				{
					throw new Exception("Invalid rule: '$rule' (Source '$sourcename')");
				}
			}
			// Save Cache
			file_put_contents($monitorcachefile, serialize($monitorcache));
		}
	}
	catch (Exception $e)
	{
		echo "Error:" . PHP_EOL;
		echo $e;
		echo PHP_EOL;
		$config['log']['logger']->logException(logger::ERR, $e);
	}
}
?>
