<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 1 );
}

use MediaWiki\Extension\PoolCounter\Client;

wfLoadExtension( 'PoolCounter' );

$wgPoolCounterConf = [
	'ArticleView' => [
		'class' => Client::class,
		'timeout' => 15,
		'workers' => 2,
		'maxqueue' => 100,
		// T250248
		'fastStale' => true,
	],
	'CirrusSearch-Search' => [
		'class' => Client::class,
		'timeout' => 15,
		'workers' => 200,
		'maxqueue' => 200,
	],
	// Software tries to recognize sources of external automation, such as GAE,
	// AWS, browser automation, etc. and give them a separate pool so they
	// can cap out without interfering with interactive users.
	'CirrusSearch-Automated' => [
		'class' => PoolCounter_Client::class,
		'timeout' => 15,
		'workers' => 30,
		'maxqueue' => 35,
	],
	// Super common and mostly fast
	'CirrusSearch-Prefix' => [
		'class' => Client::class,
		'timeout' => 15,
		'workers' => 32,
		'maxqueue' => 40,
	],
	// Super common and mostly fast, replaces Prefix (eventually)
	'CirrusSearch-Completion' => [
		'class' => Client::class,
		'timeout' => 15,
		'workers' => 432,
		'maxqueue' => 450,
	],
	// Regex searches are much heavier then regular searches so we limit the
	// concurrent number.
	'CirrusSearch-Regex' => [
		'class' => Client::class,
		'timeout' => 60,
		'workers' => 10,
		'maxqueue' => 15,
	],
	// These should be very very fast
	'CirrusSearch-NamespaceLookup' => [
		'class' => Client::class,
		'timeout' => 5,
		'workers' => 100,
		'maxqueue' => 120,
	],
	// These are very expensive and incredibly common at more than 5M per hour
	// before varnish caching. If the somehow the cache hit rate drops this
	// protects the cluster
	// NOTE: This is an increase from typical sizing to handle the expected
	// empty more_like cache on switchover from eqiad->codfw.
	'CirrusSearch-MoreLike' => [
		'class' => Client::class,
		'timeout' => 5,
		'workers' => 150,
		'maxqueue' => 175,
	],
	'FileRender' => [
		'class' => Client::class,
		'timeout' => 8,
		'workers' => 2,
		'maxqueue' => 100
	],
	'FileRenderExpensive' => [
		'class' => Client::class,
		'timeout' => 8,
		'workers' => 2,
		'slots' => 8,
		'maxqueue' => 100
	],
	'SpecialContributions' => [
		'class' => Client::class,
		'timeout' => 15,
		'workers' => 2,
		'maxqueue' => 25,
	],
	'TranslateFetchTranslators' => [
		'class' => Client::class,
		'timeout' => 8,
		'workers' => 1,
		'slots' => 16,
		'maxqueue' => 20,
	],
	'WikiLambdaFunctionCall' => [
		'class' => Client::class,
		'timeout' => 1,
		'workers' => 2,
		'maxqueue' => 5,
		'slots' => 50,
	],
];

$wgPoolCountClientConf = [
	'servers' => $wmgLocalServices['poolcounter'],
	'timeout' => 0.5,
	'connect_timeout' => 0.01
];
