=== 聚合推送 ===

[![Join the chat at https://gitter.im/teakong/push](https://badges.gitter.im/teakong/push.svg)](https://gitter.im/teakong/push?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Contributors: teakong
Tags: tixing, 微信提醒, 提醒, 通知, 动态提醒, 微信, 语音, 评论通知, 注册通知, 评论提醒, 注册提醒, 钉钉提醒, 飞书提醒
Requires at least: 4.0
Tested up to: 5.2
Stable tag: 4.1
License: GPLv2 or later

一封传话提醒，将博客动态发送提醒通知到您的手机上（微信，钉钉、飞书）。

== Description ==

需配合微信公众号【一封传话】（以下简称“一封传话”）使用。一封传话是一个聚合推送应用，详见一封传话官方网站 https://www.phprm.com/push/h5/
使用本插件前，请您先在微信内搜索并关注公众号：【一封传话】，注册后创建一个提醒，将提醒的通道码填写到插件内，即可在博客动态推送到手机上接收。

本插件将通过Wordpress的钩子（hook）技术截获您博客的动态信息，并将相关信息通过聚合推送API接口 https://www.phprm.com.com/services/push/trigger (去掉多余的.com)提交，再由公众号、钉钉、飞书将提醒推送到您手机。
目前支持以下事件的提醒推送并将向上述API提交以下内容：
1.有新用户注册时推送提醒，提交内容：新用户注册的邮箱；
2.有新的评论提交时推送提醒，提交内容：文章id和评论正文；
本插件不会保存或上传您任何隐私资料，上述功能也可在插件设置界面中开关。

您必须知晓并同意上述服务与功能才能使用本插件。

== Installation ==

上传插件目录到您的博客的 wp-content\plugins\ 目录下，使用管理员登录博客并在博客后台激活本插件，即可使用。

== Changelog ==

= 2.0.0 =
*发布日期2022年12月23日

* 新增有新用户注册时发送提醒通知；
* 新增有新的评论提交时发送提醒通知。