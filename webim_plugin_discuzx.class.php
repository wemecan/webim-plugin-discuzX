<?php

/**
 * WebIM Plugin for DiscuzX
 */
class webim_plugin_discuzX extends webim_plugin {

    var $friend_groups; 

	/*
	 * constructor
	 */
    function __construct() {
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
    function webim_plugin_discuzX() {
        $this->__construct();
    }
    
    function user() {
        global $_G, $IMC; 
        if( !$_G['uid'] ) return null;

        //load user
        $uid = $_G['uid'];
        $user = array();
        $user['id'] = $uid;
        $user['nick'] = $this->to_utf8($_G['username'] );
        if( $IMC['show_realname'] ) {
            $data = DB::fetch_first("SELECT realname FROM ".DB::table('common_member_profile')." WHERE uid = {$uid}");
            if( $data && $data['realname'] ) {
                $u['nick'] = $data['realname'];
            }
        }
        $user['avatar'] = avatar($uid, 'small', true);
        $user['url'] = $this->profile_url( $uid );
        $user = (object)$user;
        $this->complete_status( array( $user ) );
        return $user;
    }

    /**
     * buddies of current user.
     */
    function buddies($uid) {
        global $IMC;
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
                        "nick" => $this->nick($value),
                        "group" => "manager",
                        "url" => $this->profile_url( $value['uid'] ),
                        "avatar" => avatar($value['uid'], 'small', true),
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
                            "nick" => $this->nick($value),
                            "group" => "manager",
                            "url" => $this->profile_url( $value['uid'] ),
                            "avatar" => avatar($value['uid'], 'small', true),
                        );
                    }
                }
        }
        //buddies
        if( !webim_isvid($uid) ) {
            $query = DB::query("SELECT f.fuid uid, f.fusername username, p.realname name, f.gid 
                FROM ".DB::table('home_friend')." f, ".DB::table('common_member_profile')." p
                WHERE f.uid='{$uid}' AND p.uid = f.uid 
                ORDER BY f.num DESC, f.dateline DESC");
            while ($value = DB::fetch($query)){
                if( !$this->contain_uid($admins, $value['uid']) ) {
                    $buddies[] = (object)array(
                        "id" => $value['uid'],
                        "nick" => $this->nick($value),
                        "group" => isset($value['gid']) && $value['gid'] ? $friend_groups[$value['gid']] : "manager",
                        "url" => $this->profile_url( $value['uid'] ),
                        "avatar" => avatar($value['uid'], 'small', true),
                    );
                }
            }
        }

        $rtlist = array_merge($admins, $buddies);
        $this->complete_status( $rtlist );
        return $rtlist;
    }

    function contain_uid($list, $uid) {
        foreach($list as $u) {
            if($u->id == $uid) return true; 
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
    function buddies_by_ids($uid, $ids){
        if( count($ids) === 0 ) return array();
        $uids = array();
        foreach($ids as $id) {
            if( !webim_isvid($id) ) $uids[] = $id;
        }
        if( count($uids) === 0) return array();
        $buddies  = array();
        $where_in = 'm.uid IN (' . implode(',', $uids) . ')';
        if( webim_isvid($uid) ) {
            $query = DB::query("SELECT m.uid, m.username, p.realname name FROM ".DB::table('common_member')." m
            LEFT JOIN ".DB::table('common_member_profile')." p
            ON m.uid = p.uid 
            WHERE $where_in");
        } else {
            $query = DB::query("SELECT m.uid, m.username, p.realname name, f.gid FROM ".DB::table('common_member')." m
            LEFT JOIN ".DB::table('home_friend')." f 
            ON f.fuid = m.uid AND f.uid = {$uid} 
            LEFT JOIN ".DB::table('common_member_profile')." p
            ON m.uid = p.uid 
            WHERE m.uid <> {$uid} AND $where_in");
        }
        while ( $value = DB::fetch( $query ) ){
            $buddies[] = (object)array(
                "id" => $value['uid'],
                "nick" => $this->nick($value),
                "group" => isset($value['gid']) && $value['gid'] ? $friend_groups[$value['gid']] : "stranger",
                "url" => $this->profile_url( $value['uid'] ),
                "avatar" => avatar($value['uid'], 'small', true),
            );
        }
        $this->complete_status( $buddies );
        return $buddies;
    }

    function rooms($uid) {
        if( webim_isvid($uid) ) return array();
        $ids = array();
        $query = DB::query("SELECT fid FROM ".DB::table("forum_groupuser")." WHERE uid=$uid");
        while ($value = DB::fetch($query)){
            $ids[] = $value['fid'];
        }
        return $this->rooms_by_ids($uid, $ids);
    }

    /**
     * Get room list
     * $ids: Get all imuser rooms if not given.
     *
     */
    function rooms_by_ids($uid, $ids){
        if( webim_isvid($uid)  ) return array();
        $rooms = array();
        $ids = "'".implode("','", $ids)."'";
        $where = "f.fid IN ($ids)";
        $query = DB::query("SELECT f.fid, f.name, ff.icon, ff.membernum, ff.description 
            FROM ".DB::table('forum_forum')." f 
            LEFT JOIN ".DB::table("forum_forumfield")." ff ON ff.fid=f.fid 
            WHERE f.type='sub' AND f.status=3 AND $where");
        while ($value = DB::fetch($query)){
            $rooms[] = (object)array(
                "id" => $value['fid'],
                "nick" => $value['name'],
                "url" => $this->site_url() . "forum.php?mod=group&fid=".$value['fid'],
                "avatar" => $this->site_url() . get_groupimg($value['icon'], 'icon'),
                "status" => $value['description'],
                "count" => 0,
                "all_count" => $value['membernum'],
                "blocked" => false,
            );
        }
        return $rooms;
    }

    function members($room) {
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

    /**
     * notifications of current user
     */
    function notifications($uid){
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
                $ids[] = $m->id;
                $cache[$m->id] = $m;
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
