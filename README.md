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

