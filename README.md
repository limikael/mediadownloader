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
