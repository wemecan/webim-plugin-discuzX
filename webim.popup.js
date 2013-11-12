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
	var ui = new webim.ui(document.getElementById("webim_content"), {
		imOptions: {
			jsonp: _IMC.jsonp
		},
		soundUrls: soundUrls,
		layout: "layout.popup",
		layoutOptions: {
			unscalable: true
		},
		buddyChatOptions: {
			simple: true,
			upload: _IMC.upload
		},
		roomChatOptions: {
			simple: true,
			upload: _IMC.upload
		}
	}), im = ui.im;

	if( _IMC.user ) im.setUser( _IMC.user );

	ui.addApp("buddy", {
		is_login: _IMC['is_login'],
		//	loginOptions: _IMC['login_options']
		userOptions: {show: true},
		showUnavailable: _IMC.showUnavailable,
		disable_group: false
	} );

	if( !_IMC.disable_room )ui.addApp("room", { discussion: false});
	ui.addApp("setting", {"data": {
		play_sound: webim.setting.defaults.data.play_sound
	}});

	ui.render();
	_IMC['is_login'] && im.autoOnline() && im.online();
})(webim);
