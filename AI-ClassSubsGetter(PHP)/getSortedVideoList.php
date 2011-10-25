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

try {

	// array of videos to be populated
	$vidoes = array();
	
	//starting offset of requested vidoes
	$startIndex = 1;
	
	$resCount = 50;
	$index=0;
	
	//while requests results are 50 there is still more videos and we should request next 50
	//sadly we can request feed only by pages of 50 videos per page
	while($resCount==50)
	{
		// url to Stanford AI-Course user videos feed
		$url = "http://gdata.youtube.com/feeds/api/users/knowitvideos/uploads/?start-index=".$startIndex."&max-results=50";
		echo "url: ".$url;
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
		echo "<br/>got data:".$resCount;
		
		
		foreach ($xml->entry as $video) {
			
			//getting video title
			$title = (string)$video->title[0];
			
			//splitting video link to retrive ID of the video
			$parts = split("/",(string)$video->id[0]);
			$id = $parts[count($parts)-1];
			
			//doing regular expression search for all numbers in title to retrive lecture and unit numbers
			preg_match_all("/\d+/",$title,$numbers);
			
			/*
			 * Homeworks have sepparate numbering from lectures
			 * to make them appear at their chronological order I multiply homework number by 2
			 * so that Homework 1 unit is 2 now, and homework 2 unit is 4
			 * also I add 100 to their lecture number
			 * this way Homework 1 appears as lectures in the end of Unit 2 and so one for later Homeworks
			 */
			if(strrpos($title,"Homework")!==false)
			{
				$numbers[0][0]+=1;
				$numbers[0][1]+=100;
			}

			// creating associative object with video info
			$videoObj = array(    
			'title' => $title,
			'id' => $id,
			'unit' => $numbers[0][0],
			'lecture'=> $numbers[0][1]);
			
			
			
			
			$videos[$index] = $videoObj;
			$index++;
		}
		$startIndex+=50;
	}
	echo "<br/> Finished:".$index;
	
	//sorting video list by chronological order using custom comparer
	usort($videos, "custom_sort");
     
	//outputing video titles
	foreach ($videos as $vo) {
		echo "<br/>".$vo['title'];
	}


} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>