<?php
/**
 * MyCMS is a simple framework that allows for the rapid development php based apps.
 *
 * @package MyCMS
 * @url https://github.com/superterran/MyCMS
 * @author Doug Hatcher superterran@gmail.com
 * @copyright http://creativecommons.org/licenses/by/3.0/deed.en_US
 */


ini_set('display_errors','On');
error_reporting(E_ALL);


/**
 * This is the framework class, http means it's for rendering browser content, dive in to understand how it works.
 * @see mycms
 */
require_once('mycms/http.php');

/**
 * Instantiate the framework, this will spin it up and get it to the point
 * where we can start working through the browser request.
 * @see mycms
 */
$mycms = new mycms_http();

/**
 * At this point the framework is spun up. Now let's work out the request
 * @see mycms::init();
 */
$mycms->init();


/**
 * Everything is done except for generating the HTML to complete.
 */
$mycms->renderOutput();

