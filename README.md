一个C/S架构的分布式缓存清除系统

一、开发工具
Server端：perl
Client端：php

二、功能描述
功能结构分为客户端和服务端，客户端用于SA提交需要清除的url或制定内容和目录，服务端监听9999端口(默认，可在程序中更改)，用于接收客户端发送过来的请求。 此系统分为两种清除模式：

普通模式
在普通模式下，SA必须输入完整的url地址，每个地址占一行，client端会把所有url推送给选定的cache组，进行清除，同时返回清除的结果消息。
高级模式
在高级模式下，SA可以任意输入想要清除的内容，比如：swf，jpg，http://www.wenzizone.cn/images/，/upload/， client端会把这些内容通过服务器端的9999端口推送给选定的cache组进行批量清除。此模式下暂时还不支持返回结果，而且目前只能支持squid缓存的批量清除。

三、系统架构图
![alt tag](http://pic.yupoo.com/wenzizone/AJNYTtFT/medium.jpg)

四、平台截图
![alt tag](http://pic.yupoo.com/wenzizone/AJOg2dHB/medium.jpg)
![alt tag](http://pic.yupoo.com/wenzizone/AJOg3e7x/medium.jpg)
![alt tag](http://pic.yupoo.com/wenzizone/AJOg4K09/medium.jpg)

