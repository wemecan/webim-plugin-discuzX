//custom
(function(webim){
	var path = _IMC.path;
	webim.extend(webim.setting.defaults.data, _IMC.setting );

	webim.route( {
		online: path + "im.php?webim_action=online",
		offline: path + "im.php?webim_action=offline",
		deactivate: path + "im.php?webim_action=refresh",
		message: path + "im.php?webim_action=message",
		presence: path + "im.php?webim_action=presence",
		status: path + "im.php?webim_action=status",
		setting: path + "im.php?webim_action=setting",
		history: path + "im.php?webim_action=history",
		clear: path + "im.php?webim_action=clear_history",
		download: path + "im.php?webim_action=download_history",
		members: path + "im.php?webim_action=members",
		join: path + "im.php?webim_action=join",
		leave: path + "im.php?webim_action=leave",
		buddies: path + "im.php?webim_action=buddies",
		upload: path + "static/images/upload.php",
		notifications: path + "im.php?webim_action=notifications"
	} );

	webim.ui.emot.init({"dir": path + "static/images/emot/default"});
	var soundUrls = {
		lib: path + "static/assets/sound.swf",
		msg: path + "static/assets/sound/msg.mp3"
	};
	var ui = new webim.ui(document.body, {
		imOptions: {
			jsonp: _IMC.jsonp
		},
		soundUrls: soundUrls,
		buddyChatOptions: {
			upload: _IMC.upload
		},
		roomChatOptions: {
			upload: _IMC.upload
		}
	}), im = ui.im;

	if( _IMC.user ) im.setUser( _IMC.user );
	if( _IMC.menu ) ui.addApp("menu", { "data": _IMC.menu } );
	if( _IMC.enable_shortcut ) ui.layout.addShortcut( _IMC.menu );

	ui.addApp("buddy", {
		showUnavailable: _IMC.showUnavailable,
		is_login: _IMC['is_login'],
		disable_login: true,
		loginOptions: _IMC['login_options']
	} );
	if( !_IMC.disable_room )ui.addApp("room", { discussion: true });
	if( !_IMC.disable_noti )ui.addApp("notification");
	ui.addApp("setting", {"data": webim.setting.defaults.data});
	if( !_IMC.disable_chatlink )ui.addApp("chatlink", {
		space_href: [/mod=space&uid=(\d+)/i, /space\-uid\-(\d+)\.html$/i],
		space_class: /xl\sxl2\scl/,
		space_id: null,
		link_wrap: document.getElementById("ct")
	});
	ui.render();
	_IMC['is_login'] && im.autoOnline() && im.online();
})(webim);
