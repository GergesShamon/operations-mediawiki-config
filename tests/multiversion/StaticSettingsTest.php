<?php
// Ensure that we're not casting any types
declare( strict_types = 1 );

use Wikimedia\MWConfig\MWConfigCacheGenerator;

/**
 * Really tests the settings retrieved from wmfGetVariantSettings, but no easy way to mark that
 *
 * @covers wmfGetVariantSettings
 */
class StaticSettingsTest extends PHPUnit\Framework\TestCase {

	/**
	 * @var array[] keys are the names of settings, values are arrays mapping wiki names
	 *   to configured setting values
	 */
	protected $variantSettings = [];
	private $originalWmfDC;

	public function setUp(): void {
		// This global is set by multiversion/MWRealm.php
		$this->originalWmfDC = $GLOBALS['wmgDatacenter'];
		$GLOBALS['wmgDatacenter'] = 'testvalue';

		$this->variantSettings = MWConfigCacheGenerator::getStaticConfig();
	}

	public function tearDown(): void {
		$GLOBALS['wmgDatacenter'] = $this->originalWmfDC;
	}

	public function testConfigIsScalar() {
		foreach ( $this->variantSettings as $variantSetting => $settingsArray ) {
			$this->assertTrue( is_array( $settingsArray ), "Each variant setting set must be an array, but $variantSetting is not" );

			foreach ( $settingsArray as $wiki => $value ) {
				$this->assertTrue(
					is_scalar( $value ) || is_array( $value ) || $value === null,
					"Each variant setting must be scalar, an array, or null, but $variantSetting is not for $wiki."
				);
			}
		}
	}

	public function testVariantUrlsAreLocalhost() {
		$knownToContainExternalURLs = [
			// These are user-facing, not in-cluster, and are fine
			'wgCanonicalServer', 'wgServer', 'wgUploadPath', 'wgRedirectSources', 'wgUploadNavigationUrl', 'wgScorePath', 'wgUploadMissingFileUrl', 'wgRightsUrl', 'wgWelcomeSurveyPrivacyPolicyUrl', 'wgWBCitoidFullRestbaseURL', 'wgGlobalRenameDenylist', 'wgWMEClientErrorIntakeURL', 'wgEventLoggingServiceUri',
			// FIXME: Just… wow. By name, this should be a boolean.
			'wmgUseFileExporter',
			// FIXME: Just set in wikibase.php? Most of these are user-facing.
			'wgEntitySchemaShExSimpleUrl', 'wmgWBRepoSettingsSparqlEndpoint', 'wmgWikibaseClientRepoUrl', 'wmgWikibaseClientPropertyOrderUrl', 'wgArticlePlaceholderRepoApiUrl', 'wgMediaInfoExternalEntitySearchBaseUri', 'wgMediaSearchExternalEntitySearchBaseUri', 'wmgWikibaseSSRTermboxServerUrl', 'wmgWikibaseClientDataBridgeHrefRegExp',
		];

		foreach ( $this->variantSettings as $variantSetting => $settingsArray ) {
			if ( in_array( $variantSetting, $knownToContainExternalURLs ) ) {
				// Skip for now.
				continue;
			}

			foreach ( $settingsArray as $wiki => $value ) {
				if ( !is_string( $value ) ) {
					continue;
				}

				$this->assertFalse(
					strpos( $value, '//' ) !== false && strpos( $value, 'localhost' ) === false,
					"Variant URLs must point to localhost, or be defined in CommonSettings, but $variantSetting for $wiki is '$value'."
				);
			}
		}
	}

	public function testUseFlagsAreBoolean() {
		$knownToBeBad = [
			'wgCirrusSearchUseCompletionSuggester',
			'wgCirrusSearchUseIcuFolding',
			'wgMFUseDesktopSpecialHistoryPage',
			"wgMFUseDesktopSpecialWatchlistPage",
			'wmgUseCognate',
			'wmgUseFileExporter',
			'wmgUseFileImporter',
		];

		foreach ( $this->variantSettings as $variantSetting => $settingsArray ) {
			if ( preg_match( '/Use[A-Z]/', $variantSetting ) ) {
				if ( in_array( $variantSetting, $knownToBeBad ) ) {
					// Skip for now.
					continue;
				}

				foreach ( $settingsArray as $wiki => $value ) {
					$this->assertTrue(
						is_bool( $value ),
						"Use flags should be boolean, but $variantSetting for $wiki is " . ( is_array( $value ) ? "an array" : "'" . (string)$value . "'" ) . "."
					);
				}
			}
		}
	}

