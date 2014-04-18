<?php

/**
 * WebIM Plugin for DiscuzX
 */
class webim_plugin_discuzX extends webim_plugin {

    var $user = null;

    var $friend_groups; 

	/*
	 * constructor
	 */
    public function __construct() {
        parent::__construct();
        //Cache friend_groups;
        $this->friend_groups = friend_group_list();
        foreach($this->friend_groups as $k => $v){
            $this->friend_groups[$k] = $this->to_utf8($v);
        }
	}

	/*
	 * old constructor
	 */
    public function webim_plugin_discuzX() {
        $this->__construct();
    }
    
	protected function load_uid() {
        global $_G; return $_G['uid'];
	}

	protected function load_user($uid) {
        global $_G, $IMC;
        $user = array();
        $user['id'] = $uid;
        $user['uid'] = $uid;
        $name = $this->to_utf8($_G['username'] );
        $user['nick'] = $name;
        if( $IMC['show_realname'] ) {
            $data = DB::fetch_first("SELECT realname FROM ".DB::table('common_member_profile')." WHERE uid = {$uid}");
            if( $data && $data['realname'] ) {
                $u['nick'] = $data['realname'];
            }
        }
        $user['pic_url'] = avatar($uid, 'small', true);
        $user['url'] = $this->profile_url( $uid );
        $user['role'] = 'user';
        $user = (object)$user;
        $this->complete_status( array( $user ) );
        return $user;
    }

	protected function load_visitor() {
        require_once 'lib/IP.class.php';
        if ( isset($_COOKIE['_webim_visitor_id']) ) {
            $id = $_COOKIE['_webim_visitor_id'];
        } else {
            $id =  substr(uniqid(), 6);
            setcookie('_webim_visitor_id', $id, time() + 3600 * 24 * 30, "/", "");
        }
        $vid = $this->vid($id);
        $data = DB::fetch_first("SELECT id from ".DB::table('webim_visitors')." WHERE name = '$vid'");
        $ip = isset($_SERVER['X-Forwarded-For']) ? $_SERVER['X-Forwarded-For'] : $_SERVER["REMOTE_ADDR"];
        $loc = implode('',  IP::find($ip) );
        if( !($data && $data["id"]) ) {
            //var_dump($_SERVER);
            DB::insert('webim_visitors', array(
                "name" => $vid,
                "ipaddr" => $ip,
                "url" => $_SERVER['REQUEST_URI'],
                "referer" => $_SERVER['HTTP_REFERER'],
                "location" => $loc,
                "created" => date( 'Y-m-d H:i:s' ),
            ));
        }
        return (object)array(
            'id' => $vid,
            'uid' => $vid,
            'nick' => "v".$id,
            'group' => "visitor",
            'presence' => 'online',
            'pic_url' => avatar($vid, 'small', true),
            'role' => 'visitor',
            'url' => "#",
            'status' => $loc
        );
    }

