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

// Define the sources, read the sourcefiles for the needed details
$config = array();
$config['cactisample']['module'] = new external(SOURCEPATH . 'external/cacti/XYZ.pl');
$config['cactisample']['rra'] = 'default';
$config['trafficCHAIN']['module'] = new traffic('CHAIN');
$config['trafficCHAIN']['rra'] = 'default';
$config['mysql']['module'] = new mysql('user', 'password', 'localhost');
$config['mysql']['rra'] = 'default';
$config['load']['module'] = new load();
$config['load']['rra'] = 'default';
$config['users']['module'] = new users();
$config['users']['rra'] = 'default';

// The external source uses default values for the datasources, you can change
// these values using:
// $config['externalsample']['module']->addDatasourceDefinition(<dsname>, <DST>, <heartbeat>, <min>, <max>)

?>
