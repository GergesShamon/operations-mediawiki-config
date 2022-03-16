<?php
# WARNING: This file is publicly viewable on the web. Do not put private data here.

# This file holds the CirrusSearch configuration which is common to all realms,
# i.e. settings that apply to both production and Beta Cluster.
#
# NOTE: Included for all wikis.
#
# Load tree:
#  |-- wmf-config/CommonSettings.php
#      |
#      `-- wmf-config/CirrusSearch-common.php
#
# If you need to reference an IP address, use the realm-specific
# files: CirrusSearch-labs.php, or CirrusSearch-production.php
#
# See: https://wikitech.wikimedia.org/wiki/Search
#
# Contact Wikimedia Operations or Wikimedia Search Platform for more details.

# Set the backoff for Cirrus' job that reacts to template changes - slow and steady
# will help prevent spikes in Elasticsearch load.
// $wgJobBackoffThrottling['cirrusSearchLinksUpdate'] = 5;  -- disabled, Ori 3-Dec-2015
# Also engage a delay for the Cirrus job that counts incoming links to pages when
# pages are newly linked or unlinked.  Too many link count queries at once could flood
# Elasticsearch.
// $wgJobBackoffThrottling['cirrusSearchIncomingLinkCount'] = 1; -- disabled, Ori 3-Dec-2015

// TextCat models
$wgCirrusSearchTextcatModel = [
	"$IP/vendor/wikimedia/textcat/LM-query",
	"$IP/vendor/wikimedia/textcat/LM",
];

$wgHooks['CirrusSearchMappingConfig'][] = static function ( array &$config, $mappingConfigBuilder ) {
	$config['page']['properties']['popularity_score'] = [
		'type' => 'double',
	];
};

// PoolCounter needs to be adjusted to account for additional latency when default search
// is pointed at a remote datacenter. Currently this makes the assumption that it will either
// be eqiad or codfw which have ~40ms latency between them. Multiples are chosen using
// (p75 + cross dc latency)/p75
if ( $wgCirrusSearchDefaultCluster !== $wmgDatacenter ) {
	// prefix has p75 of ~30ms
	if ( isset( $wgPoolCounterConf[ 'CirrusSearch-Prefix' ] ) ) {
		$wgPoolCounterConf['CirrusSearch-Prefix']['workers'] *= 2;
	}
	// namespace has a p75 of ~15ms
	if ( isset( $wgPoolCounterConf['CirrusSearch-NamespaceLookup' ] ) ) {
		$wgPoolCounterConf['CirrusSearch-NamespaceLookup']['workers'] *= 3;
	}
	// completion has p75 of ~30ms
	if ( isset( $wgPoolCounterConf['CirrusSearch-Completion'] ) ) {
		$wgPoolCounterConf['CirrusSearch-Completion']['workers'] *= 2;
	}
}

if ( $wmgCirrusSearchMLRModel ) {
	foreach ( $wmgCirrusSearchMLRModel as $name => $mlrModel ) {
		// LTR Rescore profile
		$wgCirrusSearchRescoreProfiles[$name] = [
			'i18n_msg' => 'cirrussearch-qi-profile-wsum-inclinks-pv',
			'supported_namespaces' => 'content',
			'supported_syntax' => [ 'simple_bag_of_words' ],
			'fallback_profile' => $wmgCirrusSearchMLRModelFallback,
			'rescore' => [
				[
					'window' => 8192,
					'query_weight' => 1.0,
					'rescore_query_weight' => 1.0,
					'score_mode' => 'total',
					'type' => 'function_score',
					'function_chain' => 'wsum_inclinks_pv'
				],
				[
					'window' => 8192,
					'query_weight' => 1.0,
					'rescore_query_weight' => 1.0,
					'score_mode' => 'multiply',
					'type' => 'function_score',
					'function_chain' => 'optional_chain'
				],
				[
					'window' => $mlrModel['window'] ?? 1024,
					'query_weight' => 1.0,
					'rescore_query_weight' => 10000.0,
					'score_mode' => 'total',
					'type' => 'ltr',
					// assumes mlrModel is a string if not defined
					'model' => $mlrModel['model'] ?? $mlrModel,
				],
			],
		];
	}
}

# Load per realm specific configuration, one of:
# - CirrusSearch-labs.php
# - CirrusSearch-production.php
require __DIR__ . "/CirrusSearch-{$wmgRealm}.php";
