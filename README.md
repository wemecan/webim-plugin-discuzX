NexTalk For DiscuzX
=================================

为DiscuzX社区开发的网页版即时聊天工具，用户通过聊天工具栏可随即与在线好友聊天。


需求
-----------------------------

*       MySQL版本不低于4.1.2
*       需要PHP版本不低于4.3
*       PHP访问外部网络，WebIM连接时需要访问WebIM服务器, 请确保您的php环境是否可连接外部网络, 设置php.ini中`allow_url_fopen=ON`.


升级
---------------------------------

1.	覆盖安装目录webim/内容到source/plugin/nextalk/
2.	到DiscuzX后台管理界面更新nextalk插件


安装
---------------------------------

1.	解压安装包到DiscuzX插件目录source/plugin/
2.	登录DiscuzX管理后台安装新插件nextalk
3.	在配置中设置在nextalk.im(原webim20.cn)注册的域名和apikey
4.	启用插件
5.	给予上传文件目录可写权限: chmod -R 777 static/images/files/




