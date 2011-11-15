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

// constructor for HTTP requests object
function createXMLHttpRequest() {
    // Mozilla/Chrome/Safari/IE7+ (normal browsers)
    try { return xmlhttp = new XMLHttpRequest(); } catch (e1) {};
	
	XMLHTTP_IDS = [ 'MSXML2.XMLHTTP.5.0', 'MSXML2.XMLHTTP.4.0',
					'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP', 'Microsoft.XMLHTTP' ];
	for (i = 0; i < XMLHTTP_IDS.length && !success; i++) {
		try { return new ActiveXObject(XMLHTTP_IDS[i]);}
		catch (e2) {}
	}
    return null;
};

// creating main http requests object
var http = createXMLHttpRequest();
if(http==null) alert("failed to create http request");

var out; // variable for html object in to which output will be written

var intervalID = false; // ticker id that will be requestion progress updates

// function to check if string ends with some other string
function endsWith(str, suffix) {
    return str.indexOf(suffix, str.length - suffix.length) !== -1;
}

//when page loaded 
window.onload=function()
{
	//get div that will be used for progress output
	out = document.getElementById("console");
	//button for starting subs update script
	var update=document.getElementById("updateBtn");
	
	//function to start update
	update.onclick = function()
	{
		
		var getSubs = createXMLHttpRequest();
		
		//calling update script
		getSubs.open('GET', 'getSubs.php', true);
		//when this request returns stop polling
		getSubs.onreadystatechange = function()
		{
			if (http.readyState === 4) {
				if (http.status === 200) {
					stopUpdates();
					update();
				}
			}
		};
		getSubs.send(null);
		//after a second start polling for progress, needed so that update script will have time to start outputing something
        var t=setTimeout("startUpdates()",1000);
	}
}


function startUpdates()
{
	if (!intervalID) {
        intervalID = window.setInterval('update()', 1000);
    }
}


function stopUpdates()
{
	window.clearInterval(intervalID);
    intervalID = false;
}


function update()
{
	//requestion subs update script outpute file
	http.open('GET', 'subslog.txt', true);
	http.onreadystatechange = gotProgress;
    http.send(null);  
}

function gotProgress()
{
	
    if (http.readyState === 4) {
        if (http.status === 200) { 
			//write progress log in to DOM object
			out.innerHTML = this.responseText.split("\n").join("<br/>");
        }
        else 
		{
            out.innerHTML = 'Error:[' + http.status + ']' + http.statusText;
        }
    }	
}



