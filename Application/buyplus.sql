create database buyplus charset=utf8;

use buyplus;
-- 会员表
create table ye_member
(
	member_id int unsigned auto_increment,
	email varchar(255),
	telephone varchar(16),
	password varchar(64) not null default '',
	name varchar(64) not null default '',
	is_newsletter tinyint not null default 0, -- 不订阅
	primary key (member_id),
	unique key (email),
	unique key (telephone),
	unique key (name)
) charset=utf8;
-- 促销活动表
create table ye_event
(
	event_id int unsigned auto_increment,
	title varchar(64) not null default '',
	primary key (event_id)
) charset=utf8;
-- 活动会员关联表
create table ye_event_member
(
	event_member_id int unsigned auto_increment,
	event_id int unsigned not null default 0,
	member_id int unsigned not null default 0,
	primary key (event_member_id)
) charset=utf8;
insert into ye_event values (101, '2016双11');
insert into ye_event values (100, '2016国庆大促');
insert into ye_event_member values (null, 101, 14);
insert into ye_event_member values (null, 100, 14);
insert into ye_event_member values (null, 101, 17);

-- 会员登陆行为日志
drop table if exists ye_member_login_log;
create table ye_member_login_log(
	member_login_log_id int unsigned auto_increment,
	member_id int unsigned not null default 0,
	login_time int not null default 0, -- 登陆时间
	login_ip int unsigned not null default 0, -- 登陆IP
	error_number int unsigned not null default 0, -- 错误次数
	primary key (member_login_log_id),
	index (member_id)
) charset=utf8;
insert into ye_member_login_log values (null, 11, unix_timestamp()-50000, inet_aton('22.45.163.11'), 0);
insert into ye_member_login_log values (null, 11, unix_timestamp()-10000, inet_aton('22.45.165.11'), 0);
insert into ye_member_login_log values (null, 11, unix_timestamp(), inet_aton('22.45.163.12'), 0);


create table member_regisger_log();-- 注册行为日志


CREATE TABLE ye_session 
(
	session_id varchar(255) NOT NULL,
	session_expire int(11) NOT NULL,
	session_data blob,
	UNIQUE KEY `session_id` (`session_id`)
) charset=utf8;



-- 商品分类表
drop table if exists ye_category;
create table ye_category (
	category_id int unsigned auto_increment,
	title varchar(32) not null default '',
	parent_id int unsigned not null default 0,
	sort_number int not null default 0,


	image varchar(255) not null default '', -- 分类图片
	image_thumb varchar(255) not null default '', -- 分类图片缩略图
	-- 前台展示
	is_used boolean not null default 1, -- tinyint(1)
	is_nav tinyint not null default 1, -- 针对顶级分类

	-- SEO优化
	meta_title varchar(255) not null default '',
	meta_keywords varchar(255) not null default '',
	meta_description varchar(1024) not null default '',
	primary key (category_id),
	index (parent_id),
	index (sort_number)
) charset=utf8;

