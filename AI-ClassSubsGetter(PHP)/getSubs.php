<?php

/**
 * A test of getting subs for video trough PHP using their public source
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
 * This is part of experiments for making a caption + video page of you tube videos
 * Sadly requests from PHP to publuc source of subs failes for reason I do not understand
 * It return error 400, malformed or illegal request
 * Partially I suspect they disallow such scrapping by PHP scripts, but may be I am wrong
 */ 
 
 /**
 * As usual fopen and curl did not work,
 * I tried this script from php.net that tries to spoof usual browser request
 * It just that from your browser requesting that url returns subtitles, same with desktop app
 * But mot php script, I wonder what is wrong
 */
function curl_spoof($url) 
{

        $curl = curl_init($url);

	//set some headers if you want 
        $header[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
 
        //curl_setopt($curl, CURLOPT_URL, $url);
	
	//Spoof the agent
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.6) Gecko/20100107 Fedora/3.5.6-1.fc12 Firefox/3.5.6');

	//Spoof the Referer
        curl_setopt($curl, CURLOPT_REFERER, 'http://www.idontexist.com');


        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
 
        if (!$html = curl_exec($curl))
	{ 
		$html = file_get_contents($url); 
	}
        curl_close($curl);
	return $html;
}

try {
	
 	echo "start";
	$id = "cx3lV07w-XE";
	$url = "http://video.google.com/timedtext?lang=en&name=English%20via%20dotsub&v=".$id;
	echo "<br/>subs url: <a href='".$url."'>".$url."</a>";
	echo "<br/>".curl_spoof($url);

} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>