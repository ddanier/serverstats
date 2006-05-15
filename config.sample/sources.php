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

// Define the sources, read the sourcefiles for the needed details

// Add sources using the simple configuration
// (To change what sources are used by simpleconfig please edit simple.php)
simpleconfig::sources($config, $rootConfig['simple']);

// Add own sources (examples, like those used in the simple-config)
/*
$config['cpu']['module'] = new cpu();
$config['mem']['module'] = new memory();
$config['load']['module'] = new load();
$config['users']['module'] = new users();
$config['traffic_proc']['module'] = new traffic_proc('eth0');
*/

?>
