<?php

	/**
	 * Use youtube-mp3.org to downlaod videos.
	 */
	class YoutubeMp3OrgDownloader implements IDownloader {

		private $progressFunc;
		private $media;
		private $youtubeCookiePath;
		private $youtubeCurlHeaders;
		private $youtubeUrl;
		private $youtubeId;
		private $oldPercent;

		/**
		 * Construct.
		 */
		public function YoutubeMp3OrgDownloader() {
		}

		/**
		 * Set progress func.
		 */
		public function setProgressFunc($func) {
			$this->progressFunc=$func;
		}

		/**
		 * Load.
		 */
		public function load($url) {
			$this->youtubeUrl=$url;
			$this->youtubeId=self::youtubeUrlToId($this->youtubeUrl);
			$this->youtubeCookieFile=tempnam(sys_get_temp_dir(),'youtube-cookie');

			// This pretends this scraper to be browser client IE6 on Windows XP, 
			// of course you can pretend to be other browsers just you have to know the correct headers.
			$this->youtubeCurlHeaders=array(
				"Accept-Language: en-us",
				"User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15",
				"Connection: Keep-Alive",
				"Cache-Control: no-cache"
			);

			// Fetch raw page.
			$this->rawPage=$this->fetchUrlFromYoutube($this->youtubeUrl);

			// Fetch info and decode.
			$this->rawInfo=$this->fetchUrlFromYoutube("http://www.youtube.com/get_video_info?video_id=".$this->youtubeId);
			$this->parseMetadata();

			$media=new Media("audio/mp3",Media::MEDIUM);
			$media->data["url"]=$url;
			$media->setDownloader($this);

			$this->media=array($media);
		}

		/**
		 * Convert url to id.
		 */
		private static function youtubeUrlToId($youtubeUrl) {
			$url=parse_url($youtubeUrl);
			$vars=self::decodeUrlVariables($url["query"]);

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
		 * Can we handle this url?
		 */
		public static function canHandle($url) {
			$a=parse_url($url);

			if ($a["host"]=="www.youtube.com")
				return TRUE;

			else
				return FALSE;
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
		 * Fetch url in youtube context.
		 */
		private function fetchUrlFromYoutube($url) {
			$ch=curl_init();	

			curl_setopt($ch,CURLOPT_HTTPHEADER,$this->youtubeCurlHeaders);
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_BINARYTRANSFER,1);
			curl_setopt($ch,CURLOPT_COOKIEJAR,$this->youtubeCookiePath);
			curl_setopt($ch,CURLOPT_COOKIEFILE,$this->youtubeCookiePath);
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
		 * Construct metadata object.
		 */
		private function parseMetadata() {
			$vars=self::decodeUrlVariables($this->rawInfo);

			$this->metadata=new Metadata();
			$this->metadata->title=$vars["title"];
			$this->metadata->thumbnail=$vars["thumbnail_url"];
			$this->metadata->keywords=explode(",",$vars["keywords"]);

			$regex = '/Content-Length:\s([0-9].+?)\s/';
			$count = preg_match('/<meta name="description" content="([^"]*)">/', $this->rawPage, $matches);
			$this->metadata->description=$matches[1];
		}

		/**
		 * Download.
		 */
		public function download($media, $targetFile) {
			Logger::debug("downloading from youtube-mp3.org: ".$media->data["url"]);

			$pushUrl="http://www.youtube-mp3.org/a/pushItem/?item=".
				urlencode($media->data["url"]).
				"&el=na&bf=false&r=".rand(0,100000);

			Logger::debug("url: ".$pushUrl);
			$out=$this->fetchUrlRaw($pushUrl);

			if ($out!=$this->youtubeId)
				throw new Exception("Unable to initiate downlaod at this point.");

			$status=$this->getYoutubeMp3OrgStatus();

			Logger::debug("youtube-mp3.org status: ".print_r($status,TRUE));

			$downloadUrl="http://www.youtube-mp3.org/get?video_id=".
				$this->youtubeId.
				"&h=".$status["h"]."&r=".rand(0,100000);

			$this->saveRawUrl($downloadUrl,$targetFile);
		}

		/**
		 * Get remote status.
		 */
		private function getYoutubeMp3OrgStatus() {
			$itemUrl="http://www.youtube-mp3.org/a/itemInfo/?video_id=".
				$this->youtubeId.
				"&ac=www&t=grp&r=".rand(0,100000);

			$res=$this->fetchUrlRaw($itemUrl);
			$res=trim($res);
			$res=str_replace("info = {","{",$res);
			$res=str_replace("};","}",$res);
			Logger::debug("status: ".$res);
			return json_decode($res,TRUE);
		}

		/**
		 * Fetch url in youtube context.
		 */
		private function fetchUrlRaw($url) {
			$ch=curl_init();	

			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_BINARYTRANSFER,1);
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
		 * Save raw url.
		 */
		private function saveRawUrl($url, $filename) {
			$ch=curl_init();	

			$outf=fopen($filename,"wb");
			if (!$outf)
				throw new Exception("Unable to open output file for download.");

			$this->oldPercent=-1;
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_FILE,$outf);
			curl_setopt($ch,CURLOPT_BINARYTRANSFER,1);
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
			curl_setopt($ch,CURLOPT_NOPROGRESS,FALSE);
			curl_setopt($ch,CURLOPT_PROGRESSFUNCTION,array($this,"onDownloadProgress"));

			$result=curl_exec($ch);
			fclose($outf);

			if ($result===FALSE)
				throw new Exception(curl_error($ch));
		}

		/**
		 * On download progress.
		 */
		public function onDownloadProgress($dlSize,$dl,$ulSize,$ul) {
			if (!$dlSize)
				return;

			$percent=round(100*$dl/$dlSize);

			if ($percent!=$this->oldPercent && $this->progressFunc)
				call_user_func($this->progressFunc,$percent);

			$this->oldPercent=$percent;
		}
	}