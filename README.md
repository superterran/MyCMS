MyCMS
=============

This is a framework which allows for the rapid development of php based web applications.

How it works
------------

	* .htaccess funnels all requests to index.php where we
	  instantiate the framework

	* the framework parses the url query/parameters and determines
	  what it is the user is trying to accomplish, then
	  fires off whatever is needed to perform the request and
	  builds whatever output is required to complete

	* the framework serves up the output in a template, all the
	  links/actions to use the served web app are urls that
	  use the framework's url parsing, ensuring that everything
	  is handled through the framework in a common way


Installation
------------

    clone this to your system, point a vhost to the mycms' dir and load up in a web browser.

Requirements
------------

    Apache2
    PHP 5.3+
    SimpleXML

