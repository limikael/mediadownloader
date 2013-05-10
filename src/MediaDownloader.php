<?php

	if (!class_exists("Logger"))
		require_once __DIR__."/base/Logger.php";

	require_once __DIR__."/base/Media.php";
	require_once __DIR__."/base/IDownloader.php";
	require_once __DIR__."/base/Metadata.php";

	require_once __DIR__."/downloaders/YoutubeMp3OrgDownloader.php";
	require_once __DIR__."/downloaders/YoutubeDownloader.php";

	/**
	 * Download media from the Internet.
	 */
	class MediaDownloader {

		private $downloader;
		private $downloaderClasses;
		private $progressFunc;

		const LOW=Media::LOW;
		const MEDIUM=Media::MEDIUM;
		const HIGH=Media::HIGH;
		
		/**
		 * Constructor.
		 */
		public function MediaDownloader($url=NULL) {
			$this->downloaderClasses=array(
				"YoutubeMp3OrgDownloader",
				"YoutubeDownloader"
			);

			if ($url)
				$this->load($url);
		}

		/**
		 * Load media.
		 */
		public function load($url) {
			$url=trim($url);

			Logger::debug("initiating media download from: ".$url);
			$useClassName=NULL;

			foreach ($this->downloaderClasses as $className) {
				if (!$useClassName && $className::canHandle($url))
					$useClassName=$className;
			}

			if (!$useClassName)
				throw new Exception("No handler for: ".$url);

			$this->downloader=new $useClassName();
			$this->downloader->load($url);
			$this->downloader->setProgressFunc(array($this,"onDownloadProgress"));
		}

		/**
		 * Download progress.
		 */
		public function onDownloadProgress($percent) {
			if ($this->progressFunc)
				call_user_func($this->progressFunc,$percent);
		}

		/**
		 * Get metadata.
		 */
		public function getMetadata() {
			return $this->downloader->getMetadata();
		}

		/**
		 * Set progress func.
		 */
		public function setProgressFunc($progressFunc) {
			$this->progressFunc=$progressFunc;
		}

		/**
		 * Get media.
		 */
		public function getMedia() {
			return $this->downloader->getMedia();
		}

		/**
		 * Get media by specific type.
		 */
		public function getMediaByType($type) {
			foreach ($this->getMedia() as $media)
				if ($media->getType()==$type)
					return $media;

			return NULL;
		}

		/**
		 * Find media according to preferred type.
		 */
		public function getMediaByPreferredType($types) {
			foreach ($types as $type) {
				$media=$this->getMediaByType($type);
				if ($media)
					return $media;
			}

			return NULL;
		}
	}