<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://www.webmasterpro.de/~ddanier/serverstats/
 * License: GPL v2 or later (http://www.gnu.org/copyleft/gpl.html)
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
