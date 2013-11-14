MediaDownloader
===============

Library for downloading media from various sites.

Actually, it is only YouTube at the moment, but the goal is to have as many sites as possible!

Usage
-----

The API is intended to be flexible and easy to use. The following is both a tutorial and the closest thing 
that exists to a reference. 

The first thing to do is to create a `MediaDownloader` object for the URL we want to download the media from.

```php
$downloader=new MediaDownloader("http://www.youtube.com/watch?v=Z5AISm31cqc");
```

At this point we can fetch metadata for the media. The metadata includes ```title```, ```description```, 
```keywords``` and ```thumbnail``` url for the media. These are all strings, except keywords which is an
array of strings.

```php
$metadata=$downloader->getMetaData();

echo $metadata->title."\n";
echo $metadata->description."\n";
echo $metadata->thumbnail."\n";
print_r($metadata->keywords);
```

Next, we can fetch an array of ```Media``` objects. They each contain information suggesting the type and
quality of the media.

```php
$mediaEntries=$downloader->getMedia();

foreach ($mediaEntries as $media) {
  echo "type: ".$media->getType()." quality: ".$media->getQuality()."\n";
}
```

It is also possible to fetch the ```Media``` object we want by asking for a particular type, provided that it exists.

```php
$media=$downloader->getMediaByType("video/mp4");
```

Once we have the ```Media``` object, we can start the download.

```php
$media->download("my_downloaded_media");
```

