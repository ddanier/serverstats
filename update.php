#!/usr/bin/php
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
	// All needed Vars
	$cachefile = CACHEPATH . $sourcename . '.sav';
	$rrdfile = RRDPATH . $sourcename . '.rrd';
	// The classes may throw exceptions, so we need to catch them
	try
	{
		if (!($source instanceof source))
		{
			throw new Exception('Source "' . $sourcename . '" not instanceof source');
		}
		$source->init();
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
		$source->refreshData();
		if ($source instanceof source_cached)
		{
			$cache = serialize($source->getCache());
			file_put_contents($cachefile, $cache);
			unset($cache);
		}
		$sourcevalues = $source->fetchValues();
		if ($source instanceof source_rrd)
		{
			$sourcerrd = new rrd($config['main']['rrdtool'], $rrdfile);
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
			echo "\tUpdating RRD-file" . PHP_EOL;
			$config['log']['logger']->logString(logger::INFO, 'Updating source "' . $sourcename . '"');
			foreach ($sourcevalues as $valuename => $value)
			{
				$sourcerrd->setValue($valuename, $value);
			}
			$sourcerrd->update();
		}
		// TODO: Only send warnings once
		if (isset($config['monitor'][$sourcename]))
		{
			echo "\tMonitoring values" . PHP_EOL;
			foreach ($config['monitor'][$sourcename] as $rule)
			{
				$matches = array();
				if (preg_match('/^([a-zA-Z0-9_]+)\s*(>=?|<=?)\s*(.*)$/', $rule, $matches))
				{
					$name = $matches[1];
					$operator = $matches[2];
					$limit = $matches[3];
					if (!isset($sourcevalues[$name]))
					{
						throw new Exception("Invalid datasource in rule: $rule ($sourcename)");
					}
					if (value_compare($sourcevalues[$name], $limit, $operator))
					{
						echo "\t\tLimit hit: $rule ($sourcename)" . PHP_EOL;
						$config['log']['logger']->logString(logger::ERR, "Limit hit: $rule ($sourcename)");
					}
				}
				else
				{
					throw new Exception("Invalid rule: $rule ($sourcename)");
				}
			}
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
