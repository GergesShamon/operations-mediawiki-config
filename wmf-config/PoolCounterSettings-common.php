<?php

if ( !defined( 'MEDIAWIKI' ) ) exit( 1 );

include( "$IP/extensions/PoolCounter/PoolCounterClient.php" );

$wgPoolCounterConf = array(
	'ArticleView' => array(
		'class' => 'PoolCounter_Client',
		'timeout' => 15,
		'workers' => 2,
		'maxqueue' => 100
	),
	'CirrusSearch-Search' => array(
		'class' => 'PoolCounter_Client',
		'timeout' => 15,
		'workers' => 432,
		'maxqueue' => 600,
	),
	// Super common and mostly fast
	'CirrusSearch-Prefix' => array(
		'class' => 'PoolCounter_Client',
		'timeout' => 15,
		'workers' => 432,
		'maxqueue' => 600,
	),
	// Regex searches are much heavier then regular searches so we limit the
	// concurrent number.
	'CirrusSearch-Regex' => array(
		'class' => 'PoolCounter_Client',
		'timeout' => 60,
		'workers' => 10,
		'maxqueue' => 20,
	),
	// These should be very very fast and reasonably rare
	'CirrusSearch-NamespaceLookup' => array(
		'class' => 'PoolCounter_Client',
		'timeout' => 5,
		'workers' => 50,
		'maxqueue' => 200,
	),
	'FileRender' => array(
		'class' => 'PoolCounter_Client',
		'timeout' => 8,
		'workers' => 2,
		'maxqueue' => 100
	),
	'FileRenderExpensive' => array(
		'class' => 'PoolCounter_Client',
		'timeout' => 8,
		'workers' => 2,
		'slots' => 8,
		'maxqueue' => 100
	),
	'TranslateFetchTranslators' => array(
		'class' => 'PoolCounter_Client',
		'timeout' => 8,
		'workers' => 1,
		'slots' => 16,
		'maxqueue' => 20,
	),
	'CirrusSearch-PerUser' => array(
		'class' => 'PoolCounter_Client',
		'timeout' => 0,
		'workers' => 15,
		'maxqueue' => 15,
	),
);

require( getRealmSpecificFilename( "$wmfConfigDir/PoolCounterSettings.php" ) );
