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

$config['used'] = true; // is the simple config used
$config['modules'] = array(
	'apache' => array(
		'used' => false,
		'hosts' => array('localhost'),
		'graphs' => array(
			'requests' => array('used' => true, 'title' => 'Apache: Requests/s (%s)')
		)
	),
	'cpu' => array(
		'used' => true,
		'graphs' => array(
			'usage' => array('used' => true, 'title' => 'CPU usage')
		)
	),
	'disk' => array(
		'used' => false,
		'disks' => array('hda'),
		'graphs' => array(
			'iorate' => array('used' => true, 'title' => 'IO-Rate (%s)')
		)
	),
	'load' => array(
		'used' => true,
		'graphs' => array(
			'load' => array('used' => true, 'title' => 'Load'),
			'processes' => array('used' => true, 'title' => 'Processes')
		)
	),
	'memory' => array(
		'used' => true,
		'graphs' => array(
			'memory' => array('used' => true, 'title' => 'Memory'),
			'swap' => array('used' => true, 'title' => 'Swap')
		)
	),
	'mysql' => array(
		'used' => false,
		'host' => 'localhost',
		'user' => 'somebody',
		'password' => 'something',
		'graphs' => array(
			'questions' => array('used' => true, 'title' => 'MySQL: questions per second'),
			'processes' => array('used' => true, 'title' => 'MySQL: query count ("SHOW PROCESSLIST")')
		)
	),
	'ping' => array(
		'used' => false,
		'hosts' => array('www.google.com'),
		'graphs' => array(
			'combined' => array('used' => true, 'title' => 'Ping'),
			'single' => array('used' => false, 'title' => 'Ping (%s)')
		)
	),
	'ping_http' => array(
		'used' => false,
		'hosts' => array('www.google.com'),
		'graphs' => array(
			'combined' => array('used' => true, 'title' => 'HTTP-Ping'),
			'single' => array('used' => true, 'title' => 'HTTP-Ping (%s)')
		)
	),
	'traffic' => array(
		'used' => false,
		'chains' => array('WWW'),
		'graphs' => array(
			'combined_bps' => array('used' => true, 'title' => 'Traffic (bps)'),
			'single_bps' => array('used' => false, 'title' => '%s-Traffic (bps)'),
			'combined_count' => array('used' => true, 'title' => 'Traffic (count)'),
			'single_count' => array('used' => false, 'title' => '%s-Traffic (count)')
		)
	),
	'traffic_proc' => array(
		'used' => true,
		'interfaces' => array('eth0'),
		'graphs' => array(
			'combined_bps' => array('used' => false, 'title' => 'Traffic'),
			'single_bps' => array('used' => true, 'title' => 'Traffic (%s)')
		)
	),
	'users' => array(
		'used' => true,
		'graphs' => array(
			'logged_in' => array('used' => true, 'title' => 'Users logged in')
		)
	)
);

?>
