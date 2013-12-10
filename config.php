<?php

/**
 * Author: Hidden
 * Date: Mon Aug 23 22:25:15 CST 2010
 *
 */
$_IMC = array();

/*
|--------------------------------------------------------------------------
| 插件版本
|--------------------------------------------------------------------------
*/
$_IMC["version"] = "@VERSION";

/*
|--------------------------------------------------------------------------
| 調試
|--------------------------------------------------------------------------
*/
$_IMC["debug"] = false;

/*
|--------------------------------------------------------------------------
| 开启Webim
|--------------------------------------------------------------------------
*/
$_IMC["isopen"] = true;

/*
|--------------------------------------------------------------------------
| 网站域名，NexTalk.IM註冊
|--------------------------------------------------------------------------
*/
$_IMC["domain"] = "";

/*
|--------------------------------------------------------------------------
| 消息服務器通信APIKEY, NexTalk.IM註冊申請
|--------------------------------------------------------------------------
*/
$_IMC["apikey"] = "";

/*
|--------------------------------------------------------------------------
| 消息服务器地址
|--------------------------------------------------------------------------
*/
$_IMC["host"] = "nextalk.im";

/*
|--------------------------------------------------------------------------
| 消息服務器端口
|--------------------------------------------------------------------------
*/
$_IMC["port"] = 8000;

/*
|--------------------------------------------------------------------------
| 界面主题，根据webim/static/themes/目录内容选择
|--------------------------------------------------------------------------
*/
$_IMC["theme"] = "base";

/*
|--------------------------------------------------------------------------
| 本地语言，扩展请修改webim/static/i18n/内容
|--------------------------------------------------------------------------
*/
$_IMC["local"] = "zh-CN";

/*
|--------------------------------------------------------------------------
| 是否显示好友真实姓名
|--------------------------------------------------------------------------
*/
$_IMC["show_realname"] = false;

/*
|--------------------------------------------------------------------------
| 群组聊天
|--------------------------------------------------------------------------
*/
$_IMC["enable_room"] = true;
	
/*
|--------------------------------------------------------------------------
| 页面名字旁边的聊天链接
|--------------------------------------------------------------------------
*/
$_IMC["enable_chatlink"] = true;

/*
|--------------------------------------------------------------------------
| 支持工具栏快捷方式
|--------------------------------------------------------------------------
*/
$_IMC["enable_shortcut"] = false;

/*
|--------------------------------------------------------------------------
| 表情主题
|--------------------------------------------------------------------------
*/
$_IMC["emot"] = "default";

/*
|--------------------------------------------------------------------------
| Toolbar背景透明度设置
|--------------------------------------------------------------------------
*/
$_IMC["opacity"] = 80;

/*
|--------------------------------------------------------------------------
| 显示工具条
|--------------------------------------------------------------------------
*/
$_IMC['enable_menu'] = false; 

/*
|--------------------------------------------------------------------------
| 允许未登录时显示IM，并可从IM登录
| 
| DEPRECATED: 该配置已取消
|--------------------------------------------------------------------------
*/
$_IMC['enable_login'] = false; 

/*
|--------------------------------------------------------------------------
| 设定im服务器为访问域名,当独立部署时,公网内网同时访问时用
|--------------------------------------------------------------------------
*/
$_IMC["host_from_domain"] = false; 

/*
|--------------------------------------------------------------------------
| 是否支持文件(图片)上传
|--------------------------------------------------------------------------
*/
$_IMC['upload'] = false; 

/*
|--------------------------------------------------------------------------
| 支持显示不在线用户
|--------------------------------------------------------------------------
*/
$_IMC['show_unavailable'] = false; 

/*
|--------------------------------------------------------------------------
| 支持访客聊天(默认好友为站长),开启后通过im登录无效
|--------------------------------------------------------------------------
*/
$_IMC['visitor'] = false; 

/*
|--------------------------------------------------------------------------
| 通知按钮
|--------------------------------------------------------------------------
*/
$_IMC["enable_noti"] = true;

$query = DB::query("SELECT v.* FROM ".DB::table('common_pluginvar')." v, 
	".DB::table('common_plugin')." p 
	WHERE p.identifier='webim' AND v.pluginid = p.pluginid");
while($var = DB::fetch($query)){
	if(isset($var['value'])){
		$_IMC[$var['variable']] = empty( $var['value'] ) ? false : $var['value'];
	}
}


