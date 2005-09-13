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

// Load all needed classes, function and everything else
require_once('init.php');

foreach ($config['sources'] as $sourcename => $sourcedata)
{
	echo "Working on $sourcename\n";
	$source = $sourcedata['module'];
	// All needed Vars
	$cachefile = CACHEPATH . $sourcename . '.sav';
	$rrdcachefile = CACHEPATH . $sourcename . '.rrd.sav';
	$rrdfile = RRDPATH . $sourcename . '.rrd';
	// The classes may throw exceptions, so we need to catch them
	try
	{
		if (!($source instanceof source))
		{
			throw new Exception('Source "' . $sourcename . '" not instanceof source');
		}
		$source->init();
		if ($source->useCache())
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
		if ($source->useCache())
		{
			$cache = serialize($source->getCache());
			file_put_contents($cachefile, $cache);
			unset($cache);
		}
		$sourcerrd = new rrd($config['main']['rrdtool'], $rrdfile);
		if (file_exists($rrdcachefile))
		{
			$cache = unserialize(file_get_contents($rrdcachefile));
			$sourcerrd->initOptions($cache);
			unset($cache);
		}
		else
		{
			echo "\tCreating RRD-file\n";
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
			$cache = serialize($sourcerrd->getOptions());
			file_put_contents($rrdcachefile, $cache);
			unset($cache);
		}
		echo "\tUpdating RRD-file\n";
		$source->updateRRD($sourcerrd);
		$sourcerrd->update();
	}
	catch (Exception $e)
	{
		$config['main']['logger']->logException(logger::ERR, $e);
	}
}
?>
