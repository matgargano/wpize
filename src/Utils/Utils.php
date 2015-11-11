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

	public static function recursivelyCopy($src, $dst, Array $exceptions = array() )
	{
		$dir = opendir($src);
		if (!is_dir($dst)) {
			mkdir($dst);
		}
		while (false !== ($file = readdir($dir))) {
			if ( ( $file != '.' ) && ( $file != '..' ) && ( ! is_array( $exceptions ) || ( isset( $exceptions['directories'] ) && ! in_array( $file, $exceptions['directories'] ) ) ) ) {
				if (is_dir($src . '/' . $file)) {
					self::recursivelyCopy( $src . '/' . $file, $dst . '/' . $file, $exceptions );
				} elseif ( is_array( $exceptions ) && isset( $exceptions['files'] ) && ! in_array( $file, $exceptions['files'] ) ) {

					copy($src . '/' . $file, $dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}


}