	public function testLogos() {
		$scalarLogoKeys = [
			'1x' => 'wmgSiteLogo1x',
			'1.5x' => 'wmgSiteLogo1_5x',
			'2x' => 'wmgSiteLogo2x',
			'icon' => 'wmgSiteLogoIcon',
		];

		$pairedSizes = [ '1.5x', '2x' ];

		// Test if all scalar logos exist
		foreach ( $scalarLogoKeys as $size => $key ) {
			foreach ( $this->variantSettings[ $key ] as $db => $entry ) {
				$this->assertFileExists( __DIR__ . '/../..' . $entry, "$db has non-existent $size logo in $key" );

				if ( in_array( $size, $pairedSizes ) ) {
					$otherSize = array_values( array_diff( $pairedSizes, [ $size ] ) )[ 0 ];
					$otherKey = $scalarLogoKeys[ $otherSize ];

					$this->assertArrayHasKey(
						$db,
						$this->variantSettings[ $otherKey ],
						"$db has a logo set for $size in $key but not for $otherSize in $otherKey"
					);

					$baseKey = $scalarLogoKeys['1x'];

					$this->assertArrayHasKey(
						$db,
						$this->variantSettings[ $baseKey ],
						"$db has an over-ride HD logo set for $size in $key but not for regular resoltion in $baseKey"
					);

					// Test if 2x and 1.5x is really of correct size
					// Tolerate up to 5 px difference
					$imagesizeOne = getimagesize( __DIR__ . '/../..' . $this->variantSettings[ $scalarLogoKeys[ '1x' ] ][ $db ] )[0];
					$imagesizeOneAndHalf = getimagesize( __DIR__ . '/../..' . $this->variantSettings[ $scalarLogoKeys[ '1.5x' ] ][ $db ] )[0];
					$imagesizeTwo = getimagesize( __DIR__ . '/../..' . $this->variantSettings[ $scalarLogoKeys[ '2x' ] ][ $db ] )[0];

					// Remove this exception as soon as the logos are updated to meet the condition
					if ( !in_array( $db, [
						'hiwiki',
						'cawikiquote', 'enwikiquote', 'eowikiquote', 'eswikiquote', 'hrwikiquote', 'hywikiquote', 'knwikiquote', 'slwikiquote', 'srwikiquote',
						'zhwikinews',
						'ruwikivoyage', 'zhwikivoyage'
					] ) ) {
						$this->assertTrue(
							$imagesizeOneAndHalf >= (int)( $imagesizeOne * 1.5 - 5 ),
							"$db has 1.5x HD logo of $imagesizeOneAndHalf width, at least " . (int)( $imagesizeOne * 1.5 ) . " expected"
						);
					}

					// Remove this exception as soon as the logo is updated to meet the condition
					if ( $db !== 'zhwikivoyage' ) {
						$this->assertTrue(
							$imagesizeTwo >= $imagesizeOne * 2 - 5,
							"$db has 2x HD logo of $imagesizeTwo width, at least " . $imagesizeOne * 2 . " expected"
						);
					}

				}
			}
		}

		// Test if all wordmark logo values are set and the file exists
		foreach ( $this->variantSettings[ 'wmgSiteLogoWordmark' ] as $db => $entry ) {
			if ( !count( $entry ) ) {
				// Wordmark logo over-ridden to unset.
				continue;
			}
			$this->assertArrayHasKey( 'src', $entry, "$db has no path set for its wordmark logo in wmgSiteLogoWordmark" );
			$this->assertFileExists( __DIR__ . '/../..' . $entry['src'], "$db has non-existent wordmark logo in wmgSiteLogoWordmark" );
			$this->assertArrayHasKey( 'width', $entry, "$db has no width set for its wordmark logo in wmgSiteLogoWordmark" );
			$this->assertArrayHasKey( 'height', $entry, "$db has no height set for its wordmark logo in wmgSiteLogoWordmark" );
		}
	}

	public function testwgExtraNamespaces() {
		foreach ( $this->variantSettings['wgExtraNamespaces'] as $db => $entry ) {
			foreach ( $entry as $number => $namespace ) {
				// Test for invalid spaces
				$this->assertStringNotContainsString( ' ', $namespace, "Unexpected space in '$number' namespace title for $db, use underscores instead" );

				// Test for invalid colons
				$this->assertStringNotContainsString( ':', $namespace, "Unexpected colon in '$number' namespace title for $db, final colon is not needed and can be removed" );

				// Test namespace numbers
				if ( $number < 100 || in_array( $number, [ 828, 829 ] ) ) {
					continue; // It's not an extra namespace, do not test
				}
				if ( $number % 2 == 0 ) {
					$this->assertArrayHasKey( $number + 1, $entry, "Namespace $namespace (ID $number) for $db doesn't have corresponding talk namespace set" );
				} else {
					$this->assertArrayHasKey( $number - 1, $entry, "Namespace $namespace (ID $number) for $db doesn't have corresponding non-talk namespace set" );
				}
			}
		}
	}

