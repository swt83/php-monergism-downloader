# Monergism

A PHP script for downloading over 500 Christian ebooks from Monergism.com.

The website [Monergism.com](https://www.monergism.com/) offers hundreds of free Christian ebooks, going back as far as Augustine and including the works of many early Reformers.  Downloading all of them would take quite a bit of time, so I wrote this script to download them all automatically.  This script isn't perfect, but it's 99% perfect.

Total filesize of the downloaded archive is approx 500 MB.

## How To Run

This script will scan the website for all downloads of type ``ebub`` or ``mobi`` and it will save those files to the ``storage`` directory.

```
$ git clone git@github.com:swt83/php-monergism-downloader.git monergism
$ cd monergism
$ composer update
$ php run
```

Rerunning the app will auto-skip the files that already exist on your hard drive, so you are safe to stop and restart the script at any time.

It will download all versions of a title that it finds and number them accordingly, placing the files into subfolders named after the authors.