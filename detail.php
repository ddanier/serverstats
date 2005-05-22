<?php

// Load all needed classes, function and everything else
require_once('init.php');

// Init Vars used in this script
if (!isset($_GET['graph']) || ! isset($config['graphlist'][$_GET['graph']]))
{
	die('Graph not found');
}
$graphindex = $_GET['graph'];
$graph = $config['graphlist'][$graphindex];

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">  
<head>
	<title>Statistik: <?php echo $graph['title']; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="style.css" type="text/css" media="screen, projection" />
</head>
<body class="detail">
<?php

// Show the menu
menu();

?>
<h1>Statistik: <?php echo $graph['title']; ?></h1>
<?php

// List all configured graphs
foreach ($config['graphtypes'] as $graphtype)
{
	?>
	<h2><?php echo $graphtype['title']; ?></h2>
	<img src="graph.php?graph=<?php echo $graphindex; ?>&period=<?php echo $graphtype['period']; ?>&title=<?php echo $graphtype['title']; ?>&usecache=<?php echo $config['graph']['usecache']; ?>" alt="<?php echo $graphtype['title']; ?>" />
	<?php
}
?>
</body>
</html>
