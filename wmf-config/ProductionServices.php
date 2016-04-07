<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.

#######################################################################
# ProductionServices.php should include all the service hostnames/ips
# for any service used in production, divided by datacenter.
#
# It is included by CommonSettings.php
#
#######################################################################

$wmfAllServices = array();

$wmfAllServices['eqiad'] = array(
	'udp2log' => 'fluorine.eqiad.wmnet:8420',
	'statsd' => 'statsd.eqiad.wmnet',
	'search' => array( 'search.svc.eqiad.wmnet' ), # elasticsearch must be accessed by hostname for SSL certificate verification to work
	'ocg' => 'http://ocg.svc.eqiad.wmnet:8000',
	'urldownloader' => 'http://url-downloader.wikimedia.org:8080',
	'parsoid' => 'http://parsoid.svc.eqiad.wmnet:8000',
	'mathoid' => 'http://mathoid.svc.eqiad.wmnet:10042',
	'eventlogging' => 'udp://10.64.32.167:8421', # eventlog1001.eqiad.wmnet
	'eventbus' => 'http://eventbus.svc.eqiad.wmnet:8085',
	'upload' => 'upload.svc.eqiad.wmnet',
);

$wmfAllServices['codfw'] = array(
	'udp2log' => 'fluorine.eqiad.wmnet:8420',
	'statsd' => 'statsd.eqiad.wmnet',
	'search' => array( 'search.svc.codfw.wmnet' ), # elasticsearch must be accessed by hostname for SSL certificate verification to work
	'ocg' => 'http://ocg.svc.eqiad.wmnet:8000',
	'urldownloader' => 'http://url-downloader.wikimedia.org:8080',
	'parsoid' => 'http://parsoid.svc.eqiad.wmnet:8000', # Change this once parsoid is up and running in codfw
	'mathoid' => 'http://mathoid.svc.eqiad.wmnet:10042',
	'eventlogging' => 'udp://10.64.32.167:8421',  # eventlog1001.eqiad.wmnet,
	'eventbus' => 'http://eventbus.svc.eqiad.wmnet:8085',
	'upload' => 'upload.svc.codfw.wmnet',
);

### Logstash
$wmfAllServices['eqiad']['logstash'] = array(
	'10.64.0.122', // logstash1001.eqiad.wmnet
	'10.64.32.137', // logstash1002.eqiad.wmnet
	'10.64.48.113', // logstash1003.eqiad.wmnet
);
$wmfAllServices['codfw']['logstash'] = $wmfAllServices['eqiad']['logstash'];

### Analytics Kafka cluster
$wmfAllServices['eqiad']['kafka'] = array(
	'10.64.5.12:9092',   // kafka1012.eqiad.wmnet
	'10.64.5.13:9092',   // kafka1013.eqiad.wmnet
	'10.64.36.114:9092', // kafka1014.eqiad.wmnet
	'10.64.53.10:9092',  // kafka1018.eqiad.wmnet
	'10.64.53.12:9092',  // kafka1020.eqiad.wmnet
	'10.64.36.122:9092', // kafka1022.eqiad.wmnet
);
$wmfAllServices['codfw']['kafka'] = $wmfAllServices['eqiad']['kafka'];

### IRC
$wmfAllServices['eqiad']['irc'] = '208.80.154.160'; // eqiad: argon
$wmfAllServices['codfw']['irc'] = $wmfAllServices['eqiad']['irc'];

### Restbase
$wmfAllServices['eqiad']['restbase'] = 'http://10.2.2.17:7231'; # restbase.svc.eqiad.wmnet
$wmfAllServices['codfw']['restbase'] = 'http://10.2.1.17:7231'; # restbase.svc.codfw.wmnet

### Poolcounter
$wmfAllServices['eqiad']['poolcounter'] = array(
	'10.64.0.179', # helium.eqiad.wmnet
	'10.64.16.152', # potassium.eqiad.wmnet
);
$wmfAllServices['codfw']['poolcounter'] = array(
	'10.192.16.124', # subra.codfw.wmnet
	'10.192.0.121', # suhail.codfw.wmnet
);