	public function testMetaNamespaces() {
		foreach ( $this->variantSettings['wgMetaNamespace'] as $db => $namespace ) {
			// Test for invalid spaces
			$this->assertStringNotContainsString( ' ', $namespace, "Unexpected space in meta namespace title for $db, use underscores instead" );

			// Test for invalid colons
			$this->assertStringNotContainsString( ':', $namespace, "Unexpected colon in meta namespace title for $db, final colon is not needed and should be removed" );
		}

		foreach ( $this->variantSettings['wgMetaNamespaceTalk'] as $db => $namespace ) {
			// Test for invalid spaces
			$this->assertStringNotContainsString( ' ', $namespace, "Unexpected space in meta talk namespace title for $db, use underscores instead" );

			// Test for invalid colons
			$this->assertStringNotContainsString( ':', $namespace, "Unexpected colon in meta talk namespace title for $db, final colon is not needed and should be removed" );
		}
	}

	public function testMustHaveConfigs() {
		$dbLists = DBList::getLists();
		// This list EXCLUDES special. See processing below.
		$wikiFamilies = [ 'wikipedia', 'wikibooks', 'wikimedia', 'wikinews', 'wikiquote', 'wikisource', 'wikiversity', 'wikivoyage', 'wiktionary' ];

		$mustHaveWikiFamilyConfig = [ 'wgServer', 'wgCanonicalServer' ];
		foreach ( $mustHaveWikiFamilyConfig as $key => $setting ) {
			foreach ( $wikiFamilies as $j => $family ) {
				$this->assertArrayHasKey(
					$family,
					$this->variantSettings[ $setting ],
					"Family '$family' has no default $setting."
				);
			}
		}

		$mustHaveConfigForSpecialWikis = [ 'wgServer', 'wgCanonicalServer' ];

		// TODO: Fix these and fold them into the above.
		$mustHaveConfigForSpecialWikisButSomeDoNot = [ 'wgLanguageCode' ];
		$knownFailures = [
			'advisorswiki',
			'boardgovcomwiki',
			'boardwiki',
			'commonswiki',
			'electcomwiki',
			'foundationwiki',
			'internalwiki',
			'labswiki',
			'labtestwiki',
			'loginwiki',
			'mediawikiwiki',
			'metawiki',
			'movementroleswiki',
			'nostalgiawiki',
			'outreachwiki',
			'sourceswiki',
			'spcomwiki',
			'specieswiki',
			'techconductwiki',
			'testcommonswiki',
			'testwikidatawiki',
			'wikidatawiki',
			'wikimaniawiki',
			'wikimania2005wiki',
			'wikimania2006wiki',
			'wikimania2007wiki',
			'wikimania2008wiki',
			'wikimania2009wiki',
			'wikimania2010wiki',
			'wikimania2011wiki',
			'wikimania2012wiki',
			'wikimania2013wiki',
			'wikimania2014wiki',
			'wikimania2015wiki',
			'wikimania2016wiki',
			'wikimania2017wiki',
			'wikimania2018wiki',
		];

		foreach ( $dbLists['special'] as $i => $db ) {
			foreach ( $mustHaveConfigForSpecialWikis as $j => $setting ) {
				$this->assertArrayHasKey(
					$db,
					$this->variantSettings[ $setting ],
					"Wiki '$db' is in the 'special' family but has no $setting set."
				);
			}

			foreach ( $mustHaveConfigForSpecialWikisButSomeDoNot as $j => $setting ) {
				if ( in_array( $db, $knownFailures ) ) {
					continue;
				}

				$this->assertArrayHasKey(
					$db,
					$this->variantSettings[ $setting ],
					"Wiki '$db' is in the 'special' family but has no $setting set."
				);
			}
		}
	}

	public function testwgServer() {
		foreach ( $this->variantSettings['wgCanonicalServer'] as $db => $entry ) {
			// Test if wgCanonicalServer start with https://
			$this->assertStringStartsWith( "https://", $entry, "wgCanonicalServer for $db doesn't start with https://" );
		}

		foreach ( $this->variantSettings['wgServer'] as $db => $entry ) {
			// Test if wgServer start with //
			$this->assertStringStartsWith( "//", $entry, "wgServer for $db doesn't start with //" );
		}
	}

