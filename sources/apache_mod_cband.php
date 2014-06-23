<?php
/**
 * $Id$
 *
 * Author: cryptogopher@github
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 *
 * Copyright (C) 2010 Piotr Michalczyk
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
 *
 *
 * Requirements:
 *
 * In order to use this source you have to configure Apache/mod_cband
 * like that:
 *
 * LoadModule cband_module modules/mod_cband.so
 * <Location /cband-status>
 *     SetHandler cband-status
 *     Order deny,allow
 *     Deny from all
 *     Allow from 127.0.0.1
 * </Location>
 *
 * Then configure CBandUser with following directives:
 * 
 * <CBandUser example>
 *  CBandUserLimit 0
 *  CBandUserScoreboard /var/log/apache2/example.cband
 * </CBandUser>
 * 
 * Next add CBandUser to as many VirtualHosts as You wish:
 * <VirtualHost *:80>
 *  ServerName a.example.com
 *  ...
 *  CBandUser example
 * </VirtualHost>
 *
 * Now You can gather statistics for either VirtualHost or user
 * passing their names as '$targets' to this module.
 */

class apache_mod_cband extends source implements source_rrd
{
	private $url_cbandstatus;
	private $targets;
	private $labels;
	private $data;
	
	public function __construct($targets = array(), $url_cbandstatus = 'http://localhost/cband-status?xml')
	{
		foreach ($targets as $target) {
			$this->labels[] = array(
				$target.' traffic used' => $target.'_tused',
				$target.' traffic limit' => $target.'_tlimit',
				$target.' bandwidth used' => $target.'_bused',
				$target.' bandwidth limit' => $target.'_blimit'
			);
		}
		
		$this->url_cbandstatus = $url_cbandstatus;
		$this->targets = $targets;
	}
	
	public function refreshData()
	{
		if (($lines = @file($this->url_cbandstatus)) === false)
		{
			throw new Exception('Error while reading cband-status (perhaps file-access is forbidden, check \'allow_url_fopen\'?)');
			return;
		}
		$status = new DOMDocument();
		$status->loadXML(implode('', $lines));
		foreach ($this->targets as $target) {
			$elem = $status->getElementsByTagName($target);
			if ($elem != null) {
				$limits = $elem->item(0)->getElementsByTagName("limits");
				$total = $limits->item(0)->getElementsByTagName("total");
				$this->data[$target.'_tlimit'] = ((int)$total->item(0)->nodeValue)*1024;
				$kbps = $limits->item(0)->getElementsByTagName("kbps");
				$this->data[$target.'_blimit'] = (int)$kbps->item(0)->nodeValue;
				
				$usages = $elem->item(0)->getElementsByTagName("usages");
				$total = $usages->item(0)->getElementsByTagName("total");
				$this->data[$target.'_tused'] = ((int)$total->item(0)->nodeValue)*1024;
				$this->data[$target.'_bused'] = $this->data[$target.'_tused'];
			} else {
				$this->data[$target.'_tused'] = 0;
				$this->data[$target.'_tlimit'] = 0;
				$this->data[$target.'_bused'] = 0;
				$this->data[$target.'_blimit'] = 0;
			}
		}
	}
	
	public function initRRD(rrd $rrd)
	{
		foreach ($this->targets as $target)
		{
			$rrd->addDatasource(rrd::escapeDsName($target.'_tused'), 'GAUGE');
			$rrd->addDatasource(rrd::escapeDsName($target.'_tlimit'), 'GAUGE');
			$rrd->addDatasource(rrd::escapeDsName($target.'_bused'), 'DERIVE', null, 0);
			$rrd->addDatasource(rrd::escapeDsName($target.'_blimit'), 'GAUGE');
		}
	}
	
	public function fetchValues()
	{
		$values = array();
		foreach ($this->data as $dsname => $value)
		{
			$values[rrd::escapeDsName($dsname)] = $value;
		}
		return $values;
	}
}

?>
