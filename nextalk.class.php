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
		return '<script src="source/plugin/nextalk/custom.js.php" type="text/javascript"></script>';
	}
}

