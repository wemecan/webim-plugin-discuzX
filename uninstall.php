<?php

/**
 * Author: Hidden
 * Date: Mon Aug 23 21:27:07 CST 2010
 *
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS `cdb_webim_settings`;
DROP TABLE IF EXISTS `cdb_webim_histories`;
DROP TABLE IF EXISTS `cdb_webim_rooms`;
DROP TABLE IF EXISTS `cdb_webim_members`;
DROP TABLE IF EXISTS `cdb_webim_blocked`;
DROP TABLE IF EXISTS `cdb_webim_visitors`;

EOF;

runquery($sql);

$finish = TRUE;
