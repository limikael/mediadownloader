<?php

	/**
	 * Download media from youtube.
	 */
	class YoutubeDownloader implements IDownloader {

		private $youtubeUrl;
		private $cookiePath;
		private $metadata;
		private $media;
		private $progressFunc;
		private $oldPercent;

		/**
		 * Consctructor.
		 */
		public function YoutubeDownloader() {
		}

		/**
		 * Set progress callback.
		 */
		public function setProgressFunc($func) {
			$this->progressFunc=$func;
		}

		/**
		 * Load url.
		 */
		public function load($url) {
			$this->youtubeUrl=$url;
			$this->youtubeId=YoutubeDownloader::youtubeUrlToId($this->youtubeUrl);
			$this->cookieFile=tempnam(sys_get_temp_dir(),'youtube-cookie');

			// This pretends this scraper to be browser client IE6 on Windows XP, 
			// of course you can pretend to be other browsers just you have to know the correct headers.
			$this->curlHeaders=array(
				"Accept-Language: en-us",
				"User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15",
				"Connection: Keep-Alive",
				"Cache-Control: no-cache"
			);

			// Fetch raw page.
			$this->rawPage=$this->fetchUrl($this->youtubeUrl);

			// Fetch info and decode.
			$this->rawInfo=$this->fetchUrl("http://www.youtube.com/get_video_info?video_id=".$this->youtubeId);
			$this->parseMetadata();
			$this->fetchMedia();
		}

		/**
		 * Get media.
		 */
		public function getMedia() {
			return $this->media;
		}

		/**
		 * Get metadata.
		 */
		public function getMetadata() {
			return $this->metadata;
		}

		/**
		 * Construct metadata object.
		 */
		private function parseMetadata() {
			$vars=YoutubeDownloader::decodeUrlVariables($this->rawInfo);

			$this->metadata=new Metadata();
			$this->metadata->title=$vars["title"];
			$this->metadata->thumbnail=$vars["thumbnail_url"];
			$this->metadata->keywords=explode(",",$vars["keywords"]);

			$regex = '/Content-Length:\s([0-9].+?)\s/';
			$count = preg_match('/<meta name="description" content="([^"]*)">/', $this->rawPage, $matches);
			$this->metadata->description=$matches[1];
		}

		/**
		 * Fetch media objects.
		 */
		private function fetchMedia() {
			$vars=YoutubeDownloader::decodeUrlVariables($this->rawInfo);
			//print_r($vars);
			$fmtMap=$vars["url_encoded_fmt_stream_map"];
			$fmtEntries=explode(",",$fmtMap);

			foreach ($fmtEntries as $fmtEntry) {
				$fmtVars=YoutubeDownloader::decodeUrlVariables($fmtEntry);
				//print_r($fmtVars);

				switch ($fmtVars["quality"]) {
					case "small":
						$quality=Media::LOW;
						break;

					case "medium":
						$quality=Media::MEDIUM;
						break;

					case "high":
						$quality=Media::HIGH;
						break;

					case "hd720":
						$quality=Media::HIGH;
						break;

					default:
						MediaDownloaderLogger::debug("unknown quality: ".$fmtVars["quality"]);
						$quality=Media::MEDIUM;
						break;
				}

				$parts=explode(";",$fmtVars["type"]);
				$type=$parts[0];

				if (sizeof($parts)>1)
					$extra=trim($parts[1]);

				else
					$extra="";

				$media=new Media($type,$quality,$extra);
				$media->setDownloader($this);
				$media->data["url"]=$fmtVars["url"]."&signature=".$fmtVars["sig"];

				$this->media[]=$media;
			}
		}

		/**
		 * Fetch url in youtube context.
		 */
		private function fetchUrl($url) {
			$ch=curl_init();	

			curl_setopt($ch,CURLOPT_HTTPHEADER,$this->curlHeaders);
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_BINARYTRANSFER,1);
			curl_setopt($ch,CURLOPT_COOKIEJAR,$this->cookiePath);
			curl_setopt($ch,CURLOPT_COOKIEFILE,$this->cookiePath);
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);

			$output=curl_exec($ch);

			if ($output===FALSE)
				throw new Exception(curl_error($ch));

			$info=curl_getinfo($ch);
			curl_close($ch);
    
			return $output;
		}

		/**
		 * Convert url to id.
		 */
		private static function youtubeUrlToId($youtubeUrl) {
			$url=parse_url($youtubeUrl);
			$vars=YoutubeDownloader::decodeUrlVariables($url["query"]);

			//MediaDownloaderLogger::debug("id: ".$vars["v"]);

			return $vars["v"];
		}

		/**
		 * Decode url variables into array.
		 */
		private static function decodeUrlVariables($urlString) {
			$a=explode("&",$urlString);
			$r=array();

			foreach ($a as $pair) {
				$pos=strpos($pair,"=");
				if ($pos!==FALSE) {
					$key=substr($pair,0,$pos);
					$value=urldecode(substr($pair,$pos+1));
					$r[$key]=$value;
				}
			}

			return $r;
		}

		/**
		 * On download progress.
		 */
		public function onDownloadProgress($dlSize,$dl,$ulSize,$ul) {
			//echo "download progres...";
			if (!$dlSize)
				return;

			$percent=round(100*$dl/$dlSize);

			if ($percent!=$this->oldPercent && $this->progressFunc)
				call_user_func($this->progressFunc,$percent);

			$this->oldPercent=$percent;
		}

		/**
		 * Download.
		 */
		public function download($media, $targetFile) {
			$this->oldPercent=-1;

			MediaDownloaderLogger::debug("youtube: will download from: ".$media->data["url"]);

			$ch=curl_init($media->data["url"]);
			$fp=fopen($targetFile,"w");
			curl_setopt($ch,CURLOPT_FILE,$fp);
			curl_setopt($ch,CURLOPT_BINARYTRANSFER,1);
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true );
			curl_setopt($ch,CURLOPT_COOKIEJAR,$this->cookiePath);
			curl_setopt($ch,CURLOPT_COOKIEFILE,$this->cookiePath);
			curl_setopt($ch,CURLOPT_HTTPHEADER,$this->curlHeaders); 
			curl_setopt($ch,CURLOPT_NOPROGRESS,FALSE);
			curl_setopt($ch,CURLOPT_PROGRESSFUNCTION,array($this,"onDownloadProgress"));
			$res=curl_exec($ch);
			curl_close($ch);
			fclose($fp);

			if (!filesize($targetFile))
				throw new Exception("Unable to download");

			if (!$res)
				throw new Exception(curl_error($ch));
		}

		/**
		 * Can we handle this url?
		 */
		public static function canHandle($url) {
			$a=parse_url($url);

			if ($a["host"]=="www.youtube.com")
				return TRUE;

			else
				return FALSE;
		}
	}