<?php
/**
 * $Id: index.php 123 2006-04-11 12:35:34Z goliath $
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

// Load all needed classes, function and everything else
require_once('../init.php');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">  
<head>
	<title><?php echo lang::t('Statistics'); ?> - <?php echo lang::t('Summary'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script type="text/javascript" src="js/tree.js"></script>
        <script type="text/javascript" src="js/xml.js"></script>
        <script type="text/javascript" src="js/ajax.js"></script>
	<script type="text/javascript" >
		function loadgraphs(url) {
                        var myajax = new concentre.ajax(url,'get','');

                        myajax.progressIndicator = function () {
                                var content = document.getElementById('content');
                                content.className = 'progress';
                        }

                        myajax.callBack = function () {

                                var content = document.getElementById('content');
                                content.className = '';
                                content.innerHTML = this.http.responseText;

                        }

                        myajax.send();
		}

		window.onload = function () {
			if (document.getElementById && document.getElementsByTagName) {
			  var uls = document.getElementsByTagName('ul');
			  for (var i = 0; i < uls.length; i++) {
			   if (uls[i].className.indexOf('tree') != -1) {
			    tree(uls[i]);
			   }
			  }
 			}

			//loadgraphs ('home.php');
		}

		
	</script>
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen, projection" />
	<link charset="utf-8" href="css/tree.css" media="screen" rel="stylesheet" type="text/css">
</head>
<body class="index">
<div id="page">
<div id="header">
<h1><?php echo lang::t('Statistics'); ?> - <?php echo lang::t('Summary'); ?></h1>
</div>
<div id="main">
<div id="left">
<?php

require_once('tree.php');

?>
</div>
<div id="content">

</div>
</div>
<div id="footer">
serverstats v0.8, 2006
</div>
</div>
</body>
</html>
