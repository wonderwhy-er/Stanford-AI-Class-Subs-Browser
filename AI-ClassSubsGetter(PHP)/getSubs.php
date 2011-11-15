<?php

/**
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

/**
 * A script that retrives list of Staford AI-Course lectures and sorts them
*/ 

/*
 * Custom comparer function for sorting
 * First we sort by unit
 * If unit is equal for two videos we compare them by lecture
 * If unit and lecture is equal we compare them just in dictionary order
 * This way 1.5a goes before 1.5b
 * Also Answer videos go after question videos(they have same unit/lecture numbers and name except for Answer in the end)
 */
function custom_sort($a,$b) 
{
	if($a['unit']>$b['unit'])
		return true;
	else if($a['unit']<$b['unit'])
		return false;
	else
	{
		if($a['lecture']>$b['lecture'])
			return true;
		else if($a['lecture']<$b['lecture'])
			return false;
		else
			return $a['title']>$b['title'];
	}
}

//function to add one XML element as child of another dynamically, sadly not possible trough SimpleXML
function appendChild(SimpleXMLElement $to, SimpleXMLElement $from) {
    $toDom = dom_import_simplexml($to);
    $fromDom = dom_import_simplexml($from);
    $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
}

// getting remote file
function getRemoteFile($url) 
{

	$curl = curl_init($url);

	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($curl, CURLOPT_TIMEOUT, 10);
 
	if (!$html = curl_exec($curl))
	{ 
		$html = file_get_contents($url); 
	}
	curl_close($curl);
	
	return $html;
}



//as this script is very long it can't report its progress directly, so instead it write progress in to a log file which is polled by admin page
$logFile = fopen("subslog.txt", 'w') or die("can't open file");

//as this script can take a very long time php script timeout is set to infinite, risky if you have buggy loops in there
set_time_limit(0);


//output function that write output in to a log file
function writeLog($msg)
{
	global $logFile;
	fwrite($logFile, $msg."\n");
}


