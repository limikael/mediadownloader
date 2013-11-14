<?php

	/**
	 * Logger.
	 */
	class MediaDownloaderLogger {

		private static $enabled=FALSE;

		/**
		 * Debug.
		 */
		public static function debug($s) {
			if (self::$enabled)
				echo "debug: $s\n";
		}

		/**
		 * Enable.
		 */
		public static function setEnabled($value) {
			self::$enabled=$value;
		}
	}