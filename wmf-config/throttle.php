<?php
# WARNING: This file is publicly viewable on the web. Do not put private data here.

# Initialize the array. Append to that array to add a throttle
$wmgThrottlingExceptions = [];

# $wmgThrottlingExceptions is an array of arrays of parameters:
#  'from'  => date/time to start raising account creation throttle
#  'to'    => date/time to stop
#
# Optional arguments can be added to set the value or restrict by client IP
# or project dbname. Options are:
#  'value'  => new value for $wgAccountCreationThrottle (default: 50)
#  'IP'     => client IP as given by $wgRequest->getIP() or array (default: any IP)
#  'range'  => alternatively, the client IP CIDR ranges or array (default: any range)
#  'dbname' => a $wgDBname or array of dbnames to compare to
#             (eg. enwiki, metawiki, frwikibooks, eswikiversity)
#             Note that the limit is for the total number of account
#             creations on all projects. (default: any project)
# Example:
# $wmgThrottlingExceptions[] = [
# 'from'   => '2016-01-01T00:00 +0:00',
# 'to'     => '2016-02-01T00:00 +0:00',
# 'IP'     => '123.456.78.90',
# 'dbname' => [ 'xxwiki', etc. ],
# 'value'  => xx
# ];
## Add throttling definitions below.
#
## If you are adding a throttle exception with a 'from' time that is less than
## 72 hours in advance, you will also need to manually clear a cache after
## deploying your change to this file!
## https://wikitech.wikimedia.org/wiki/Increasing_account_creation_threshold

// T319212
$wmgThrottlingExceptions[] = [
	'from' => '2022-10-06T0:00 +2:00',
	'to' => '2022-10-06T23:59 +2:00',
	'IP' => '195.113.145.2',
	'dbname' => [ 'cswiki' ],
	'value' => 20,
];

// T319244
$wmgThrottlingExceptions[] = [
	'from' => '2022-10-13T10:00 +1:00',
	'to' => '2022-10-13T12:00 +1:00',
	'range' => '185.153.192.0/26',
	'dbname' => [ 'cswiki', 'commonswiki' ],
	'value' => 15,
];

// T324105
$wmgThrottlingExceptions[] = [
	'from' => '2022-11-30T0:00 +1:00',
	'to' => '2022-11-30T23:59 +1:00',
	'IP' => [ '83.208.68.147', '195.113.244.93' ],
	'dbname' => [ 'cswiki', 'skwiki', 'enwiki', 'commonswiki' ],
	'value' => 25,
];

## Add throttling definitions above.
