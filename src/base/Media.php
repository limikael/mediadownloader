<?php

	/**
	 * Media for media.
	 */
	class Media {

		const LOW="low";
		const MEDIUM="medium";
		const HIGH="high";

		private $url;
		private $quality;
		private $type;
		private $downloader;
		private $extra;

		// Extra data used by the downloader.
		public $data;

		/**
		 * Constructor.
		 */
		public function Media($type, $quality, $extra="") {
			$this->type=$type;
			$this->quality=$quality;
			$this->extra=$extra;

			$this->data=array();
		}

		/**
		 * Set downloader.
		 */
		public function setDownloader($value) {
			$this->downloader=$value;
		}

		/**
		 * Download.
		 */
		public function download($targetFile) {
			$this->downloader->download($this, $targetFile);
		}

		/**
		 * Get type.
		 */
		public function getType() {
			return $this->type;
		}

		/**
		 * Get quality.
		 */
		public function getQuality() {
			return $this->quality;
		}

		/**
		 * Get string rep.
		 */
		public function toString() {
			return "[Media (type='".$this->type."', quality='".$this->quality."', extra='".$this->extra."')]";
		}

		/**
		 * Get extra info.
		 */
		public function getExtra() {
			return $this->extra;
		}

		/**
		 * Get a suggested extension for the media.
		 */
		public function getSuggestedExtension() {
			switch ($this->type) {
				case "video/mp4":
					return "mp4";
					break;

				case "video/x-flv":
					return "flv";
					break;

				case "audio/mp3":
					return "mp3";
					break;

				default:
					return "media";
			}
		}
	}