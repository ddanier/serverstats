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

// Layout for all sources
abstract class source
{
	// Called when updating the data, __construct should not be used
	public function init() { }
	// Called to refresh the data
	abstract function refreshData();
	// Called to test if the source needs the cache
	public function useCache() { return false; }
	// Called if no cachedata is present
	public function initCache() { }
	// Called to load the cached data
	public function loadCache($cachedata) { }
	// Called to fetch the cache and save it back to disk
	public function getCache() { }
	// Called if no rrd (or rrdcache) in present
	abstract public function initRRD(rrd $rrd);
	// Called to update the rrd
	abstract public function updateRRD(rrd $rrd);
}

?>
