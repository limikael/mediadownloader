<?php

	require_once "../src/MediaDownloader.php";

	Logger::enable();

	function onprogress($percent) {
		echo "Downloading: ".$percent."%\r";
	}

	//$downloader=new MediaDownloader("http://www.youtube.com/watch?v=lol3Rn570pA");
	//$downloader=new MediaDownloader("http://www.youtube.com/watch?v=Nq6Kq4Q1asc");
	//$downloader=new MediaDownloader("http://www.youtube.com/watch?v=XwqUTKfqk9M");
	//$downloader=new MediaDownloader("http://www.youtube.com/watch?v=jvONXJiEOhw");
	//$downloader=new MediaDownloader("http://www.youtube.com/watch?v=GI6KcHn0dvU");
	$downloader=new MediaDownloader("http://www.youtube.com/watch?v=5tQRFDXpcyc");
	$metadata=$downloader->getMetaData();

	echo "Title:       ".$metadata->title."\n";
	echo "Description: ".$metadata->description."\n";
	echo "Keywords:    ".join(",",$metadata->keywords)."\n";
	echo "Thumbnail:   ".$metadata->thumbnail."\n";
	echo "Media:\n";

	foreach ($downloader->getMedia() as $media) {
		echo $media->toString()."\n";
	}

	$downloader->setProgressFunc("onprogress");
	$media=$downloader->getMediaByPreferredType(array("video/mp4","audio/mp3"));
	$media->download("test.".$media->getSuggestedExtension());

	echo "\n";
	echo "Done!\n";