<?php

/**
 * WebIM-for-DiscuzX插件入口文件
 *
 * @copyright   (C) 2014 NexTalk.IM
 * @license     http://nextalk.im/license
 * @lastmodify  2014-04-15
 */ 

// Die if PHP is not new enough
if (version_compare( PHP_VERSION, '4.3', '<' ) ) {
	die( sprintf( 'Your server is running PHP version %s but webim requires at least 4.3', PHP_VERSION ) );
}
define( 'DISABLEXSSCHECK', true );
//NOTICE: discuzX1.5 will check url and report error when url content quote
$_SERVER['REQUEST_URI'] = "";

if ( !defined('IN_DISCUZ') ) {
	require_once'../../class/class_core.php';
	$discuz = & discuz_core::instance();
	$discuz->init();
}

require DISCUZ_ROOT . './source/function/function_friend.php';
require DISCUZ_ROOT . './source/function/function_group.php';
require DISCUZ_ROOT . './source/function/function_misc.php';

//Find and insert data with utf8 client.
@DB::query( "SET NAMES utf8" );

function WEBIM_PATH() {
	global $_SERVER;
    $name = htmlspecialchars($_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF']); 
    return substr( $name, 0, strrpos( $name, '/' ) ) . "/";
}

function WEBIM_IMAGE($img) {
    return WEBIM_PATH() . "static/images/{$img}";
}

require 'env.php';
require 'config.php';

$_dbconfig = $_G['config']['db'][1];
$IMC['dbuser'] = $_dbconfig['dbuser'];
$IMC['dbpassword'] = $_dbconfig['dbpw'];
$IMC['dbname'] = $_dbconfig['dbname'];
$IMC['dbhost'] = $_dbconfig['dbhost'];
$IMC['dbprefix'] = $_dbconfig['tablepre'] . 'webim_';
unset( $_dbconfig );

if($IMC['debug']) {
    define(WEBIM_DEBUG, true);
} else {
    define(WEBIM_DEBUG, false);
}

// Modify error reporting levels to exclude PHP notices
if( WEBIM_DEBUG ) {
	error_reporting( -1 );
} else {
	error_reporting( E_ALL & ~E_NOTICE & ~E_STRICT );
}

if( !$IMC['isopen'] ) exit('WebIM Not Opened');

/**
 * load libraries
 */
require 'lib/http_client.php';
require 'lib/webim_client.class.php';
require 'lib/webim_common.func.php';
require 'lib/webim_db.class.php';
require 'lib/webim_model.class.php';
require 'lib/webim_plugin.class.php';
require 'lib/webim_router.class.php';
require 'lib/webim_app.class.php';

require 'webim_plugin_discuzx.class.php';

/**
 * webim route
 */
$app = new webim_app();

$app->plugin(new webim_plugin_discuzx());

$app->model(new webim_model());

$app->run();

?>
