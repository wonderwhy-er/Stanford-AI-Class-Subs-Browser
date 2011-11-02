/**
 * An AIR application for downloading captions from AI-Class Stanford course and preparing XML for a webpage
 * 
 * Copyright (C) 2011 by Eduard Ruzga
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
*/


package 
{
	import flash.display.SimpleButton;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.events.SecurityErrorEvent;
	import flash.filters.BevelFilter;
	import flash.geom.ColorTransform;
	import flash.net.FileReference;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.text.TextField;
	import flash.text.TextFieldAutoSize;
	
	/**
	 * ...
	 * @author Eduard Ruzga
	 */
	public class Main extends Sprite 
	{
		
		private var output:TextField;

		
		public function Main():void 
		{
			/* just initializing output */
			output = new TextField();
			output.border = true;
			output.x = 5;
			output.y = 5;
			output.width = stage.stageWidth - output.x - 5;
			output.height = stage.stageHeight - output.y - 5;
			addChild(output);
			
			request();
		}
		
		/*
		 * Function that request 50 videos from user public videos with offset
		 * no more then 50 is allowed at once so it runs as long as there are 50 videos in response feed
		 * when there is less then we are the end of the list
		 */
		
		 //	current video feed request offset
		 private var offset:uint = 1;	

		private function request():void
		{
			try {
				
				var loader:URLLoader = new URLLoader();
				var url:String = "http://gdata.youtube.com/feeds/api/users/knowitvideos/uploads/?start-index="+offset.toString()+"&max-results=50";
				echo(url);
				var urlRequest:URLRequest = new URLRequest(url);
				loader.addEventListener(Event.COMPLETE, complete);
				
				loader.addEventListener(SecurityErrorEvent.SECURITY_ERROR,
				function(e:IOErrorEvent):void
				{
					echo("error:" + e.toString());
				});
				
				loader.load(urlRequest);
				offset+=50;
			}
			catch(e:Error)
			{
				echo("error");
			}
		}
		
		//	don't mind this, just needed to work with namespaces in XML in AS3
		private var ns:Namespace = new Namespace("http://www.w3.org/2005/Atom");
		
		//	resulting XML that we will populate with data

		private var videosXML:XML = 
		<videos date={new Date().toDateString()}>
		</videos>;
		
		//	array of objects, intermediate storrage for video parrameters, needed to sort videos
		private var videos:Array = [];
		
		/*
		 * Function where we het video feed request result
		 * Here we:
			 * get result
			 * parse XML
			 * populate videos array
			 * sort videos
			 * populate videosXML
		 */
		private function complete(e:Event):void
		{
			//getting request result
			var l:URLLoader = e.currentTarget as URLLoader;
			var data:String = l.data as String;
			var xml:XML = XML(data);
			echo("Got lecture list [" + offset + "," + (offset + xml.ns::entry.length()) + "]:");
			
			
			/*
			 * Regular expression to find out all numbers in title, 
			 * needed to figure out units and lectures to which video belongs to sort videos later
			*/
			var numbers:RegExp = new RegExp(/\d+/g);
			
			// runing trough each video in feed
			for each(var entry:XML in xml.ns::entry)
			{
				// youtube video title
				var title:String = entry.ns::title.children();
				// youtube video link
				var id:String = entry.ns::link.(@rel=="alternate").@href;
				
				// getting video id from the link
				id = id.split("?")[1].split("&")[0].split("=")[1];
				
				numbers.lastIndex = 0;
				//finding first and second number in title 
				var unit:uint = numbers.exec(title);
				var lecture:uint = numbers.exec(title);
				
				/*
				 * Homeworks have sepparate numbering from lectures
				 * to make them appear at their chronological order I multiply homework number by 2
				 * so that Homework 1 unit is 2 now, and homework 2 unit is 4
				 * also I add 100 to their lecture number
				 * this way Homework 1 appears as lectures in the end of Unit 2 and so one for later Homeworks
				 */
				if (title.indexOf("Homework") > -1)
				{
					unit *= 2;
					lecture += 100;
				}
				
				//splitting string by , and space
				var parts:Array = title.split(",")[0].split(" ");
				
				//getting group name like Unit 1
				var groupName:String = parts[0] + " " + parts[1];
				
				//splitting a new to remove group name from titles
				parts = title.split(" ");
				parts.splice(0, 2);
				title = parts.join(" ");
				//echo(group);
				//adding associative object to array of videos for soring later
				videos.push({title:title,id:id,unit:unit,lecture:lecture,group:groupName});

			}
			
			// If there are 50 viodes in result then we ask for one more page, if not we got all videos there is
			if(xml.ns::entry.length()==50)
			{
				request();
			}
			else
			{	
				
				/*
				 * Currently Homeworks are added to public list only after they expired + answers are added
				 * But homeworks appear on AI-Class site before it as private videos
				 * So sadly we need to add them by hand
				 */
				
				/*videos.push({title:"Homework 1 Peg solitier",id:"CxjV8H50xfU",			unit:2,lecture:35});
				videos.push({title:"Homework 1 Loaded Coin",id:"ZmVLMZ5Fwcg",		unit:2,lecture:36});
				videos.push({title:"Homework 1 Maze",id:"dj6jEEU-jZc",				unit:2,lecture:37});
				videos.push({title:"Homework 1 Search Tree",id:"qsxMRW2SOqI",		unit:2,lecture:38});
				videos.push({title:"Homework 1 Search Tree 2",id:"vWNEaVcK2gU",		unit:2,lecture:39});
				videos.push({title:"Homework 1 Search Network",id:"IQhUlwJaBqc",	unit:2,lecture:40});
				videos.push({title:"Homework 1 A* Search",id:"V4h2H0jpGsg",			unit:2,lecture:41});
				*/
				videos.push({title:"Bayes Rule",					id:"_fJTJNK9ejY",	unit:4,lecture:11,		group:"Homework 2"});
				videos.push({title:"Simple Bayes Net",				id:"f6mq9rTj-Po",	unit:4,lecture:12,		group:"Homework 2"});
				videos.push({title:"Simple Bayes Net 2 ",			id:"P6WEObhmL_o",	unit:4,lecture:13,		group:"Homework 2"});
				videos.push({title:"Conditional Independence ",		id:"pP7U6KIO9yE",	unit:4,lecture:14,		group:"Homework 2"});
				videos.push( { title:"Conditional Indepedence 2 ",	id:"LMKW60DmJtc",	unit:4,lecture:15,		group:"Homework 2" } );
				videos.push( { title:"Parameter Count ",			id:"8npZMwT0Sac",	unit:4,lecture:16,		group:"Homework 2" } );
				
				/*videos.push( { title:"Homework 3 Naive Bayes",			id:"rGWjGzcWm_Y",	unit:6, lecture:101 } );
				videos.push( { title:"Homework 3 Naive Bayes 2",			id:"YUhCs9cdoNQ",	unit:6, lecture:102 } );
				videos.push( { title:"Homework 3 Maximum Likelihood",		id:"OtnU31P68bQ",	unit:6, lecture:103 } );
				videos.push( { title:"Homework 3 Linear Regression",		id:"KcpbUw86hXg",	unit:6, lecture:104 } );
				videos.push( { title:"Homework 3 Linear Regression 2",		id:"FcIEuDUzo1M",	unit:6, lecture:105 } );
				videos.push( { title:"Homework 3 K Nearest Neighbors",		id:"RM3FfIYoOy8",	unit:6, lecture:106 } );
				videos.push( { title:"Homework 3 K Nearest Neighbors 2",	id:"qXR_IIL-VZY",	unit:6, lecture:107 } );
				videos.push( { title:"Homework 3 Perceptron ",				id:"zpLDF6HrW_w",	unit:6, lecture:108 } );*/
				
				/*videos.push( { title:"Naive Bayes Answer",				id:"evtCdmjcZ4I",	unit:6, lecture:101,		group:"Homework 3" } );
				videos.push( { title:"Naive Bayes 2 Answer",			id:"LRQKhmXpDLI",	unit:6, lecture:102,		group:"Homework 3" } );
				videos.push( { title:"Maximum Likelihood Answer",		id:"3lA9jrqw7_4",	unit:6, lecture:103,		group:"Homework 3" } );
				videos.push( { title:"Linear Regression Answer",		id:"yTYQg1XiBEQ",	unit:6, lecture:104,		group:"Homework 3" } );
				videos.push( { title:"Linear Regression 2 Answer",		id:"ynxLGEE_Bgo",	unit:6, lecture:105,		group:"Homework 3" } );
				videos.push( { title:"K Nearest Neighbors Answer",		id:"01qBi27m3Ss",	unit:6, lecture:106,		group:"Homework 3" } );
				videos.push( { title:"K Nearest Neighbors 2 Answer",	id:"IjzpuYn7Szc",	unit:6, lecture:107,		group:"Homework 3" } );
				videos.push( { title:"Perceptron Answer",				id:"P88qJlIRnwI",	unit:6, lecture:108,		group:"Homework 3" } ); */
				
				
				
				videos.push( { title:"1. Logic ",				id:"WP_97aspqrc",		unit:8, lecture:101,		group:"Homework 4" } );
				videos.push( { title:"2. More Logic",			id:"P_eu1YFp9Z8",		unit:8, lecture:102,		group:"Homework 4" } ); 
				videos.push( { title:"3. Vacuum World ",		id:"wsfXrIhDhJ0",		unit:8, lecture:103,		group:"Homework 4" } ); 
				videos.push( { title:"4. More Vacuum World ",	id:"2H4NJg8Iiaw",		unit:8, lecture:104,		group:"Homework 4" } );
				videos.push( { title:"5. More Vacuum World ",	id:"i5XMOLw6CGE",		unit:8, lecture:105,		group:"Homework 4" } );
				videos.push( { title:"6. More Vacuum World ",	id:"x93ewPQhIQc",		unit:8, lecture:106,		group:"Homework 4" } );
				videos.push( { title:"7. More Vacuum World ",	id:"RW-l7JWDtYQ",		unit:8, lecture:107,		group:"Homework 4" } );
				videos.push( { title:"8. Monkey and Bananas ",	id:"rCGAgc9smZg",		unit:8, lecture:108,		group:"Homework 4" } );
				videos.push( { title:"9. Situation Calculus ",	id:"eeDwEYxWCTA",		unit:8, lecture:109,		group:"Homework 4" } );

				
				/*
				 * Here we sort resulting video list
				 * First we sort by unit
				 * If unit is equal for two videos we compare them by lecture
				 * If unit and lecture is equal we compare them just in dictionary order
				 * This way 1.5a goes before 1.5b
				 * Also Answer videos go after question videos(they have same unit/lecture numbers and name except for Answer in the end)
				 */
				videos.sortOn(["unit", "lecture", "title"], [Array.NUMERIC, Array.NUMERIC, Array.CASEINSENSITIVE]);
				
				//dictionary to track what groups were already encoutered
				var groups:Object = { };
				//group xml object
				var group:XML;
				
				//Outputing resulting sorted titles + populating our resulting XML
				echo("Sorted lecture list:");
				for each(var video:Object in videos)
				{
					//if we already have a group just raise the count
					if (groups.hasOwnProperty(video.group))
						groups[video.group]++;
					else
					{
						//if it is a new group, create new counter and new group xml object to which to append vidoes
						groups[video.group] = 1;
						group = <group title={video.group}/>;
						videosXML.appendChild(group);
						//trace(group);
					}
					//echo(video.title);
					group.appendChild(<video title={video.title} id={video.id}/>);
				}
				
				//when all objects are added to groups add video count property to groups
				for (groupName in groups)
				{
					videosXML.group.(@title == groupName)[0].@count  = groups[groupName];
				}
				//echo(videosXML);
				echo("Got video list, starting to retrive captions");
				//Starting to request subtitles
				getSubs();
			}
		}
		
		// index of video for which we are getting subs now
		private var currentVideo:uint = 0;
		
		// function where we retrive current video ID from array, and then we make HTTP request for youtube captions from public source
		private function getSubs():void
		{
			var loader:URLLoader = new URLLoader();
			loader.addEventListener(Event.COMPLETE,subsComplete);
			var v:String = videos[currentVideo].id;
			loader.load(new URLRequest("http://video.google.com/timedtext?lang=en&name=English via dotsub&v=" + v));
			loader.addEventListener(IOErrorEvent.IO_ERROR, ioError);
		}
		
		private function ioError(e:IOErrorEvent):void
		{
			trace("io error:" + e.text);
			getSubs();
		}
		
		/*
		 * Callback for subs request
		 * We check if we got something
		 * If yes, we append resulting subtitles to apropriate video XML ellement as child ellement
		 * If not(some videos lack subtitles) we add "no subtitles" line
		 */
		
		private function subsComplete(e:Event):void
		{
			var data:String = e.target.data;
			var empty:Boolean = false;
			if (data.length == 0)
			{
				data = "<transcript><text start=\"0\" dur=\"3\">No subtitles...</text></transcript>"
				empty = true;
			}
			var xml:XML = XML(data);
			videosXML.group.video[currentVideo].appendChild(xml);
			var title:String = videos[currentVideo].title;
			
			if (empty)
			{
				videosXML.group.video[currentVideo].@length = "?";
			}
			else
			{
				var texts:XMLList = xml.text;
				var lastCaption:XML = texts[texts.length()-1];
				videosXML.group.video[currentVideo].@length = Number(lastCaption.@start) + Number(lastCaption.@dur);
			}

			
			currentVideo++;
			//if it is not the last video, request next one
			if(currentVideo!=videos.length)
			{
				echo("got:"+(currentVideo+1)+"/"+videos.length+" : "+title+" : "+videos[currentVideo].id+" : "+xml.toString().length);
				getSubs();
			}
			else
			{
				// if we got all videos, open up xml saving dialog
				var fr:FileReference = new FileReference();
				fr.save(videosXML, "ai-class.xml");
			}
		}
		
		// output function
		private function echo(txt:String):void
		{
			output.appendText(txt + "\n");
			output.scrollV = output.maxScrollV;
		}
		
	}
	
}