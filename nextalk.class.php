<?php

/**
 * Author: Hidden
 * Date: Mon Aug 23 22:25:15 CST 2010
 *
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_nextalk {

	function global_footer() {
		global $_G;
        list($usec, $sec) = explode(" ", microtime());
		return "<script src=\"source/plugin/nextalk/boot.js.php?ts=${sec}\" type=\"text/javascript\"></script>";
	}
}

