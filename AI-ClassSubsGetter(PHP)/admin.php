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
 * This is an admin page, used to check last subs update date and start new retrival process, reports progress trough ajax request of subs output
 * main code is in main.js file
*/ 

?>



<!DOCTYPE html>
    <html>
    <head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<title>AI-Class subs admin panel</title>
		<script src="main.js" type="text/javascript"></script>
	</head>
	<body>
	 	<?php
			/**
			 * If there is old subs file open it and retrive last update date
			*/ 
			if (file_exists('ai-class.xml')) {
				$xml = simplexml_load_file('ai-class.xml');
				echo "<p id='lastupdate'>Last update:".$xml['date']."</p>";
			}
		?>
		<button type="button" id="updateBtn">Update</button>
		<div id="console"/>
	</body>
</html>
