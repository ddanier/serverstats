<?php

$config = array(
	array(
		'title' => 'Sample',
		// Here you see all supported contenttypes
		// Every type needs _exactly_ the options
		// shown here
		'content' => array(
			// Simple Options, no need to care about the DEFs
			array(
				'type' => 'line',
				'width' => 2, // may be 1, 2 or 3
				'source' => 'sample',
				'ds' => 's1',
				'cf' => 'AVERAGE',
				'legend' => 'Sample1\n',
				'color' => 'FF0000'
			),
			array(
				'type' => 'area',
				'source' => 'sample',
				'ds' => 's2',
				'cf' => 'AVERAGE',
				'legend' => 'Sample2',
				'color' => 'FF0000'
			),
			array(
				'type' => 'stack',
				'source' => 'sample',
				'ds' => 's3',
				'cf' => 'AVERAGE',
				'legend' => 'Sample3',
				'color' => 'FF0000'
			),
			array(
				'type' => 'hrule',
				'value' => 10,
				'legend' => 'Sample4',
				'color' => 'FF0000'
			),
			array(
				'type' => 'vrule',
				'time' => 1116659895,
				'legend' => 'Sample5',
				'color' => 'FF0000'
			),
			array(
				'type' => 'comment',
				'text' => 'Sample6'
			),
			array(
				'type' => 'gprint',
				'source' => 'sample',
				'ds' => 's3',
				'cf' => 'AVERAGE', // Must be set even if using own (C)DEFs
						   // see below
				'format' => '%lf'
			),
			// Advanced Options
			array( // Define own DEF
				'type' => 'def',
				'name' => 'vname1',
				'source' => 'sample',
				'ds' => 's4',
				'cf' => 'AVERAGE'
			),
			array( // Define own CDEF
				'type' => 'cdef',
				'name' => 'vname2',
				'expression' => 'vname1,2,/' // See 'man rrdgraph'
							     // vnames used here are not validated!
			),
			array( // Use own (C)DEFs
				'type' => 'line',
				'width' => 2,
				// 'source', 'ds' and 'cf' is replaced with:
				'name' => 'vname2',
				'legend' => 'Sample1',
				'color' => 'FF0000'
			),
			array( // Use own (C)DEF with GPRINT
			       // Attention: 'cf' MUST stay
				'type' => 'gprint',
				'name' => 'vname2',
				'cf' => 'AVERAGE',
				'format' => '%lf'
			)
		)
	)
);

?>
