<?php

// All sources should be loaded on demand
function __autoload($class)
{
	require_once(SOURCEPATH . $class . '.php');
}

// Function to  draw the menu
function menu()
{
	global $config;
	if (!isset($config) || !isset($config['graphlist']))
	{
		return;
	}
	?><div id="menu">
	<ul>
	<li class="index"><a href="index.php">Übersicht</a></li>
	<?php
	foreach ($config['graphlist'] as $graphindex => $graph)
	{
		?>
		<li class="detail">
			<a href="detail.php?graph=<?php echo $graphindex; ?>"><?php echo $graph['title']; ?></a>
		</li>
		<?php
	}
	?>
	</ul>
	</div>
	<?php
}

?>