try {

	writeLog("start");
	
	// array of videos to be populated
	$videos = array();
	
	//starting offset of requested vidoes
	$startIndex = 1;
	
	$resCount = 50;
	
	//current video index
	$index=0;
	
	//while requests results are 50 there is still more videos and we should request next 50
	//sadly we can request feed only by pages of 50 videos per page
	while($resCount==50)
	{
		// url to Stanford AI-Course user videos feed
		$url = "http://gdata.youtube.com/feeds/api/users/knowitvideos/uploads/?start-index=".$startIndex."&max-results=50";
		writeLog("url: ".$url);
		// requesting feed trough curl
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);
		curl_close($ch);
		
		//parsing xml
		$xml = simplexml_load_string($data);
		
		//checking number of videos we got on this feed page
		$resCount = count($xml->entry);
		writeLog("got data:".$resCount);
		
		
		foreach ($xml->entry as $video) {
			
			//getting video title
			$title = (string)$video->title[0];
			
			//AI-Class started to add video for blind it seems. So far decided to filter them out
			//their titles end with " ad" so I am filtering them out based on that
			if(substr($title,-3)==" ad")
				continue;
			
			//office hours are here too, decided to not inlude them in to the page
			if(strrpos($title,"office hours")!==false)
				continue;
			
			//echo $title."<br/>";
			//splitting video link to retrive ID of the video
			$parts = split("/",(string)$video->id[0]);
			$id = $parts[count($parts)-1];
			
			//doing regular expression search for all numbers in title to retrive lecture and unit numbers
			preg_match_all("/\d+/",$title,$numbers);
			
			
			 // Homeworks have sepparate numbering from lectures
			 // to make them appear at their chronological order I multiply homework number by 2
			 // so that Homework 1 unit is 2 now, and homework 2 unit is 4
			 // also I add 100 to their lecture number
			 // this way Homework 1 appears as lectures in the end of Unit 2 and so one for later Homeworks
			
			if(strrpos($title,"Homework")!==false)
			{
				$numbers[0][0]*=2;
				$numbers[0][1]+=100;
			}
			
			//there is some new preview section for exam it seems. putting it after unit 11
			if(strrpos($title,"mdpreview")!==false)
			{
				$numbers[0][0]=11;
				$numbers[0][1]+=100;
			}
			
			
			//getting unit name to group lectures by units
			$parts = split(",",$title);
			$parts = split(" ",$parts[0]);
			
			$groupName = $parts[0]." ".$parts[1];
			
			//now I get only part of title without group/unit name
			$parts 		= split(" ",$title);
			$parts 		= array_slice($parts,2);
			$newTitle 	= implode(" ", $parts);
			
			// creating associative object with video info
			$videoObj = array(    
			'title' => $newTitle,
			'id' => $id,
			'unit' => $numbers[0][0],
			'lecture'=> $numbers[0][1],
			'group'=> $groupName);

			//adding to videos array
			$videos[$index] = $videoObj;
			$index++;
		}
		$startIndex+=50;
	}
	writeLog("Finished:".$index);
	
	
	//some homeworks or answers to homeworks are not in public video list so I am adding them by hand
	$videos[count($videos)] =  array('title' => "1 Bayes Rule",					'id' => "_fJTJNK9ejY",	'unit' => 4,'lecture'=> 11,'group'=> "Homework 2");
	$videos[count($videos)] =  array('title' => "2 Simple Bayes Net",			'id' => "f6mq9rTj-Po",	'unit' => 4,'lecture'=> 12,'group'=> "Homework 2");
	$videos[count($videos)] =  array('title' => "3 Simple Bayes Net 2",			'id' => "P6WEObhmL_o",	'unit' => 4,'lecture'=> 13,'group'=> "Homework 2");
	$videos[count($videos)] =  array('title' => "4 Conditional Independence",	'id' => "pP7U6KIO9yE",	'unit' => 4,'lecture'=> 14,'group'=> "Homework 2");
	$videos[count($videos)] =  array('title' => "5 Conditional Indepedence 2",	'id' => "LMKW60DmJtc",	'unit' => 4,'lecture'=> 15,'group'=> "Homework 2");
	$videos[count($videos)] =  array('title' => "6 Parameter Count",			'id' => "8npZMwT0Sac",	'unit' => 4,'lecture'=> 16,'group'=> "Homework 2");
	
	
	$videos[count($videos)] =  array('title' => "1. Logic ",					'id' => "WP_97aspqrc",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "1. Logic Answer",				'id' => "XFR1231H0M0",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");	
	$videos[count($videos)] =  array('title' => "2. More Logic",				'id' => "P_eu1YFp9Z8",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "2. More Logic Answer",			'id' => "SiZtjEaLiE8",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "3. Vacuum World ",				'id' => "wsfXrIhDhJ0",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "3. Vacuum World Answer",		'id' => "FhowsCKPJCE",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "4. More Vacuum World",			'id' => "2H4NJg8Iiaw",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "4. More Vacuum World Answer",	'id' => "Z6QNCiMIR1I",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "5. More Vacuum World",			'id' => "i5XMOLw6CGE",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "5. More Vacuum World Answer",	'id' => "QHPs9m5qE9A",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "6. More Vacuum World",			'id' => "x93ewPQhIQc",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "6. More Vacuum World Answer",	'id' => "984YVReF6Do",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "7. More Vacuum World",			'id' => "RW-l7JWDtYQ",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "7. More Vacuum World Answer",	'id' => "mPk9fV8RZ3g",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "8. Monkey and Bananas",		'id' => "rCGAgc9smZg",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "8. Monkey and Bananas Answer",	'id' => "rZtBR-d0H5Y",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "9. Situation Calculus",		'id' => "eeDwEYxWCTA",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	$videos[count($videos)] =  array('title' => "9. Situation Calculus Answer",	'id' => "2oZexvl5fVU",	'unit' => 8,'lecture'=> 101,'group'=> "Homework 4");
	
	//$videos[count($videos)] =  array('title' => "1. Q Learning",				'id' => "Ybifm6j2SP4",	'unit' => 10,'lecture'=> 101,'group'=> "Homework 5");
	//$videos[count($videos)] =  array('title' => "2. Function Generalization",	'id' => "tpH7hp_pLqk",	'unit' => 10,'lecture'=> 101,'group'=> "Homework 5");
	//$videos[count($videos)] =  array('title' => "3. Passive RL Agent",			'id' => "212NkM6UCBc",	'unit' => 10,'lecture'=> 101,'group'=> "Homework 5");
	
	//sorting video list by chronological order using custom comparer
	usort($videos, "custom_sort");

	
	date_default_timezone_set('UTC');
	
	//creating resulting XML object
	$videosXML = new SimpleXMLElement("<videos/>");
	
	//adding update date in to it
	$videosXML->addAttribute("date", date('l jS \of F Y'));
	$groupCount = 1;
	$groupName = "";
	$groupXML;
	
	//starting to add videos information in to resulting xml
	//I am grouping videos by unit and its little bit tricky and not intuitive
	//main idea is that videos go in chronological order and all videos for one group are together
	//so I just run trough the list of videos tracking one group, when that group ends I finish to work with it and start working with next one
	foreach ($videos as $vo) {
	
		// I am grouping videos by unit or homework + I count how many videos are in each unit
		//here I check if current video group is same as group am working with
		if($groupName==$vo['group'])
		{	
			//if yes I update videos counter and continue
			$groupCount++;
		} 
		else 
		{
			//if group is different I finish working with last one and start working with new videos group
			if($groupXML)
			{
				//if there is previous group object set its videos count
				$groupXML->addAttribute("count",$groupCount);
			}
			//init new group, videos count 1, new group name etc
			$groupCount = 1;
			$groupName = $vo['group'];
			$groupXML = $videosXML->addChild("group");
			$groupXML->addAttribute("title",$groupName);
		}
		
		//create xml element for current video and set its title and id
		$videoXML = $groupXML->addChild("video");
		$videoXML["title"] = $vo['title'];
		$videoXML["id"]	   = $vo['id'];
		
		
		//request current video subtitles
		$url = "http://video.google.com/timedtext?lang=en&name=English%20via%20dotsub&v=".$vo['id'];
		$subsTXT = getRemoteFile($url);
		
		if(strlen($subsTXT)>0)
		{
			//if there are subtitles parse them
			$subsXML = simplexml_load_string($subsTXT);
			//add them in to current video xml element
			appendChild($videoXML,$subsXML);
			//retrive last subtitle
			$last = $subsXML->text[count($subsXML->text)-1];
			
			//retruve last subtitle start time and duration, sum them and use as aproximate video length 
			$videoXML->addAttribute("length",((int)$last["start"]+(int)$last["dur"]));
		}
		else
		{
			//if there is no subtitles create empty subtitles and add to current video XML element
			$subsXML = new SimpleXMLElement('<transcript><text start="0" dur="3">No subtitles...</text></transcript>');
			appendChild($videoXML,$subsXML);
			//set video length as unknown
			$videoXML->addAttribute("length","?");
		}
		writeLog($vo['group'].': '.$vo['title']);
	}
	//set last group video count
	$groupXML->addAttribute("count",$groupCount);
	
	writeLog("finished, saving");
	
	//write result in to an xml file
	$aifile = fopen("ai-class.xml", 'w') or die("can't open file");
	fwrite($aifile, $videosXML->asXML());
	fclose($aifile);

	
} catch (Exception $e) {
    die('Caught exception: '.$e->getMessage());
}
fclose($logFile);

echo "finish";
?>