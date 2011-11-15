README
======

This is a small project for Stanford AI-Class www.ai-class.com

License
-------

Publishing it under MIT license
Mostly use it in any way you like.
However metioning my name is welcome :D

It was made to allow watching of lectures along with reading the caption.
Also allows to search trough all captions and jump to exactly that lecture and place where that line was used.

There are three sub projects here you can find in separate folders.

In short it all works like this:

* Step 1: Download and make XML of all videos and captions
* Step 2: Use XML as data source of a web page with all captions and YouTube video player

More about separate projects, you can read even more in comments to the source code

SubsBrowser
-----------
Its page itself. Just put it on to a server that support PHP and try it out.
Not much more to tell here, plain and simple :)

Issues:
 
* Does not work on iOS devices, no support for non Flash player
* Works badly on android, firstly not made for small screens, secondly issues with position:fixed

You can see it in action here: http://www.wonderwhy-er.com/ai-class/

AI-ClassSubsGetter(AS3)
-----------------------
### Update: 

Now is depricated, will not be updated, use PHP getter instead

Its an Flash/AIR application that downloads and outputs as XML all videos, titles, and captions.
It will show progress and in the end will open xml file saving dialog.

Made in Flash Develop 4 RC1. Just download it and open project file in it AI-ClassSubsGetter(AS3).as3proj

Alternatively install Adobe AIR, and then install app itself AI-ClassSubsGetter(AS3)/air/AIClassSubsGetterAS3.air

Issues:

Uses public user video feed to scrap videos list, homework videos are private until answers are given, so home work videos are added by hand until they become public

AI-ClassSubsGetter(PHP)
-----------------------
### Update:

Thanks to https://github.com/mudelta who showed what was wrong with getting subs.

Now this script is used to retrive videos and subtitles and prepare xml file.
I did not put it publiclly anywhere as it is a heavy script and I don't want dozen of visitors poking it

Works like this:

* Visit admin.php
* Click Update
* getSubs.php will be called
* getSubs.php runs pretty long operation of getting and parsing viodes and subtitles
* getSubs.php output progress in to subslog.txt
* admin.php page after clicking update will start polling trough AJAX subslog.txt and showing progress
* when finished polling will stop and result ai-class.xml will be saved in same folder

Issues:

* Seems to not be very relaible, may under some server settings or circumstances fail to finish
* There is no mechanism right now for checking if script is runing, so its possible to call it few times and few will run in parallel, not good, will need to fix it later


