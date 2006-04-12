<?php
/**
 * $Id$
 *
 * Author: David Danier, david.danier@team23.de
 * Project: Serverstats, http://serverstats.berlios.de/
 * License: LGPL v2.1 or later (http://www.gnu.org/licenses/lgpl.html)
 *
 * Copyright (C) 2005 David Danier
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */

class rrdgraph
{
	private $rrdtoolbin;
	private $title;
	private $start;
	private $end;
	private $width = 500;
	private $height = 150;
	private $format = 'PNG';
	
	private $base;
	private $upperLimit;
	private $lowerLimit;
	private $verticalLabel;
	private $unitsExponent;
	private $altYMrtg = false;
	private $altAutoscale = false;
	private $altAutoscaleMax = false;
	private $lazy = false;
	
	private $content = array();
	private $defs = array();
	
	public function __construct($rrdtoolbin, $start, $end = null)
	{
		$this->rrdtoolbin = $rrdtoolbin;
		$this->start = $start;
		$this->end = $end;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	public function setWidth($width)
	{
		$this->width = $width;
	}
	
	public function setHeight($height)
	{
		$this->height = $height;
	}

	public static function escape($text)
	{
		return str_replace(':', '\\:', $text);
	}
	
	public function add($type, $p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = null, $p6 = null, $p7 = null, $p8 = null)
	{
		switch ($type)
		{
			case 'DEF':
				// DEF:<vname>=<rrdfile>:<dsname>:<CF>[:step=<step>][:start=<time>][:end=<time>][:reduce=<CF>]
				if (!(isset($p1) && isset($p2) && isset($p3) && isset($p4)))
				{
					throw new Exception('Wrong Paramcount for ' . $type);
				}
				if (isset($this->defs[$p1]))
				{
					throw new Exception('Name already in use');
				}
				$this->content[] = array(
					'type' => $type,
					'cf' => $p4,
					'vname' => $p1,
					'ds' => $p3,
					'rrdfile' => $p2,
					'start' => $p6,
					'step' => $p5,
					'end' => $p7,
					'reduce' => $p8
				);
				$this->defs[$p1] = $type;
				break;
			case 'CDEF':
				// CDEF:vname=RPN expression
			case 'VDEF':
				// VDEF:vname=RPN expression
				if (!(isset($p1) && isset($p2)))
				{
					throw new Exception('Wrong Paramcount for ' . $type);
				}
				if (isset($this->defs[$p1]))
				{
					throw new Exception('Name already in use');
				}
				$this->content[] = array(
					'type' => $type,
					'vname' => $p1,
					'expression' => $p2
				);
				$this->defs[$p1] = $type;
				break;
			case 'LINE':
				// LINE[width]:value[#color][:[legend][:STACK]]
				if (!isset($p2))
				{
					throw new Exception('Wrong Paramcount for ' . $type);
				}
				if (!isset($p5))
				{
					$p5 = false;
				}
				$this->content[] = array(
					'type' => $type,
					'width' => $p1,
					'vname' => $p2,
					'color' => $p3,
					'legend' => $p4,
					'stacked' => $p5
				);
				break;
			case 'AREA':
				// AREA:value[#color][:[legend][:STACK]]
				if (!isset($p1))
				{
					throw new Exception('Wrong Paramcount for ' . $type);
				}
				if (!isset($p4))
				{
					$p4 = false;
				}
				$this->content[] = array(
					'type' => $type,
					'vname' => $p1,
					'color' => $p2,
					'legend' => $p3,
					'stacked' => $p4
				);
				break;
			case 'TICK':
				// TICK:vname#rrggbb[aa][:fraction[:legend]]
				if (!(isset($p1) && isset($p2)))
				{
					throw new Exception('Wrong Paramcount for ' . $type);
				}
				$this->content[] = array(
					'type' => $type,
					'vname' => $p1,
					'color' => $p2,
					'fraction' => $p3,
					'legend' => $p4
				);
				break;
			case 'VRULE':
				// VRULE:time#color[:legend]
				if (!(isset($p1) && isset($p2)))
				{
					throw new Exception('Wrong Paramcount for ' . $type);
				}
				$this->content[] = array(
					'type' => $type,
					'time' => $p1,
					'color' => $p2,
					'legend' => $p3
				);
				break;
			case 'GPRINT':
				// GPRINT:vname:format
				if (!(isset($p1) && isset($p2)))
				{
					throw new Exception('Wrong Paramcount for ' . $type);
				}
				$this->content[] = array(
					'type' => $type,
					'vname' => $p1,
					'format' => $p2
				);
				break;
			case 'COMMENT':
				// COMMENT:text
				if (!isset($p1))
				{
					throw new Exception('Wrong Paramcount for ' . $type);
				}
				$this->content[] = array(
					'type' => $type,
					'text' => $p1
				);
				break;
			case 'SHIFT':
				// SHIFT:vname:offset
				if (!(isset($p1) && isset($p2)))
				{
					throw new Exception('Wrong Paramcount for ' . $type);
				}
				$this->content[] = array(
					'type' => $type,
					'vname' => $p1,
					'offset' => $p2
				);
				break;
			default:
				throw new Exception('Unknown Graphcontent ' . $type);
				break;
		}
	}
	
	public function addDEF($vname, $rrdfile, $ds, $cf, $step = null, $start = null, $end = null, $reduce = null)
	{
		$this->add('DEF', $vname, $rrdfile, $ds, $cf, $step, $start, $end, $reduce);
	}
	
	public function addCDEF($vname, $expression)
	{
		$this->add('CDEF', $vname, $expression);
	}
	
	public function addVDEF($vname, $expression)
	{
		$this->add('VDEF', $vname, $expression);
	}
	
	public function addLINE($width = null, $vname, $color = null, $legend = null, $stacked = false)
	{
		$this->add('LINE', $width, $vname, $color, $legend, $stacked);
	}
	
	public function addAREA($vname, $color = null, $legend = null, $stacked = false)
	{
		$this->add('AREA', $vname, $color, $legend, $stacked);
	}
	
	public function addTICK($vname, $color, $fraction = null, $legend = null)
	{
		$this->add('TICK', $vname, $color, $fraction, $legend);
	}
	
	public function addSHIFT($vname, $offset)
	{
		$this->add('SHIFT', $vname, $offset);
	}
	
	public function addGPRINT($vname, $format)
	{
		$this->add('GPRINT', $vname, $format);
	}
	
	public function addVRULE($time, $color, $legend = null)
	{
		$this->add('VRULE', $time, $color, $legend);
	}
	
	public function addCOMMENT($text)
	{
		$this->add('COMMENT', $text);
	}
	
	private function command($file = '-')
	{
		$params = ' graph ' . escapeshellarg($file);
		if (!empty($this->title))
		{
			$params .= ' -t ' . escapeshellarg($this->title);
		}
		$params .= ' -s ' . escapeshellarg($this->start);
		if (isset($this->end))
		{
			$params .= ' -e ' . escapeshellarg($this->end);
		}
		$params .= ' -a ' . escapeshellarg($this->format);
		$params .= ' -w ' . escapeshellarg($this->width);
		$params .= ' -h ' . escapeshellarg($this->height);
		if (isset($this->base))
		{
			$params .= ' -b ' . escapeshellarg($this->base);
		}
		if (isset($this->upperLimit))
		{
			$params .= ' -u ' . escapeshellarg($this->upperLimit);
		}
		if (isset($this->lowerLimit))
		{
			$params .= ' -l ' . escapeshellarg($this->lowerLimit);
		}
		if (isset($this->verticalLabel))
		{
			$params .= ' -v ' . escapeshellarg($this->verticalLabel);
		}
		if (isset($this->unitsExponent))
		{
			$params .= ' -X ' . escapeshellarg($this->unitsExponent);
		}
		if ($this->altYMrtg)
		{
			$params .= ' -R ';
		}
		if ($this->altAutoscale)
		{
			$params .= ' -A ';
		}
		if ($this->altAutoscaleMax)
		{
			$params .= ' -M ';
		}
		if ($this->lazy)
		{
			$params .= ' -z ';
		}
		foreach ($this->content as $c)
		{
			$optline = $c['type'];
			switch ($c['type'])
			{
				case 'DEF':
					$optline .= ':' . $c['vname'] . '=' . $c['rrdfile'] . ':' . $c['ds'] . ':' . $c['cf'];
					if (isset($c['start']))
					{
						$optline .= ':start=' . $c['start'];
					}
					if (isset($c['step']))
					{
						$optline .= ':step=' . $c['step'];
					}
					if (isset($c['end']))
					{
						$optline .= ':end=' . $c['end'];
					}
					if (isset($c['reduce']))
					{
						$optline .= ':reduce=' . $c['reduce'];
					}
					break;
				case 'CDEF':
				case 'VDEF':
					$optline .= ':' . $c['vname'] . '=' . $c['expression'];
					break;
				case 'LINE':
					if (isset($c['width']))
					{
						$optline .= $c['width'];
					}
					$optline .= ':' . $c['vname'];
					if (isset($c['color']))
					{
						$optline .= '#' . $c['color'];
					}
					if (isset($c['legend']))
					{
						$optline .= ':' . $c['legend'];
					}
					if ($c['stacked'])
					{
						$optline .= ':STACK';
					}
					break;
				case 'AREA':
					$optline .= ':' . $c['vname'];
					if (isset($c['color']))
					{
						$optline .= '#' . $c['color'];
					}
					if (isset($c['legend']))
					{
						$optline .= ':' . $c['legend'];
					}
					if ($c['stacked'])
					{
						$optline .= ':STACK';
					}
					break;
				case 'GPRINT':
					$optline .= ':' . $c['vname'] . ':' . $c['format'];
					break;
				case 'VRULE':
					$optline .= ':' . $c['time'] . '#' . $c['color'];
					if (isset($c['legend']))
					{
						$optline .= ':' . $c['legend'];
					}
					break;
				case 'COMMENT':
					$optline .= ':' . $c['text'];
					break;
				default:
					throw new Exception('NOT IMPLEMENTED');
					break;
			}
			$params .= ' ' . escapeshellarg($optline);
		}
		return (escapeshellcmd($this->rrdtoolbin) . $params);
	}
	
	public function save($file)
	{
		$output = array();
		$return = 0;
		$command = $this->command($file);
		exec($command . ' 2>&1', $output, $return);
		if ($return != 0)
		{
			throw new Exception('rrdtool ("' . $command . '") finished with exitcode ' . $return . "\n" . implode("\n", $output));
		}
	}
	
	public function output()
	{
		$return = 0;
		$command = $this->command();
		passthru($command, $return);
		if ($return != 0)
		{
			throw new Exception('rrdtool ("' . $command . '") finished with exitcode ' . $return);
		}
	}
	
	public function setBase($base)
	{
		$this->base = $base;
	}
	
	public function setUpperLimit($upperLimit)
	{
		$this->upperLimit = $upperLimit;
	}
	
	public function setLowerLimit($lowerLimit)
	{
		$this->lowerLimit = $lowerLimit;
	}
	
	public function setVerticalLabel($verticalLabel)
	{
		$this->verticalLabel = $verticalLabel;
	}
	
	public function setUnitsExponent($unitsExponent)
	{
		$this->unitsExponent = $unitsExponent;
	}
	
	public function setAltYMrtg($altYMrtg = true)
	{
		$this->altYMrtg = $altYMrtg;
	}
	
	public function setAltAutoscale($altAutoscale = true)
	{
		$this->altAutoscale = $altAutoscale;
	}
	
	public function setAltAutoscaleMax($altAutoscaleMax = true)
	{
		$this->altAutoscaleMax = $altAutoscaleMax;
	}
	
	public function setLazy($lazy = true)
	{
		$this->lazy = $lazy;
	}
}

?>
