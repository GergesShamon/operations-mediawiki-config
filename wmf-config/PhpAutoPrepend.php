<?php
# WARNING: This file is publicly viewable on the web. Do not put private data here.

/**
 * Initialisation code for all PHP processes.
 *
 * This for PRODUCTION.
 *
 * PHP is configured to execute this file before the main script. This uses the
 * `auto_prepend_file` setting. This is currently enabled on web requests
 * only, not on Maintenance/CLI processes.
 *
 * This is executed in the same run-time as the main script, which means
 * it CAN expose state, such as variables and constants.
 *
 * However, as it runs literally before anything else, it cannot use
 * any MediaWiki state and no wmf-config or private configuration
 * variables.
 *
 * @see https://www.php.net/manual/en/ini.core.php#ini.auto-prepend-file
 */

// Open logs and set the syslog.ident to a sensible value on php-fpm
// See https://phabricator.wikimedia.org/T211184 for a discussion
if ( PHP_SAPI === 'fpm-fcgi' ) {
	openlog( 'php7.2-fpm', LOG_ODELAY, LOG_DAEMON );
}

// https://phabricator.wikimedia.org/T180183
require_once __DIR__ . '/../src/Profiler.php';
require_once __DIR__ . '/../src/ServiceConfig.php';

$wmgServiceConfig = Wikimedia\MWConfig\ServiceConfig::getInstance();

Wikimedia\MWConfig\Profiler::setup( [
	'redis-host' => $wmgServiceConfig->getLocalService( 'xenon' ),
	'redis-port' => 6379,
	// Connection timeout, in seconds.
	'redis-timeout' => $wmgServiceConfig->getRealm() === 'labs' ? 1 : 0.1,
	'xhgui-conf' => $wmgServiceConfig->getLocalService( 'xhgui-pdo' )
		? [
			'pdo.connect' => $wmgServiceConfig->getLocalService( 'xhgui-pdo' ),
			'pdo.table' => 'xhgui',
		]
		: null,
	'statsd-host' => $wmgServiceConfig->getLocalService( 'statsd' ),
	'excimer-ui-url' => $wmgServiceConfig->getLocalService( 'excimer-ui-url' ),
	'excimer-ui-server' => $wmgServiceConfig->getLocalService( 'excimer-ui-server' ),
] );
