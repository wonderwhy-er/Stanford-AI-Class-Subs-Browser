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

$f1 = filemtime("index.php");
$f2 = filemtime("ai-class.xml");
$lastTime = $f1>$f2?$f1:$f2;
header("Last-Modified: ".gmdate("D, d M Y H:i:s", $lastTime)." GMT");
if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastTime)
{
	header("HTTP/1.1 304 Not Modified");
    exit; 
}

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
		<script src="jquery-1.7.min.js" type="text/javascript"></script>
		<script src="swfobject.js" type="text/javascript"></script>
		<script type="text/javascript">
			<?php
			/*
			 * Added function to track JS errors
			 */
			?>	
			function logError(txt)
			{
				//alert(txt);
				if(window.XMLHttpRequest){
					obj = new XMLHttpRequest();
				} else if(window.ActiveXObject) {
					obj = new ActiveXObject("Microsoft.XMLHTTP");
				} 
				obj.open("GET", "errorLog.php?error="+txt, true);
				obj.send(null);
			}
			<?php
			/*
			 * Global error handler, seems to not work on mobile and have other issues
			 */
			?>				
			window.onerror=function(message, url, lineNumber){
				if(url && url!="")
					logError("line: "+lineNumber+" url: "+url+" error: "+message);
				return true;
			}
		</script>
	 </head>
	 
	<body>
	
	

	<div id="PlayerPanel">
	<div id="player">
    You need Flash player 8+ and JavaScript enabled to view this video.
	</div>
	</div>
	
	<div id="menu">
	<a class="btn" id="exall"><span class="btnspan">Expand all</span></a><a class="btn" id="clall"><span class="btnspan">Collapse all</span></a><a class="btn" id="about" href="http://blog.wonderwhy-er.com/subs-browser-for-stanford-ai-class/"><span class="btnspan">About</span></a>
	</div>
	
	<div id="SubsPanel">
	<?php
	/*
	 *  That's where we load XML files with videos and captions and output it as list of tables
	 *	Information about video to play and where to play it is added in to a button id field, a hack but I don't know how better to do it
	 *	Also videos are grouped by unit or homework and info about viodes length is take from last caption, if there is no caption it is left as unknown ??:??
	 */	
		if (file_exists('ai-class.xml')) {
			$xml = simplexml_load_file('ai-class.xml');
			echo "<p id='lastupdate'>Last update:".$xml['date']."</p>";
			
			$videoID = 0;
			$groupID = 0;
			echo '<ul id="groups">';
			foreach($xml->group as $group)
			{
				$videoCount = count($group->video);
				echo '<li class="group" id="group'.$groupID.'"><a id="groupExpand'.$groupID.'" class="expandbtn plus"></a><h2>'.'('.$videoCount.') '.$group['title'].'</h2>';
				echo '<div class="texts hidden">';
				echo '<ul id="titles">';
				foreach ($group->video as $video) {
					
					$length = $video['length'];
					if($length=="?")
						$length = "<span>((??:??)) </span>";
					else
						$length = "<span>(".sec2hms($length).") </span>";
					
					echo '<li class="title" id="section'.$videoID.'"><a id="videoExpand'.$videoID.'" class="expandbtn plus"></a><h3>'.$length.$video['title'].'</h3>';
					echo '<table class="texts hidden">';
					foreach ($video->transcript->text as $text) {
						echo '<tr class="text"><td><span class="substext">'.$text.'</span></td><td><a class="btn play" id="'.$video['id'].'|'.$text['start'].'"><span class="btnspan" unselectable="on">&#9654; '.sec2hms($text['start']).'</span></a></td></tr>';
					}
					echo '</table></li>';
					$videoID++;
				}
				echo "</ul></div>\n";
				$groupID++;
			}
			echo "</ul>\n";
		} else {
			exit('Failed to open ai-class.xml.');
		}
	?>
	
	<script>


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
		try {
			var button  = $("#"+e.currentTarget.id);	
			var sectionText = $("#"+e.currentTarget.parentNode.id+" > .texts");
			button.toggleClass("plus").toggleClass("minus");
			if(button.hasClass("plus"))
			{
				sectionText.hide();
			}
			else
			{
				sectionText.show();
			}
		}
		catch(err)
		{
			logError("expandClick:"+err);
		}
	}
	
	
	<?php
	/*
	 * This function is called when YouTube player loaded and is ready to get commands from JS
	 */
	?>	
	var ytplayer = null;
	function onYouTubePlayerReady(playerId) {
	
		try
		{
			if (playerId && playerId != 'undefined') {
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
		catch(err)
		{
			logError("onYouTubePlayerReady:"+err);
		}
    }
	
	
	
		/*
		 * On clicking play button I retrive its id, from there I get id of video to play and a moment from which to play it
		 * Then call YouTube/Flash player and pass those paremeters in
		 * It may be that player failed to initialize properly, in this case there will be some kind of error 
		 */	
		function textClick(e)
		{
			try
			{
				var elementID =  e.currentTarget.id;
				var parts = elementID.split('|');
				var videoID = parts[0];
				var start = parts[1];
				ytplayer.loadVideoById(videoID,start);
			}
			catch(err)
			{
				logError("textClick:"+err);
			}
		}
		
		try {
		
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
		}
		catch(err)
		{
			logError("jQuery event registration:"+err);
		}
	</script>
	</div>
	
	</body>
</html>
  


