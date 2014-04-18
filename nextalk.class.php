<?php

/**
 * Author: Hidden
 * Date: Mon Aug 23 22:25:15 CST 2010
 *
 */
defined('IN_DISCUZ') or exit('Access Denied');

class plugin_nextalk {

	function global_footer() {
		global $_G;
        list($usec, $sec) = explode(" ", microtime());
		return "<script src=\"source/plugin/nextalk/index.php?action=boot&ts=${sec}\" type=\"text/javascript\"></script>";
	}

}

