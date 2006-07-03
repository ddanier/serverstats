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

class simpleconfig
{
	public static function graph(&$config, $simpleconfig)
	{
		if (!$simpleconfig['used'])
		{
			return;
		}
		foreach ($simpleconfig['modules'] as $module => $modconf)
		{
			if (!$modconf['used'])
			{
				continue;
			}
			switch ($module)
			{
				case 'apache':
					foreach ($modconf['hosts'] as $host)
					{
						foreach ($modconf['graphs'] as $graph => $graphconf)
						{
							if (!$graphconf['used'])
							{
								continue;
							}
							switch ($graph)
							{
								case 'requests':
									$config['list'][] = array(
										'title' => sprintf($graphconf['title'], $host),
										'lowerLimit' => 0,
										'altAutoscaleMax' => true,
										'categories' => array('host' => $host),
										'content' => array(
											array(
												'type' => 'LINE',
												'source' => $module . '_' . rrd::escapeDsName($host),
												'ds' => 'requestsps',
												'cf' => 'AVERAGE',
												'legend' => 'Requests/s',
												'color' => '7FD180'
											)
										)
									);
									break;
							}
						}
					}
					break;
				case 'cpu':
					foreach ($modconf['graphs'] as $graph => $graphconf)
					{
						if (!$graphconf['used'])
						{
							continue;
						}
						switch ($graph)
						{
							case 'usage':
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'upperLimit' => 100,
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => 'localhost'),
									'content' => array(
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'cpu_system',
											'cf' => 'AVERAGE',
											'legend' => 'System',
											'color' => 'FF0000'
										),
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'cpu_user',
											'cf' => 'AVERAGE',
											'legend' => 'User',
											'color' => '00FF00',
											'stacked' => true
										),
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'cpu_nice',
											'cf' => 'AVERAGE',
											'legend' => 'Nice',
											'color' => '0000FF',
											'stacked' => true
										),
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'cpu_idle',
											'cf' => 'AVERAGE',
											'legend' => 'Idle',
											'color' => 'FFFF00',
											'stacked' => true
										),
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'cpu_iowait',
											'cf' => 'AVERAGE',
											'legend' => 'IOwait',
											'color' => 'FFAA00',
											'stacked' => true
										),
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'cpu_irq',
											'cf' => 'AVERAGE',
											'legend' => 'IRQ',
											'color' => 'FF6600',
											'stacked' => true
										),
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'cpu_softirq',
											'cf' => 'AVERAGE',
											'legend' => 'SoftIRQ',
											'color' => 'AAFF00',
											'stacked' => true
										)
									)
								);
								break;
						}
					}
					break;
				case 'disk':
					foreach ($modconf['disks'] as $disk)
					{
						foreach ($modconf['graphs'] as $graph => $graphconf)
						{
							if (!$graphconf['used'])
							{
								continue;
							}
							switch ($graph)
							{
								case 'iorate':
									$config['list'][] = array(
										'title' => sprintf($graphconf['title'], $disk),
										'lowerLimit' => 0,
										'altAutoscaleMax' => true,
										'verticalLabel' => 'byte/s',
										'categories' => array('host' => 'localhost'),
										'content' => array(
											array(
												'type' => 'LINE',
												'source' => $module . '_' . rrd::escapeDsName($disk),
												'ds' => 'readps',
												'cf' => 'AVERAGE',
												'legend' => 'read',
												'color' => 'FF0000'
											),
											array(
												'type' => 'LINE',
												'source' => $module . '_' . rrd::escapeDsName($disk),
												'ds' => 'writeps',
												'cf' => 'AVERAGE',
												'legend' => 'write',
												'color' => 'EEEE00'
											)
										)
									);
									break;
							}
						}
					}
					break;
				case 'load':
					foreach ($modconf['graphs'] as $graph => $graphconf)
					{
						if (!$graphconf['used'])
						{
							continue;
						}
						switch ($graph)
						{
							case 'load':
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => 'localhost'),
									'content' => array(
										array(
											'type' => 'COMMENT',
											'text' => 'average number of tasks in the queue\:\n'
										),
										array(
											'type' => 'LINE',
											'source' => $module,
											'ds' => '1min',
											'cf' => 'AVERAGE',
											'legend' => '1 minute',
											'width' => 1,
											'color' => 'FFDD00'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_1min',
											'cf' => 'LAST',
											'format' => '  cur\: %01.2lf'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_1min',
											'cf' => 'MAXIMUM',
											'format' => 'max\: %01.2lf'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_1min',
											'cf' => 'MINIMUM',
											'format' => 'min\: %01.2lf'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_1min',
											'cf' => 'AVERAGE',
											'format' => 'avg\: %01.2lf\n'
										),
										array(
											'type' => 'LINE',
											'source' => $module,
											'ds' => '5min',
											'cf' => 'AVERAGE',
											'legend' => '5 minutes',
											'width' => 1,
											'color' => 'FF8800'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_5min',
											'cf' => 'LAST',
											'format' => ' cur\: %01.2lf'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_5min',
											'cf' => 'MAXIMUM',
											'format' => 'max\: %01.2lf'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_5min',
											'cf' => 'MINIMUM',
											'format' => 'min\: %01.2lf'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_5min',
											'cf' => 'AVERAGE',
											'format' => 'avg\: %01.2lf\n'
										),
										array(
											'type' => 'LINE',
											'source' => $module,
											'ds' => '15min',
											'cf' => 'AVERAGE',
											'legend' => '15 minutes',
											'width' => 1,
											'color' => 'FF0000'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_15min',
											'cf' => 'LAST',
											'format' => 'cur\: %01.2lf'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_15min',
											'cf' => 'MAXIMUM',
											'format' => 'max\: %01.2lf'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_15min',
											'cf' => 'MINIMUM',
											'format' => 'min\: %01.2lf'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'load_15min',
											'cf' => 'AVERAGE',
											'format' => 'avg\: %01.2lf\n'
										)
									)
								);
								break;
							case 'processes':
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => 'localhost'),
									'content' => array(
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'tasks',
											'cf' => 'AVERAGE',
											'legend' => 'number of processes',
											'color' => 'FFDD00'
										),
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'running',
											'cf' => 'AVERAGE',
											'legend' => 'running processes',
											'color' => 'FF0000'
										)
									)
								);
								break;
						}
					}
					break;
				case 'memory':
					foreach ($modconf['graphs'] as $graph => $graphconf)
					{
						if (!$graphconf['used'])
						{
							continue;
						}
						switch ($graph)
						{
							case 'memory':
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => 'localhost'),
									'content' => array(
										array(
											'type' => 'DEF',
											'source' => $module,
											'ds' => 'MemTotal',
											'cf' => 'AVERAGE',
											'name' => 'total'
										),
										array(
											'type' => 'CDEF',
											'expression' => 'total,1024,/,1024,/',
											'name' => 'total_mb'
										),
										array(
											'type' => 'DEF',
											'source' => $module,
											'ds' => 'MemFree',
											'cf' => 'AVERAGE',
											'name' => 'free'
										),
										array(
											'type' => 'CDEF',
											'expression' => 'free,1024,/,1024,/',
											'name' => 'free_mb'
										),
										array(
											'type' => 'DEF',
											'source' => $module,
											'ds' => 'Cached',
											'cf' => 'AVERAGE',
											'name' => 'cached'
										),
										array(
											'type' => 'CDEF',
											'expression' => 'cached,1024,/,1024,/',
											'name' => 'cached_mb'
										),
										array(
											'type' => 'AREA',
											'name' => 'total',
											'legend' => 'total',
											'color' => 'FFFFCC'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'total_mb',
											'cf' => 'LAST',
											'format' => ' cur\: %01.2lf MB\n'
										),
										array(
											'type' => 'AREA',
											'name' => 'free',
											'legend' => 'free',
											'color' => 'FF0000'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'free_mb',
											'cf' => 'LAST',
											'format' => '   cur\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'free_mb',
											'cf' => 'MINIMUM',
											'format' => 'min\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'free_mb',
											'cf' => 'MAXIMUM',
											'format' => 'max\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'free_mb',
											'cf' => 'AVERAGE',
											'format' => 'avg\: %01.2lf MB\n'
										),
										array(
											'type' => 'STACK',
											'name' => 'cached',
											'legend' => 'cached',
											'color' => 'EEDD22'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'cached_mb',
											'cf' => 'LAST',
											'format' => 'cur\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'cached_mb',
											'cf' => 'MINIMUM',
											'format' => 'min\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'cached_mb',
											'cf' => 'MAXIMUM',
											'format' => 'max\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'cached_mb',
											'cf' => 'AVERAGE',
											'format' => 'avg\: %01.2lf MB'
										),
										array(
											'type' => 'LINE',
											'width' => 1,
											'name' => 'total',
											'color' => '000000'
										)
									)
								);
								break;
							case 'swap':
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => 'localhost'),
									'content' => array(
										array(
											'type' => 'DEF',
											'source' => $module,
											'ds' => 'SwapTotal',
											'cf' => 'AVERAGE',
											'name' => 'total'
										),
										array(
											'type' => 'CDEF',
											'expression' => 'total,1024,/,1024,/',
											'name' => 'total_mb'
										),
										array(
											'type' => 'DEF',
											'source' => $module,
											'ds' => 'SwapFree',
											'cf' => 'AVERAGE',
											'name' => 'free'
										),
										array(
											'type' => 'CDEF',
											'expression' => 'free,1024,/,1024,/',
											'name' => 'free_mb'
										),
										array(
											'type' => 'DEF',
											'source' => $module,
											'ds' => 'SwapCached',
											'cf' => 'AVERAGE',
											'name' => 'cached'
										),
										array(
											'type' => 'CDEF',
											'expression' => 'cached,1024,/,1024,/',
											'name' => 'cached_mb'
										),
										array(
											'type' => 'AREA',
											'name' => 'total',
											'legend' => 'total',
											'color' => 'FFFFCC'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'total_mb',
											'cf' => 'LAST',
											'format' => ' cur\: %01.2lf MB\n'
										),
										array(
											'type' => 'AREA',
											'name' => 'free',
											'legend' => 'free',
											'color' => 'FF0000'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'free_mb',
											'cf' => 'LAST',
											'format' => '   cur\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'free_mb',
											'cf' => 'MINIMUM',
											'format' => 'min\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'free_mb',
											'cf' => 'MAXIMUM',
											'format' => 'max\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'free_mb',
											'cf' => 'AVERAGE',
											'format' => 'avg\: %01.2lf MB\n'
										),
										array(
											'type' => 'STACK',
											'name' => 'cached',
											'legend' => 'cached',
											'color' => 'EEDD22'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'cached_mb',
											'cf' => 'LAST',
											'format' => 'cur\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'cached_mb',
											'cf' => 'MINIMUM',
											'format' => 'min\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'cached_mb',
											'cf' => 'MAXIMUM',
											'format' => 'max\: %01.2lf MB'
										),
										array(
											'type' => 'GPRINT',
											'name' => 'cached_mb',
											'cf' => 'AVERAGE',
											'format' => 'avg\: %01.2lf MB'
										),
										array(
											'type' => 'LINE',
											'width' => 1,
											'name' => 'total',
											'color' => '000000'
										)
									)
								);
								break;
						}
					}
					break;
				case 'mysql':
					foreach ($modconf['graphs'] as $graph => $graphconf)
					{
						if (!$graphconf['used'])
						{
							continue;
						}
						switch ($graph)
						{
							case 'questions':
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => $modconf['host']),
									'content' => array(
										array(
											'type' => 'LINE',
											'source' => $module,
											'ds' => 'questionsps',
											'cf' => 'AVERAGE',
											'legend' => 'questions/s',
											'width' => 1,
											'color' => '00A302'
										)
									)
								);
								break;
							case 'processes':
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => $modconf['host']),
									'content' => array(
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'processcount',
											'cf' => 'AVERAGE',
											'legend' => 'number of queries',
											'color' => '7FD180'
										)
									)
								);
								break;
						}
					}
					break;
				case 'ping':
					foreach ($modconf['hosts'] as $host)
					{
						foreach ($modconf['graphs'] as $graph => $graphconf)
						{
							if (!$graphconf['used'])
							{
								continue;
							}
							switch ($graph)
							{
								case 'single':
									$config['list'][] = array(
										'title' => sprintf($graphconf['title'], $host),
										'lowerLimit' => 0,
										'altAutoscaleMax' => true,
										'categories' => array('host' => $host),
										'content' => array(
											array(
												'type' => 'LINE',
												'source' => $module . '_' . rrd::escapeDsName($host),
												'ds' => 'time',
												'cf' => 'AVERAGE',
												'legend' => $host,
												'color' => '7FD180'
											)
										)
									);
									break;
							}
						}
					}
					foreach ($modconf['graphs'] as $graph => $graphconf)
					{
						if (!$graphconf['used'])
						{
							continue;
						}
						switch ($graph)
						{
							case 'combined':
								$content = array();
								srand(0);
								foreach ($modconf['hosts'] as $host)
								{
									$content[] = array(
										'type' => 'LINE',
										'source' => $module . '_' . rrd::escapeDsName($host),
										'ds' => 'time',
										'cf' => 'AVERAGE',
										'legend' => $host . '\n',
										'color' => sprintf('%02X%02X%02X', rand(0, 255), rand(0, 255), rand(0, 255))
									);
								}
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => $modconf['hosts']),
									'content' => $content
								);
								break;
						}
					}
					break;
				case 'ping_http':
					foreach ($modconf['hosts'] as $host)
					{
						foreach ($modconf['graphs'] as $graph => $graphconf)
						{
							if (!$graphconf['used'])
							{
								continue;
							}
							switch ($graph)
							{
								case 'single':
									$config['list'][] = array(
										'title' => sprintf($graphconf['title'], $host),
										'lowerLimit' => 0,
										'altAutoscaleMax' => true,
										'categories' => array('host' => $host),
										'content' => array(
											array(
												'type' => 'AREA',
												'source' => $module . '_' . rrd::escapeDsName($host),
												'ds' => 'open',
												'cf' => 'AVERAGE',
												'legend' => 'open connection\n',
												'color' => 'DEE100'
											),
											array(
												'type' => 'AREA',
												'stacked' => true,
												'source' => $module . '_' . rrd::escapeDsName($host),
												'ds' => 'send',
												'cf' => 'AVERAGE',
												'legend' => 'send request\n',
												'color' => '665B00'
											),
											array(
												'type' => 'AREA',
												'stacked' => true,
												'source' => $module . '_' . rrd::escapeDsName($host),
												'ds' => 'receive',
												'cf' => 'AVERAGE',
												'legend' => 'receive response\n',
												'color' => '534BEF'
											),
											array(
												'type' => 'AREA',
												'stacked' => true,
												'source' => $module . '_' . rrd::escapeDsName($host),
												'ds' => 'close',
												'cf' => 'AVERAGE',
												'legend' => 'close connection\n',
												'color' => '060085'
											),
											array(
												'type' => 'LINE',
												'source' => $module . '_' . rrd::escapeDsName($host),
												'ds' => 'time',
												'cf' => 'AVERAGE',
												'legend' => 'total',
												'color' => 'CD0000'
											)
										)
									);
									break;
							}
						}
					}
					foreach ($modconf['graphs'] as $graph => $graphconf)
					{
						if (!$graphconf['used'])
						{
							continue;
						}
						switch ($graph)
						{
							case 'combined':
								$content = array();
								srand(0);
								foreach ($modconf['hosts'] as $host)
								{
									$content[] = array(
										'type' => 'LINE',
										'source' => $module . '_' . rrd::escapeDsName($host),
										'ds' => 'time',
										'cf' => 'AVERAGE',
										'legend' => $host . '\n',
										'color' => sprintf('%02X%02X%02X', rand(0, 255), rand(0, 255), rand(0, 255))
									);
								}
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => $modconf['hosts']),
									'content' => $content
								);
								break;
						}
					}
					break;
				case 'traffic':
					foreach ($modconf['chains'] as $chain)
					{
						foreach ($modconf['graphs'] as $graph => $graphconf)
						{
							if (!$graphconf['used'])
							{
								continue;
							}
							switch ($graph)
							{
								case 'single_bps':
									$config['list'][] = array(
										'title' => sprintf($graphconf['title'], $chain),
										'lowerLimit' => 0,
										'altAutoscaleMax' => true,
										'categories' => array('host' => 'localhost'),
										'content' => array(
											array(
												'type' => 'LINE',
												'source' => $module . '_' . rrd::escapeDsName($chain),
												'ds' => 'bps',
												'cf' => 'AVERAGE',
												'legend' => $chain,
												'color' => '7FD180'
											)
										)
									);
									break;
								case 'single_count':
									$config['list'][] = array(
										'title' => sprintf($graphconf['title'], $chain),
										'lowerLimit' => 0,
										'altAutoscaleMax' => true,
										'categories' => array('host' => 'localhost'),
										'content' => array(
											array(
												'type' => 'LINE',
												'source' => $module . '_' . rrd::escapeDsName($chain),
												'ds' => 'traffic',
												'cf' => 'AVERAGE',
												'legend' => $chain,
												'color' => '7FD180'
											)
										)
									);
									break;
							}
						}
					}
					foreach ($modconf['graphs'] as $graph => $graphconf)
					{
						if (!$graphconf['used'])
						{
							continue;
						}
						switch ($graph)
						{
							case 'combined_bps':
								$content = array();
								srand(0);
								foreach ($modconf['chains'] as $chain)
								{
									$content[] = array(
										'type' => 'LINE',
										'source' => $module . '_' . rrd::escapeDsName($chain),
										'ds' => 'bps',
										'cf' => 'AVERAGE',
										'legend' => $chain,
										'color' => sprintf('%02X%02X%02X', rand(0, 255), rand(0, 255), rand(0, 255))
									);
								}
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => 'localhost'),
									'content' => $content
								);
								break;
							case 'combined_count':
								$content = array();
								srand(0);
								foreach ($modconf['chains'] as $chain)
								{
									$content[] = array(
										'type' => 'LINE',
										'source' => $module . '_' . rrd::escapeDsName($chain),
										'ds' => 'traffic',
										'cf' => 'AVERAGE',
										'legend' => $chain,
										'color' => sprintf('%02X%02X%02X', rand(0, 255), rand(0, 255), rand(0, 255))
									);
								}
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => 'localhost'),
									'content' => $content
								);
								break;
						}
					}
					break;
				case 'traffic_proc':
					foreach ($modconf['interfaces'] as $interface)
					{
						foreach ($modconf['graphs'] as $graph => $graphconf)
						{
							if (!$graphconf['used'])
							{
								continue;
							}
							switch ($graph)
							{
								case 'single_bps':
									$config['list'][] = array(
										'title' => sprintf($graphconf['title'], $interface),
										'lowerLimit' => 0,
										'altAutoscaleMax' => true,
										'categories' => array('host' => 'localhost'),
										'content' => array(
											array(
												'type' => 'LINE',
												'source' => $module . '_' . rrd::escapeDsName($interface),
												'ds' => $interface . '_rbytes',
												'cf' => 'AVERAGE',
												'legend' => 'Download Bytes/s',
												'color' => '0002A3'
											),
											array(
												'type' => 'LINE',
												'source' => $module . '_' . rrd::escapeDsName($interface),
												'ds' => $interface . '_tbytes',
												'cf' => 'AVERAGE',
												'legend' => 'Upload Bytes/s',
												'color' => '00A302'
											)
										)
									);
									break;
							}
						}
					}
					foreach ($modconf['graphs'] as $graph => $graphconf)
					{
						if (!$graphconf['used'])
						{
							continue;
						}
						switch ($graph)
						{
							case 'combined_bps':
								$content = array();
								srand(0);
								foreach ($modconf['interfaces'] as $interface)
								{
									$content[] = array(
											'type' => 'LINE',
											'source' => $module . '_' . rrd::escapeDsName($interface),
											'ds' => $interface . '_rbytes',
											'cf' => 'AVERAGE',
											'legend' => 'Download Bytes/s (' . $interface . ')',
											'color' => sprintf('%02X%02X%02X', rand(0, 255), rand(0, 255), rand(0, 255))
									);
									$content[] = array(
											'type' => 'LINE',
											'source' => $module . '_' . rrd::escapeDsName($interface),
											'ds' => $interface . '_tbytes',
											'cf' => 'AVERAGE',
											'legend' => 'Upload Bytes/s (' . $interface . ')\n',
											'color' => sprintf('%02X%02X%02X', rand(0, 255), rand(0, 255), rand(0, 255))
									);
								}
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => 'localhost'),
									'content' => $content
								);
								break;
						}
					}
					break;
				case 'users':
					foreach ($modconf['graphs'] as $graph => $graphconf)
					{
						if (!$graphconf['used'])
						{
							continue;
						}
						switch ($graph)
						{
							case 'logged_in':
								$config['list'][] = array(
									'title' => $graphconf['title'],
									'lowerLimit' => 0,
									'altAutoscaleMax' => true,
									'categories' => array('host' => 'localhost'),
									'content' => array(
										array(
											'type' => 'AREA',
											'source' => $module,
											'ds' => 'users',
											'cf' => 'AVERAGE',
											'legend' => 'Users logged in',
											'color' => '4444DD'
										)
									)
								);
								break;
						}
					}
					break;
			}
		}
	}
	
	public static function sources(&$config, $simpleconfig)
	{
		if (!$simpleconfig['used'])
		{
			return;
		}
		foreach ($simpleconfig['modules'] as $module => $modconf)
		{
			if (!$modconf['used'])
			{
				continue;
			}
			switch ($module)
			{
				case 'apache':
					foreach ($modconf['hosts'] as $host)
					{
						$config[$module . '_' . rrd::escapeDsName($host)]['module'] = new apache('http://' . $host . '/server-status?auto');
					}
					break;
				case 'cpu':
					$config[$module]['module'] = new cpu();
					break;
				case 'disk':
					foreach ($modconf['disks'] as $disk)
					{
						$config[$module . '_' . rrd::escapeDsName($disk)]['module'] = new disk($disk);
					}
					break;
				case 'load':
					$config[$module]['module'] = new load();
					break;
				case 'memory':
					$config[$module]['module'] = new memory();
					break;
				case 'mysql':
					$config[$module]['module'] = new mysql($modconf['user'], $modconf['password'], $modconf['host']);
					break;
				case 'ping':
					foreach ($modconf['hosts'] as $host)
					{
						$config[$module . '_' . rrd::escapeDsName($host)]['module'] = new ping($host);
					}
					break;
				case 'ping_http':
					foreach ($modconf['hosts'] as $host)
					{
						$config[$module . '_' . rrd::escapeDsName($host)]['module'] = new ping_http($host);
					}
					break;
				case 'traffic':
					foreach ($modconf['chains'] as $chain)
					{
						$config[$module . '_' . rrd::escapeDsName($chain)]['module'] = new traffic($chain);
					}
					break;
				case 'traffic_proc':
					foreach ($modconf['interfaces'] as $interface)
					{
						$config[$module . '_' . rrd::escapeDsName($interface)]['module'] = new traffic_proc($interface);
					}
					break;
				case 'users':
					$config[$module]['module'] = new users();
					break;
			}
		}
	}
}

?>
