<?php

	/**
	 * Metadata for media.
	 */
	class Metadata {
		public $title;
		public $description;
		public $keywords;
		public $thumbnail;

		/**
		 * Convert to string.
		 */
		public function __toString() {
			return "[Metadata(title='".$this->title."')]";
		}
	}