<?php

namespace WPize\Utils;

class Utils {

	public static function recursivelyRemoveDirectory( $dir ) {
		if ( is_dir( $dir ) ) {
			$objects = scandir( $dir );
			foreach ( $objects as $object ) {
				if ( $object != "." && $object != ".." ) {
					if ( filetype( $dir . "/" . $object ) == "dir" ) {
						self::recursivelyRemoveDirectory( $dir . "/" . $object );
					} else {
						unlink( $dir . "/" . $object );
					}
				}
			}
			reset( $objects );
			rmdir( $dir );
		}
	}

	public static function createTempDir() {

		$tempfile = tempnam( sys_get_temp_dir(), '' );
		if ( file_exists( $tempfile ) ) {
			unlink( $tempfile );
		}
		mkdir( $tempfile );
		if ( is_dir( $tempfile ) ) {
			return $tempfile;
		}

		return false;
	}

	public static function recursivelyCopy($src, $dst, $skipHidden = false )
	{
		$dir = opendir($src);
		if (!is_dir($dst)) {
			mkdir($dst);
		}
		while (false !== ($file = readdir($dir))) {
			if ( $skipHidden && '.' == substr( $file, 0, 1 ) ) {

				continue;
			}
			if ( ( $file != '.' ) && ( $file != '..' ) ) {

				if (is_dir($src . '/' . $file)) {
					self::recursivelyCopy( $src . '/' . $file, $dst . '/' . $file, $skipHidden );
				} else {

					copy($src . '/' . $file, $dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}


}