insert into ye_category values (1, '未分类', 0, -1, '', '', 0, 0, '', '', '');
insert into ye_category values (5, '眼镜', 0, 0, '', '', 1, 1, '', '', '');
insert into ye_category values (6, '男士眼镜', 5, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (7, '女士眼镜', 5, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (8, '飞行员眼镜', 5, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (9, '驾驶镜', 5, 0,'', '',  1, 0, '', '', '');
insert into ye_category values (10, '太阳镜', 5, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (11, '图书', 0, 0, '', '', 1, 1, '', '', '');
insert into ye_category values (12, '历史', 11, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (14, '科技', 11, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (15, '计算机', 11, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (16, '电子书', 11, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (17, '科普', 14, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (18, '建筑', 14, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (19, '工业技术', 14, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (20, '电子通信', 14, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (21, '自然科学', 14, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (22, '互联网', 15, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (23, '计算机编程', 15, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (24, '硬件，攒机', 15, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (25, '大数据', 15, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (26, '移动开发', 15, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (27, 'PHP', 15, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (28, '近代史', 12, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (29, '当代史', 12, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (30, '古代史', 12, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (31, '先秦百家', 12, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (32, '三皇五帝', 12, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (33, '励志', 16, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (34, '小说', 16, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (35, '成功学', 16, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (36, '经济金融', 16, 0, '', '', 1, 0, '', '', '');
insert into ye_category values (37, '免费', 16, 0, '', '', 1, 0, '', '', '');

-- 品牌表
create table ye_brand
(
	brand_id int unsigned auto_increment,
	title varchar(32) not null default '', -- 品牌名
	logo varchar(255) not null default '', -- 品牌logo
	logo_ori varchar(255) not null default '', -- 品牌logo原始图像文件
	sort_number int not null default 0, -- 排序

	created_at int not null default 0, -- 创建时间
	updated_at int not null default 0, -- 修改时间
	primary key (brand_id),
	index (sort_number),
	index (title)
) charset=utf8;

-- 配置项类型（不提供管理接口）
create table ye_setting_type (
	setting_type_id int unsigned auto_increment,
	type_title varchar(32) not null default '', -- 类型说明
	primary key (setting_type_id)
) charset=utf8;
-- 加入测试数据
insert into ye_setting_type values (1, 'text');-- 文本
insert into ye_setting_type values (2, 'textarea');-- 大文本
insert into ye_setting_type values (3, 'select');-- 单选
insert into ye_setting_type values (4, 'select-multi');-- 多选

-- 配置项分组（不提供管理接口）
create table ye_setting_group (
	setting_group_id int unsigned auto_increment,
	group_title varchar(32) not null default '',-- 分组的标题
	primary key (setting_group_id)
) charset=utf8;
-- 加入测试数据
insert into ye_setting_group values (1, '商店设置');-- ['goods_count']
insert into ye_setting_group values (2, '安全配置');-- [goods_count']

-- 配置项
create table ye_setting (
	setting_id int unsigned not null auto_increment,
	`key` varchar(32) not null default '', -- 程序使用的key
	value varchar(255) not null default '', -- 配置项的值
	title varchar(32) not null default '', -- 配置项的标题描述
	setting_type_id int unsigned not null default 0, -- 配置项输入类型ID
	setting_group_id int unsigned not null default 0, -- 配置项分组的ID
	sort_number int not null default 0, -- 排序标识
	primary key (setting_id),
	index (setting_type_id),
	index (setting_group_id),
	index (sort_number)
) charset=utf8;
-- 测试数据
insert into ye_setting values (1, 'shop_title', 'BuyPlus(败家Shopping)', '商店名称', 1, 1,  0);
insert into ye_setting values (2, 'allow_comment', '5', '是否允许商品评论', 3, 1, 0);
insert into ye_setting values (3, 'use_captcha', '1,3', '哪些页面使用验证码', 4, 2, 0);
insert into ye_setting values (4, 'mate_description', 'BuyPlus(败家Shopping), 用BuyPlus，不败家！', 'mate描述description', 2, 1, 0);
-- 配置系统选项预设值
create table ye_setting_option (
	setting_option_id int unsigned auto_increment,-- 选项预设值的option value="option_id"
	option_title varchar(32) not null default '', -- 选项预设值显示内容<option>option_title</option>
	setting_id int unsigned not null default 0,-- 对应的选项ID, 如果选项为单向或多选类型, 则存在对于的选项预设值列表
	primary key (setting_option_id),
	index (setting_id)
) charset=utf8;
insert into ye_setting_option values (1, '注册', 3);
insert into ye_setting_option values (2, '登录', 3);
insert into ye_setting_option values (3, '评论', 3);
insert into ye_setting_option values (4, '生成订单', 3);
insert into ye_setting_option values (5, '是', 2);
insert into ye_setting_option values (6, '否', 2);