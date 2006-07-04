<?php
/**
 * $Id: simple.php 123 2006-04-11 12:35:34Z goliath $
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

//$config['used'] = true; // is the simple config used
$xslt = new XSLTProcessor();

$dom = new domDocument();
$dom->load(CONFIGPATH . 'xml/config.xml');
$dom->Xinclude();

function avt($matches) {
		extract($GLOBALS);               
 
		$entries = $xp->evaluate($matches[1], $child);
	        if ($entries instanceof DOMNodeList) {
			return $entries->item(0)->nodeValue;
		} else {
	                return $entries;
		}
        }

function __traversedom ( $node ) {
	global $child, $xml, $xp;

	foreach ($node->childNodes as $child) {
		if ($child->nodeType!=1) continue;

		$value = $child->getAttribute('value');
		if ($value) {
			$child->removeAttribute('value');
			$xp = new domxpath($child->ownerDocument);
			$result = preg_replace_callback('/\{([^\}]+)\}/','avt', $value);
			$child->appendChild($child->ownerDocument->createTextNode($result));
		}

		__traversedom ($child);
	}
}

$xsl = new domDocument();
$xsl -> load(CONFIGPATH . 'clean.xsl');
$xslt -> importStylesheet($xsl);

$xml = $xslt -> transformToDoc($dom);

__traversedom ($xml->documentElement);

$xmlconfig = xmlconfig::read($xml);
$config['modules'] = $xmlconfig['modules'];
$config['graphs'] = $xmlconfig['graphs'];
$config['used'] = true;

//print_r($xmlconfig);

?>
