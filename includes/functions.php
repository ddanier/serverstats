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

// All sources should be loaded on demand
function __autoload($class)
{
	if (file_exists(SOURCEPATH . $class . '.php'))
	{
		require_once(SOURCEPATH . $class . '.php');
	}
	elseif (file_exists(INCLUDEPATH . $class . '.class.php'))
	{
		require_once(INCLUDEPATH . $class . '.class.php');
	}
}

// Function to  draw the menu
function menu()
{
	global $config;
	if (!isset($config) || !isset($config['graph']['list']))
	{
		return;
	}
	?><div id="menu">
	<ul>
	<li class="index"><a href="index.php"><?php echo lang::t('Summary'); ?></a></li>
	<?php
	foreach ($config['graph']['list'] as $graphindex => $graph)
	{
		?>
		<li class="detail">
			<a href="detail.php?graph=<?php echo $graphindex; ?>"><?php echo htmlspecialchars($graph['title']); ?></a>
		</li>
		<?php
	}
	?>
	</ul>
	</div>
	<?php
}

// Function to test if some values are set in an array
function array_check($array, $values)
{
	if (!is_array($values))
	{
		$values = array($values);
	}
	foreach ($values as $value)
	{
		if (!isset($array[$value]))
		{
			return false;
		}
	}
	return true;
}

// Function to get a value out of an array and return a defaultvalue if value is not set in array
function array_get($array, $value, $default = null)
{
	if (isset($array[$value]))
	{
		return $array[$value];
	}
	else
	{
		return $default;
	}
}

function value_compare($value1, $value2, $operator)
{
	switch ($operator)
	{
		case '>=':
			return ($value1 >= $value2);
		case '>':
			return ($value1 > $value2);
		case '<=':
			return ($value1 <= $value2);
		case '<':
			return ($value1 < $value2);
		case '=':
		case '==':
			return ($value1 == $value2);
		case '!=':
			return ($value1 != $value2);
		default:
			throw new Exception('Invalid operator');
	}
}

?>
