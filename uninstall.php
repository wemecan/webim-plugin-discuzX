<?php

defined('IN_DISCUZ') or exit('Access Denied');

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