	public function testwgSitename() {
		foreach ( $this->variantSettings['wgSitename'] as $db => $entry ) {
			// Test that the string doesn't contain invalid charcters T249014
			$this->assertStringNotContainsString( ',', $entry, "wgSitename for $db contains a ',' which breaks e-mails" );
		}
	}

	public function testOnlyExistingWikis() {
		$dblistNames = array_keys( DBList::getLists() );
		$langs = file( __DIR__ . "/../../langlist", FILE_IGNORE_NEW_LINES );
		foreach ( $this->variantSettings as $config ) {
			foreach ( $config as $db => $entry ) {
				$dbNormalized = str_replace( "+", "", $db );
				$this->assertTrue(
					in_array( $dbNormalized, $dblistNames ) ||
					DBList::isInDblist( $dbNormalized, "all" ) ||
					in_array( $dbNormalized,  $langs ) ||
					in_array( $dbNormalized, [ "default", "lzh", "yue", "nan" ] ), // TODO: revert back to $db == "default"
					"$dbNormalized is referenced, but it isn't either a wiki or a dblist" );
			}
		}
	}

	public function testNoAmbiguouslyTaggedSettings() {
		self::expectNotToPerformAssertions();

		$dblists = DBList::getLists();
		$overlapping = [];
		foreach ( $dblists as $listA => $wikisA ) {
			$overlapping[$listA] = [];
			foreach ( $dblists as $listB => $wikisB ) {
				if ( $listA !== $listB && array_intersect( $wikisA, $wikisB ) ) {
					$overlapping[$listA][] = $listB;
				}
			}
		}

		$ambiguous = [];

		foreach ( $this->variantSettings as $configName => $values ) {
			foreach ( $overlapping as $listA => $lists ) {
				if ( isset( $values[$listA] ) ) {
					foreach ( $lists as $listB ) {
						if (
							isset( $values[$listB] )
							&& $values[$listA] !== $values[$listB]
						) {
							$ambiguous[$configName][] = [
								$listA => $values[$listA],
								$listB => $values[$listB]
							];
						}
					}
				}
			}
		}

		if ( count( $ambiguous ) ) {
			$detailsString = "";
			foreach ( $ambiguous as $ambiguouslySetVariable => $errorEntries ) {
				$detailsString .= "\nThe variable $ambiguouslySetVariable is set differently in some dblists which overlap:\n";
				foreach ( $errorEntries as $index => $entry ) {
					foreach ( $entry as $listname => $value ) {
						if ( is_scalar( $value ) ) {
							$detailsString .= "\t " . $listname . ' sets it to `' . $value . "`\n";
						} else {
							$detailsString .= "\t " . $listname . ' sets it to `' . json_encode( $value ) . "` (JSON encoded for readability)\n";
						}
					}
				}
			}

			$this->fail( "Overlapping dblists are setting the same variable to different values. This is banned as it would rely on runtime sequence of dblists being read, which is not guaranteed.\n" . $detailsString );
		}
	}

	public function testCacheableLoad() {
		$settings = Wikimedia\MWConfig\MWConfigCacheGenerator::getCachableMWConfig(
			'enwiki', $this->variantSettings, 'production'
		);

		$this->assertEquals(
			'windows-1252', $settings['wgLegacyEncoding'],
			"Variant settings array must have 'wgLegacyEncoding' set to 'windows-1252' for enwiki."
		);

		$settings = Wikimedia\MWConfig\MWConfigCacheGenerator::getCachableMWConfig(
			'dewiki', $this->variantSettings, 'production'
		);

		$this->assertFalse(
			 $settings['wgLegacyEncoding'],
			"Variant settings array must have 'wgLegacyEncoding' set to 'windows-1252' for enwiki."
		);
	}

	public function testCacheableLoadForLabs() {
		$settings = Wikimedia\MWConfig\MWConfigCacheGenerator::getCachableMWConfig(
			'enwiki', $this->variantSettings, 'production'
		);
		$this->assertFalse(
			$settings['wmgUseFlow'],
			"Variant settings array must have 'wmgUseFlow' set to 'false' for production enwiki."
		);

		$settings = Wikimedia\MWConfig\MWConfigCacheGenerator::getCachableMWConfig(
			'mediawikiwiki', $this->variantSettings, 'production'
		);
		$this->assertTrue(
			$settings['wmgUseFlow'],
			"Variant settings array must have 'wmgUseFlow' set to 'true' for production mediawikiwiki."
		);

		$settings = Wikimedia\MWConfig\MWConfigCacheGenerator::getCachableMWConfig(
			'enwiki', $this->variantSettings, 'labs'
		);
		$this->assertTrue(
			$settings['wmgUseFlow'],
			"Variant settings array must have 'wmgUseFlow' set to 'true' for labs enwiki."
		);
	}
}
