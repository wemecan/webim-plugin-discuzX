<?php

/**
 * Author: Hidden
 * Date: Mon Aug 23 22:25:15 CST 2010
 *
 */
defined('IN_DISCUZ') or exit('Access Denied');

class plugin_webim {

	function global_footer() {
		global $_G;
        list($usec, $sec) = explode(" ", microtime());
		return "<script src=\"source/plugin/webim/index.php?action=boot&ts=${sec}\" type=\"text/javascript\"></script>";
	}

}

