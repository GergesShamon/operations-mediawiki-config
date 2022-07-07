<?php
/**
 * Helpers for DbListTests.
 *
 * @license GPL-2.0-or-later
 * @author Antoine Musso <hashar at free dot fr>
 * @copyright Copyright © 2012, Antoine Musso <hashar at free dot fr>
 * @file
 */

class DBList {

	/**
	 * @return array
	 */
	public static function getLists() {
		static $list = null;
		if ( !$list ) {
			$list = [];
			foreach ( glob( __DIR__ . '/../dblists/*.dblist' ) as $filename ) {
				$basename = basename( $filename, '.dblist' );
				$list[$basename] = MWWikiversions::readDbListFile( $basename );
			}
		}
		return $list;
	}

	/**
	 * @param string $dbname
	 * @return bool
	 */
	public static function isWikiFamily( $dbname ) {
		return isset( MWMultiVersion::SUFFIXES[ $dbname ] );
	}

	/**
	 * Checks if given dbname is in dblist.
	 *
	 * @param string $dbname
	 * @param string $dblist
	 * @return bool
	 */
	public static function isInDblist( $dbname, $dblist ) {
		return in_array( $dbname, self::getLists()[$dblist] );
	}

	/**
	 * Get list of dblist names loaded in CommonSettings.php.
	 *
	 * @return string[]
	 */
	public static function getDblistsUsedInSettings() {
		return MWMultiVersion::DB_LISTS;
	}
}
