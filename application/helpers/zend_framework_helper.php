<?php
// For Non-Windows
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.BASEPATH."contrib/");
require_once 'Zend/Loader.php';

/**
 * Youtube tools
 * @author César Jaldin <rodia.piedra@gmail.com>
 */
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_YouTubeService.php';
require_once "facebook-php-sdk/facebook.php";