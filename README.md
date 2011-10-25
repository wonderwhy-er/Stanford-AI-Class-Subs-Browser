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
Step 1: Download and make XML of all videos and captions
Step 2: Use XML as data source of a web page with all captions and YouTube video player

More about separate projects, you can read even more in comments to the source code

SubsBrowser
-----------
Its page itself. Just put it on to a server that support PHP and try it out.
Not much more to tell here, plain and simple :)

Issues: 
- Does not work on iOS devices, no support for non Flash player
- Works badly on android, firstly not made for small screens, secondly issues with position:fixed

You can see it in action here: http://www.wonderwhy-er.com/ai-class/

AI-ClassSubsGetter(AS3)
-----------------------

Its an Flash/AIR application that downloads and outputs as XML all videos, titles, and captions.
It will show progress and in the end will open xml file saving dialog.

Made in Flash Develop 4 RC1. Just download it and open project file in it AI-ClassSubsGetter(AS3).as3proj

Alternatively install Adobe AIR, and then install app itself AI-ClassSubsGetter(AS3)/air/AIClassSubsGetterAS3.air

Issues:
- Uses public user video feed to scrap videos list, homework videos are private until answers are given, so home work videos are added by hand until they become public

AI-ClassSubsGetter(PHP)
-----------------------

This was a side project. Honestly I hoped to make a PHP script that downloads all needed data and makes an XML file for the page.
Sadly getting captions from google fails. It returns error 400: malformed or illegal request. Even though doing same request trough browser or Flash returns results just fine.
Sadly failed to figure out whats' wrong and am not intending to find it out anymore.

You can see video list scrapping script in action here(works fine): http://www.wonderwhy-er.com/ai-class/getSortedVideoList.php
And here is captions scrapping script(does not work, you can click on the link and see that browser gets it just fine): http://www.wonderwhy-er.com/ai-class/getSubs.php

Will be very thankful to anyone who will figure out a way of how to retrieve YouTube captions trough PHP so that I could finish this script and use it instead of AIR app.