### LockManager Redis
$wmfAllServices['eqiad']['redis_lock'] = array(
	'rdb1' => '10.64.0.180',
	'rdb2' => '10.64.0.181',
	'rdb3' => '10.64.0.182'
);
$wmfAllServices['codfw']['redis_lock'] = array(
	'rdb1' => '10.192.0.34',
	'rdb2' => '10.192.16.37',
	'rdb3' => '10.192.32.20',
);

### Jobqueue redis
// Note: for each host:port combination, the local fallback slave is
// the next server, so rdb1001:6379 => rdb1002:6379 and so on.
// Note: on server failure, partition masters should be switched to the slave
// Note: MediaWiki will fail-over to other shards when one is down. On master
// failure, it is best to either do nothing or just disable the whole shard
// until the master is fixed or full fail-over is done. Proper fail over
// requires changing the slave to stop slaving the old master before updating
// the MediaWiki config to direct traffic there.
// Do NOT remove entries from here if they are still present in 'partitionsBySection'
// in wmf-config/jobqueue.php
$wmfAllServices['eqiad']['jobqueue_redis'] = array(
	'rdb1-6379' => 'rdb1001.eqiad.wmnet:6379',
	'rdb1-6380' => 'rdb1001.eqiad.wmnet:6380',
	'rdb1-6381' => 'rdb1001.eqiad.wmnet:6381',
	'rdb2-6379' => 'rdb1003.eqiad.wmnet:6379',
	'rdb2-6380' => 'rdb1003.eqiad.wmnet:6380',
	'rdb2-6381' => 'rdb1003.eqiad.wmnet:6381',
	'rdb3-6379' => 'rdb1007.eqiad.wmnet:6379',
	'rdb3-6380' => 'rdb1007.eqiad.wmnet:6380',
	'rdb3-6381' => 'rdb1007.eqiad.wmnet:6381',
	'rdb4-6379' => 'rdb1005.eqiad.wmnet:6379',
	'rdb4-6380' => 'rdb1005.eqiad.wmnet:6380',
	'rdb4-6381' => 'rdb1005.eqiad.wmnet:6381',
);
$wmfAllServices['codfw']['jobqueue_redis'] = array(
	'rdb1-6379' => 'rdb2001.eqiad.wmnet:6379',
	'rdb1-6380' => 'rdb2001.eqiad.wmnet:6380',
	'rdb1-6381' => 'rdb2001.eqiad.wmnet:6381',
	'rdb2-6379' => 'rdb2003.eqiad.wmnet:6379',
	'rdb2-6380' => 'rdb2003.eqiad.wmnet:6380',
	'rdb2-6381' => 'rdb2003.eqiad.wmnet:6381',
	'rdb3-6379' => 'rdb2005.eqiad.wmnet:6479',
	'rdb3-6380' => 'rdb2005.eqiad.wmnet:6480',
	'rdb3-6381' => 'rdb2005.eqiad.wmnet:6481',
	'rdb4-6379' => 'rdb2005.eqiad.wmnet:6379',
	'rdb4-6380' => 'rdb2005.eqiad.wmnet:6380',
	'rdb4-6381' => 'rdb2005.eqiad.wmnet:6381',
);
$wmfAllServices['eqiad']['jobqueue_aggregator'] = array(
	'rdb1001.eqiad.wmnet:6378', // preferred
	'rdb1003.eqiad.wmnet:6378', // fallback
	'rdb1005.eqiad.wmnet:6378', // fallback
	'rdb1007.eqiad.wmnet:6378', // fallback
);
$wmfAllServices['codfw']['jobqueue_aggregator'] = array(
	'rdb2001.eqiad.wmnet:6378', // preferred
	'rdb2003.eqiad.wmnet:6378', // fallback
	'rdb2005.eqiad.wmnet:6378', // fallback
);


# Shorthand when we have no master-slave situation to keep into account
$wmfLocalServices = $wmfAllServices[$wmfDatacenter];

$wmfMasterServices = $wmfAllServices[$wmfMasterDatacenter];
