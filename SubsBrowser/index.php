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
 * This is a page of subs browser for Stanford AI-Class
 * It was made to allow reading captions for their lectures along with the video
 * To be able to search trough captions and jump to exact moment in video where something is dicussed
 */

  // function to format time, borrowed from php.net and modified for my needs a little
  function sec2hms ($sec, $padHours = false) 
  {

    // start with a blank string
    $hms = "";
    
    $minutes = intval(($sec / 60) % 60); 

    // add minutes to $hms (with a leading 0 if needed)
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

    // seconds past the minute are found by dividing the total number of seconds
    // by 60 and using the remainder
    $seconds = intval($sec % 60); 

    // add seconds to $hms (with a leading 0 if needed)
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    // done!
    return $hms;
    
  }

?>


<!DOCTYPE html>
    <html>
    <head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<title>AI-Class subs browser</title>
		<meta name="description" content="Just interface I made for browsing and reading stanford ai-class lectures and subs with more ease" />
		<meta name="keywords" content="ai-class, stanford, subs, notes, browser, interface" />
		<meta name="author" content="content="Eduard Ruzga"/>
		<meta name="viewport" content="width=100%; initial-scale=1; maximum-scale=1; minimum-scale=1; user-scalable=no;"/>
		<link rel="stylesheet" type="text/css" href="Style.css" />
		<link rel="icon"  type="image/png" href="favicon.ico">
		<script src="https://www.google.com/jsapi?key=ABQIAAAAmn_0VvpPaGcNmWj-N8m5zBRvR6kMDk4ce0h09wmKFr6gKHrTZxQAf0Vt6M1xsqov8hmnfsGeFKX3yw" type="text/javascript"></script>
		<script type="text/javascript">
			google.load("jquery", "1.6.3");
			google.load("swfobject", "2.1");
		</script>
	 </head>
	 
	<body>
	
	

	<div id="PlayerPanel">
	<div id="player">
    You need Flash player 8+ and JavaScript enabled to view this video.
	</div>
	<script type="text/javascript">
	
    var params = { allowScriptAccess: "always" };
    var atts = { id: "player" };
    swfobject.embedSWF("http://www.youtube.com/e/BnIJ7Ba5Sr4?enablejsapi=1&playerapiid=player",
                     "player", "100%", "100%", "8", null, null, params, atts);
					   
	<?php
	/*
	 * JS Function for opening and closing lectures using jQuery
	 */
	?>
	function expandClick(e)
	{
		var button  = $("#"+e.currentTarget.id);	
		var sectionText = $("#"+e.currentTarget.parentNode.id+" .texts");
		button.toggleClass("plus").toggleClass("minus");
		if(button.hasClass("plus"))
		{
			sectionText.slideUp(400);
		}
		else
		{
			sectionText.slideDown(400);
		}
	}
	
	
	<?php
	/*
	 * This function is called when YouTube player loaded and is ready to get commands from JS
	 */
	?>	
	function onYouTubePlayerReady(playerId) {
		
		if (playerId && playerId != 'undefined') {
			console.log('onYouTubePlayerReady:'+playerId);
			
			ytplayer = document.getElementById(playerId);
			
		}
		else
		{
			logError("PlayerID not returned");
		}
		
		if(!ytplayer)
		{
			logError("Player not initialised");
		}
		else
		{
			if(!ytplayer.loadVideoById)
			{
				logError("Player function not set");
			}
		}
    }
	
	<?php
	/*
	 * Added function to track errors with YouTube, so far only problems are with 
	 * iOS devices where there is no Flash and no YouTube JS API for Flashless videos :(
	 */
	?>	
	function logError(txt)
	{
		console.log("Error:"+txt);
		alert(txt);
		if(window.XMLHttpRequest){
			obj = new XMLHttpRequest();
		} else if(window.ActiveXObject) {
			obj = new ActiveXObject("Microsoft.XMLHTTP");
		} 
		obj.open("GET", "errorLog.php?error="+txt, true);
		obj.send(null);
	}
	</script>
	</div>
	
	<div id="menu">
	<a class="btn" id="exall"><span class="btnspan">Expand all</span></a><a class="btn" id="clall"><span class="btnspan">Collapse all</span></a><a class="btn" id="about" href="http://blog.wonderwhy-er.com/subs-browser-for-stanford-ai-class/"><span class="btnspan">About</span></a>
	</div>
	
	<div id="SubsPanel">
	<?php
	/*
	 *  That's where we load XML files with videos and captions and output it as list of tables
	 *	Information about video to play and where to play it is added in to button id field, a hack but I don't know how better to do it
	 */	
		if (file_exists('ai-class.xml')) {
			$xml = simplexml_load_file('ai-class.xml');
			echo "<p id='lastupdate'>Last update:".$xml['date']."</p>";
			echo '<ul id="titles">';
			$id = 0;
			foreach ($xml->video as $video) {
				echo '<li class="title" id="section'.$id.'"><a id="expand'.$id.'" class="expandbtn plus"></a><h2>'.$video['title'].'</h2>';
				echo '<table class="texts hidden">';
				foreach ($video->transcript->text as $text) {
					echo '<tr class="text"><td><span class="substext">'.$text.'</span></td><td><a class="btn play" id="'.$video['id'].'|'.$text['start'].'"><span class="btnspan">&#9654; '.sec2hms($text['start']).'</span></a></td></tr>';
				}
				echo '</table></li>';
				$id++;
			}
			echo "</ul>\n";
		} else {
			exit('Failed to open ai-class.xml.');
		}
	?>
	
	<script>
		/*
		 * On clicking play button I retrive its id, from there I get id of video to play and a moment from which to play it
		 * Then call YouTube/Flash player and pass those paremeters in
		 * It may be that player failed to initialize properly, in this case there will be some kind of error 
		 */	
		function textClick(e)
		{
			var elementID =  e.currentTarget.id;
			var parts = elementID.split('|');
			var videoID = parts[0];
			var start = parts[1];
			
			try
			{
				ytplayer.loadVideoById(videoID,start);
			}
			catch(err)
			{
				logError(err);
			}
		}
		$('.play').click(textClick);
		

		$('.expandbtn').click(expandClick);
		
		$('#exall').click(function(){
			$(".texts").show();
			$(".expandbtn").removeClass("plus").addClass("minus");
		});
		$('#clall').click(function(){
			$(".texts").hide();
			$(".expandbtn").removeClass("minus").addClass("plus");
		});
	</script>
	</div>
	
	</body>
</html>
  


