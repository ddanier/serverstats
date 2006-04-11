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

class logger_file extends logger
{
	private $file;
	private $fh;
	
	public function __construct($file)
	{
		$this->file = $file;
	}
	
	public function __destruct()
	{
		if (isset($this->fh)) 
		{
			fclose($this->fh);
		}
	}
	
	private function getFileHandler()
	{
		if (!isset($this->fh)) 
		{
			if (!$this->fh = fopen($this->file, 'a+'))
			{
				throw new Exception('Could not open file (' . $this->file . ') for errorlogging');
			}
		}
		return $this->fh;
	}
	
	private function writeToFile($loglevel, $string)
	{
		$date = date('r');
		fwrite($this->getFileHandler(), $date . ' (' . logger::levelToString($loglevel) . '): ' . $string . "\n");
	}
	
	public function logString($loglevel, $string)
	{
		if (!logger::needsLogging($loglevel)) return;
		$this->writeToFile($loglevel, $string);
	}
	
	public function logException($loglevel, Exception $exception)
	{
		if (!logger::needsLogging($loglevel)) return;
		$this->writeToFile($loglevel, $exception->__toString());
	}
}

?>
