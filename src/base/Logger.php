<?php

	/**
	 * Logger.
	 */
	class Logger {

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
		public static function enable() {
			self::$enabled=TRUE;
		}
	}