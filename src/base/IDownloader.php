<?php

	/**
	 * Handle media downloads for a particular site.
	 */
	interface IDownloader {

		/**
		 * Return true or false if we can handle this url.
		 */
		static function canHandle($url);

		/**
		 * Initialize with an url.
		 */
		function load($url);

		/**
		 * Get metadata, should return a Metadata object.
		 */
		function getMetadata();

		/**
		 * Get media, should return an array of Media objects.
		 */
		function getMedia();

		/**
		 * Download media, the media parameter references an
		 * object in the array returned by getMedia.
		 */
		function download($media, $targetFile);

		/**
		 * Set function where to report progress of the download.
		 */
		function setProgressFunc($progressFunc);
	}