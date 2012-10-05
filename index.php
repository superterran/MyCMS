<pre>
<?php

ini_set('display_errors','On'); 
error_reporting(E_ALL);

/*

This is a stupid little framework that attempts to do the following:

	* .htaccess funnels all requests to index.php where we 
	  instantiate the framework, which could check to see if
	  a login token is set, determine if this is a browser based request
	  or something else like an api or a terminal command 

	* the framework parses the url query/parameters passed in
	  and determines what it is the user is trying to accomplish,
	  fires off whatever is needed to perform the request and 
	  builds whatever output is required to complete

	* the framework serves up the output in a template, all the 
	  links/actions to use the served web app are urls that 
	  use the framework's url parsing, ensuring that everything
	  is handled through the framework in a common way 
			
*/

require_once('mycms/main.php'); // load the main class

$mycms = new mycms();  // instatiate the framework

$mycms->init(); // does most of the work





// at this point, no headers should be sent to the browser...

$mycms->renderOutput(); // render the output

