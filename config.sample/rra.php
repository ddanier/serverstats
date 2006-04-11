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

// Define what archives will be used (-> RRA)
// see 'man rrdcreate' for details

// You can define multiple RRAs, this is the default: (don't delete the default)
$config['default'] = array(
	'day' => array('steps' => 1, 'rows' => 1200, 'cf' => 'AVERAGE', 'xff' => 0.5), // ca. 4 days
	'week' => array('steps' => 2, 'rows' => 1800, 'cf' => 'AVERAGE', 'xff' => 0.5), // ca. 2 weeks
	'month' => array('steps' => 12, 'rows' => 1500, 'cf' => 'AVERAGE', 'xff' => 0.5), // ca. 2 month
	'year' => array('steps' => 36, 'rows' => 12000, 'cf' => 'AVERAGE', 'xff' => 0.5) // ca. 4 years
);
/* Use this for a 60 seconds step
$config['default'] = array(
	'hour' => array('steps' => 1, 'rows' => 1800, 'cf' => 'AVERAGE', 'xff' => 0.5), // ca. 24 hours
	'day' => array('steps' => 5, 'rows' => 1200, 'cf' => 'AVERAGE', 'xff' => 0.5), // ca. 4 days
	'week' => array('steps' => 10, 'rows' => 1800, 'cf' => 'AVERAGE', 'xff' => 0.5), // ca. 2 weeks
	'month' => array('steps' => 60, 'rows' => 1500, 'cf' => 'AVERAGE', 'xff' => 0.5), // ca. 2 month
	'year' => array('steps' => 180, 'rows' => 12000, 'cf' => 'AVERAGE', 'xff' => 0.5) // ca. 4 years
);
*/

?>