    /**
     * buddies of current user.
     */
    public function buddies() {
        global $IMC;
        $uid = $this->uid();
        $admins = array();
        $buddies = array();
        //addmins
        if( $IMC['admin_as_buddy'] ) {
            $query = DB::query("SELECT m.uid, m.username, p.realname name FROM ".DB::table('common_member')." m
                LEFT JOIN ".DB::table('common_member_profile')." p
                ON m.uid = p.uid 
                WHERE m.allowadmincp = 1");
            while ($value = DB::fetch($query)){
                if($value['uid'] != $uid) {
                    $admins[] = (object)array(
                        "id" => $value['uid'],
                        "uid" => $value['uid'],
                        "nick" => $this->nick($value),
                        "group" => "manager",
                        "url" => $this->profile_url( $value['uid'] ),
                        "pic_url" => avatar($value['uid'], 'small', true),
                    );
                }
            }
        } 
        if(isset($IMC['admin_uids']) and $IMC['admin_uids'] !== '') {
            $query = DB::query("SELECT m.uid, m.username, p.realname name FROM ".DB::table('common_member')." m
                LEFT JOIN ".DB::table('common_member_profile')." p
                ON m.uid = p.uid 
                WHERE m.uid in ({$IMC['admin_uids']})");
                while ($value = DB::fetch($query)){
                    if($value['uid'] != $uid and !$this->contain_uid($admins, $uid)) {
                        $admins[] = (object) array(
                            "id" => $value['uid'],
                            "uid" => $value['uid'],
                            "nick" => $this->nick($value),
                            "group" => "manager",
                            "url" => $this->profile_url( $value['uid'] ),
                            "pic_url" => avatar($value['uid'], 'small', true),
                        );
                    }
                }
        }
        //buddies
        if( !$this->is_visitor() ) {
            $query = DB::query("SELECT f.fuid uid, f.fusername username, p.realname name, f.gid 
                FROM ".DB::table('home_friend')." f, ".DB::table('common_member_profile')." p
                WHERE f.uid='{$uid}' AND p.uid = f.uid 
                ORDER BY f.num DESC, f.dateline DESC");
            while ($value = DB::fetch($query)){
                if( !$this->contain_uid($admins, $value['uid']) ) {
                    $buddies[] = (object)array(
                        "id" => $value['uid'],
                        "uid" => $value['uid'],
                        "nick" => $this->nick($value),
                        "group" => isset($value['gid']) && $value['gid'] ? $friend_groups[$value['gid']] : "manager",
                        "url" => $this->profile_url( $value['uid'] ),
                        "pic_url" => avatar($value['uid'], 'small', true),
                    );
                }
            }
        }

        $rtlist = array_merge($admins, $buddies);
        $this->complete_status( $rtlist );
        return $rtlist;
    }

    private function contain_uid($list, $uid) {
        foreach($list as $u) {
            if($u->uid == $uid) return true; 
        }
        return false;
    }

    /**
     * buddies list from given ids
     * $ids:
     *
     * Example:
     * 	buddy_by_ids(array(1,2,3));
     *
     */
    function buddies_by_ids($ids){
        if(empty($ids)) return array();
        $vids = array();
        $uids = array();
        foreach($ids as $id) {
            if( strpos($id, "vid:") === 0 ) {
                $vids [] = $id;
            } else {
                $uids[] = $id;
            }
        }
        $buddies  = array();
        if( count($uids) ) {
            $where_in = 'm.uid IN (' . implode(',', $uids) . ')';
            if( $this->is_visitor() ) {
                $query = DB::query("SELECT m.uid, m.username, p.realname name FROM ".DB::table('common_member')." m
                LEFT JOIN ".DB::table('common_member_profile')." p
                ON m.uid = p.uid 
                WHERE $where_in");
            } else {
                $query = DB::query("SELECT m.uid, m.username, p.realname name, f.gid FROM ".DB::table('common_member')." m
                LEFT JOIN ".DB::table('home_friend')." f 
                ON f.fuid = m.uid AND f.uid = {$this->uid()} 
                LEFT JOIN ".DB::table('common_member_profile')." p
                ON m.uid = p.uid 
                WHERE m.uid <> {$this->uid()} AND $where_in");
            }
            while ( $value = DB::fetch( $query ) ){
                $buddies[] = (object)array(
                    "id" => $value['uid'],
                    "uid" => $value['uid'],
                    "nick" => $this->nick($value),
                    "group" => isset($value['gid']) && $value['gid'] ? $friend_groups[$value['gid']] : "stranger",
                    "url" => $this->profile_url( $value['uid'] ),
                    "pic_url" => avatar($value['uid'], 'small', true),
                );
            }
            $this->complete_status( $buddies );
        }
        if( count( $vids) ) {
            foreach ($vids as $vid) {
                $data = DB::fetch_first("SELECT ipaddr, location from ".DB::table('webim_visitors')." WHERE name = '$vid'");
                $status = '';
                if($data && $data['location']) {
                    $status = $status . $data['location'] . '(' . $data['ipaddr'] .')';
                }
                $buddies[] = (object)array(
                    "id" => $vid,
                    "uid" => $vid,
                    "nick" => "v".substr($vid, 4), //remove vid:
                    "group" => "visitor",
                    "url" => "#",
                    "pic_url" => WEBIM_IMAGE('male.png'),
                    "status" => $status, 
                );
            }
        }
        return $buddies;
    }

    public function rooms() {
        if( $this->is_visitor() ) return array();
        $ids = array();
        $uid = $this->uid();
        $query = DB::query("SELECT fid FROM ".DB::table("forum_groupuser")." WHERE uid=$uid");
        while ($value = DB::fetch($query)){
            $ids[] = $value['fid'];
        }
        return $this->rooms_by_ids($ids);
    }

    /**
     * Get room list
     * $ids: Get all imuser rooms if not given.
     *
     */
    function rooms_by_ids($ids){
        if( $this->is_visitor() ) return array();
        $rooms = array();
        $ids = "'".implode("','", $ids)."'";
        $where = "f.fid IN ($ids)";
        $query = DB::query("SELECT f.fid, f.name, ff.icon, ff.membernum, ff.description 
            FROM ".DB::table('forum_forum')." f 
            LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid 
            WHERE f.type='sub' AND f.status=3 AND $where");
        while ($value = DB::fetch($query)){
            $rooms[] = (object)array(
                "fid" => $value['fid'],
                "id" => $value['fid'],
                "nick" => $value['name'],
                "url" => $this->site_url() . "forum.php?mod=group&fid=".$value['fid'],
                "pic_url" => $this->site_url() . get_groupimg($value['icon'], 'icon'),
                "status" => $value['description'],
                "count" => 0,
                "all_count" => $value['membernum'],
                "blocked" => false,
            );
        }
        return $rooms;
    }

    public function members($room) {
        $query = DB::query("SELECT uid, username FROM ". DB::table("forum_groupuser")." WHERE fid=$room");
        $members = array();
        while ( $value = DB::fetch($query) ){
            $members[] = (object) array(
                'id' => $value['uid'],
                'nick' => $value['username']
            );
        }
        return $members;
    }

    public function notifications(){
        return array();
    }
    
    /**
     * Add status to member info.
     *
     * @param array $members the member list
     * @return 
     *
     */
    function complete_status( $members ) {
        if( count($members) ){
            $ids = array();
            $cache = array();
            foreach($members as $m) {
                $ids[] = $m->uid;
                $cache[$m->uid] = $m;
            }
            $ids = implode(",", $ids);
            $query = DB::query("SELECT uid, spacenote FROM ".DB::table('common_member_field_home')." WHERE uid IN ($ids)");
            while($row = DB::fetch($query)) {
                $cache[$row['uid']]->status = $row['spacenote'];
            }
        }
        return $members;
    }

    function site_url() {
        return dirname ( dirname ( dirname( WEBIM_PATH() ) ) ) . "/";
    }

    function profile_url( $id ) {
        return $this->site_url() . "home.php?mod=space&uid=" . $id;
    }

    function nick($sp) {
        global $_IMC;
        return (!$_IMC['show_realname']||empty($sp['name'])) ? $sp['username'] : $sp['name'];
    }

    function to_utf8( $s ) {
        if( strtoupper( CHARSET ) == 'UTF-8' ) {
            return $s;
        } else {
            if ( function_exists( 'iconv' ) ) {
                return iconv( CHARSET, 'utf-8', $s );
            } else {
                require_once DISCUZ_ROOT . './source/class/class_chinese.php';
                $chs = new Chinese( CHARSET, 'utf-8' );
                return $chs->Convert( $s );
            }
        }
    }

    function from_utf8( $s ) {
        if( strtoupper( CHARSET ) == 'UTF-8' ) {
            return $s;
        } else {
            if ( function_exists( 'iconv' ) ) {
                return iconv( 'utf-8', CHARSET, $s );
            } else {
                require_once DISCUZ_ROOT . './source/class/class_chinese.php';
                $chs = new Chinese( 'utf-8', CHARSET );
                return $chs->Convert( $s );
            }
        }
    }

}

?>
