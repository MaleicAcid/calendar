-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: 139.196.225.214
-- 生成日期: 2017 �?01 �?19 �?00:57
-- 服务器版本: 5.7.15
-- PHP 版本: 5.5.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `rili`
--

DELIMITER $$
--
-- 存储过程
--
CREATE DEFINER=`dataread`@`%` PROCEDURE `sp_get_level`(stdate VARCHAR(20),eddate VARCHAR(20),vartype VARCHAR(20))
BEGIN	
	SET @activitydate:=NULL;
	SET @level:=NULL;
	SET @rownum:=1;
	SELECT
	a.*
	FROM cal_activity a
	INNER JOIN(
		SELECT
		*
		FROM(
			SELECT
			a.*,
			(CASE WHEN a.activitydate=@activitydate  THEN @rownum:=@rownum+1
			     ELSE @rownum:=1 END) AS rank,
			     @activitydate:=a.activitydate
			FROM(
				SELECT
				activitydate ,`level`,id,`type`
				FROM cal_activity
				WHERE activitydate >=stdate AND activitydate <=eddate
				AND `type`= CASE WHEN vartype='' THEN `type` ELSE vartype END
				GROUP BY activitydate ,`level`,id,`type`
				ORDER BY activitydate DESC ,`level` DESC 
			)a
		)temp
		WHERE rank <=3	
	)temp ON a.activitydate=temp.activitydate AND a.level=temp.level AND a.id=temp.id AND a.type=temp.type;
    END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `cal_activity`
--

CREATE TABLE IF NOT EXISTS `cal_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `startdate` varchar(20) NOT NULL COMMENT '开始时间',
  `enddate` varchar(20) NOT NULL COMMENT '结束时间',
  `level` int(3) NOT NULL COMMENT '当天等级',
  `color` varchar(7) NOT NULL COMMENT '活动颜色',
  `unit` varchar(50) NOT NULL COMMENT '举办方',
  `address` varchar(50) NOT NULL COMMENT '地点',
  `people` varchar(10) DEFAULT NULL COMMENT '联系人',
  `telephone` varchar(11) DEFAULT NULL COMMENT '联系人手机',
  `type` varchar(50) NOT NULL COMMENT '活动类型',
  `activitydate` varchar(20) NOT NULL COMMENT '活动日期',
  `addtime` varchar(20) NOT NULL DEFAULT '' COMMENT '添加事件时间 时间戳形式',
  `readcount` int(11) DEFAULT '0',
  `commentcount` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=35 ;

--
-- 转存表中的数据 `cal_activity`
--

INSERT INTO `cal_activity` (`id`, `title`, `content`, `startdate`, `enddate`, `level`, `color`, `unit`, `address`, `people`, `telephone`, `type`, `activitydate`, `addtime`, `readcount`, `commentcount`) VALUES
(1, '大甩卖', '9折大甩卖', '2017-01-03 10:00', '2017-01-03 10:00', 1, '#666699', '上海海事大学', '上海', '愣子', '13111111111', '文艺', '2017-01-03', '', 780, 14),
(2, '大甩卖2', '大甩卖', '2017-01-03 00:00', '2017-01-03 00:00', 3, '#FF6EB4', '上海海事大学', '北京', '1', '1', '军事', '2017-01-03', '', 163, 9),
(3, '3', '大甩卖1', '2017-2-27 11:00', '2016-12-27 12:00', 2, '#FF6EB4', '上海海事大学', '深圳', '2', '2', '杂耍', '2016-12-27', '', 47, 8),
(4, '1', '大甩卖2', '2017-2-25 11:00', '2016-12-25 13:00', 1, '#666699', '上海海事大学', '深圳', '1', '1', '军事', '2016-12-25', '', 20, 0),
(5, '1', '大甩卖3', '2016-12-20 21:00', '2016-12-20 21:00', 1, '#666699', '上海海事大学', '深圳', '1', '1', '军事', '2016-12-20', '', 23, 0),
(6, '1', '大甩卖4', '2016-12-20 21:00', '2016-12-20 21:00', 1, '#666699', '上海海事大学', '深圳', '1', '1', '军事', '2016-12-20', '', 3, 0),
(12, '2222', '1', '2016-12-19 22:12', '2016-12-19 22:12', 5, '#666699', '11', '1', '1', '1', '招聘', '2016-12-19', '1481724758', 2, 0),
(13, 'test', 'test111', '2016-12-19 22:55', '2016-12-19 22:55', 5, '#bcfb84', 'tets', 'test', 'test', 'test', '1111', '2016-12-19', '1481727336', 1, 0),
(14, 'asdasda', 'asda', '2016-12-18 23:21', '2016-12-18 23:21', 1, '#666699', 'asda', 'a', 'sda', 'sdsasd', '军事', '2016-12-18', '1481815293', 18, 2),
(15, '游戏', 'asdasd', '2016-12-19 21:28', '2016-12-19 22:28', 4, '#666699', 'asdasd', 'qadasda', 'asdad', 'asdasd', '游戏', '2016-12-19', '1481894928', 3, 0),
(16, '直播', 'dasda', '2016-12-18 12:29', '2016-12-18 22:29', 4, '#bcfb84', 'asdasd', 'asdas', 'asda', 'asda', '游戏', '2016-12-18', '1481894960', 30, 0),
(17, 'aaaaaaaa', '111', '2016-12-18 12:27', '2016-12-18 13:27', 1, '#666699', 'qqqqq', '1111', '1111', '1111', '军事', '2016-12-18', '1482035256', 9, 0),
(18, '好', '11111', '2016-12-18 12:29', '2016-12-18 12:29', 1, '#666699', '1111', '111111', '111111', '111', '军事', '2016-12-18', '1482035367', 5, 0),
(19, 'asdasdad', 'asd', '2016-12-19 21:32', '2016-12-19 22:32', 1, '#666699', 'asda', 'asd', 'a', 'ds', '军事', '2016-12-19', '1482154376', 1, 0),
(20, 'asda', 'asd', '2016-12-20 21:18', '2016-12-20 21:18', 1, '#666699', 'sdasd', 'a', 'asd', 'asd', '军事', '2016-12-20', '1482239934', 0, 0),
(21, '期房vs现房？买期房的6大风险你必须知道', 'asdasd', '2016-12-04 00:00', '2016-12-04 00:00', 1, '#666699', 'asdasda', 'asdasd', 'asdasd', 'asdasd', '军事', '2016-12-02', '1482239934', 84, 13),
(22, '期房vs现房？买期房的6大风险你必须知道', 'asdasd', '2017-01-03 00:00', '2017-01-03 00:00', 1, '#666699', 'asdasda', 'asdasd', 'asdasd', 'asdasd', '军事', '2016-11-04', '1482760578', 2, 0),
(23, '测试活动', 'sahkshfdkahd', '2017-01-01 00:00', '2017-01-01 01:00', 1, '#778899', 'asjhdlkash', 'akjshdkhasd', 'akjsfhkah', 'akhsdkha', '军事', '2017-01-01', '1483103117', 17, 0),
(24, '活动测试2', 'asdas', '2017-01-02 16:00', '2017-01-02 18:00', 1, '#778899', 'asda', 'asda', 'asda', 'asdas', '军事', '2017-01-02', '1483104457', 3, 0),
(25, 'test3', 'asd', '2017-02-04 08:00', '2017-02-04 10:00', 1, '#778899', 'asd', 'asda', 'asda', 'asd', '军事', '2017-02-04', '1483104830', 6, 0),
(26, 'test4', 'a来咯啦啊', '2017-01-03 00:00', '2017-01-03 01:00', 1, '#778899', 'a', 'a', 'a', 'a', '军事', '2017-01-03', '1483111535', 3, 0),
(27, 'test5', 'asdlkjalsjdlajsd', '2017-01-02 09:51', '2017-01-02 10:51', 1, '#80ff00', '小说协会', '111', '小说管理员', '131xxxxxxx', '小说', '2017-01-02', '1483357948', 8, 0),
(28, 'test6', 'a', '2017-01-02 08:53', '2017-01-02 19:53', 2, '#ff0000', 'aaa', 'aaa', 'aaa', 'aa', '游戏', '2017-01-02', '1483358056', 5, 0),
(29, 'test7', '1111111', '2017-01-03 00:00', '2017-01-03 00:00', 5, '#80ff00', '1111', '111111', '111111', '1111111', '综艺', '2017-01-03', '1483358098', 36, 1),
(30, '开会', '1234534水电费', '2017-01-03 09:41', '2017-01-03 13:41', 1, '#778899', '就业中心', '启航', '王浩', '12312313', '军事', '2017-01-03', '1483404112', 3, 0),
(31, 'asdad', 'asd', '2017-01-03 21:40', '2017-01-03 21:40', 1, '#778899', 'asdad', 'asd', 'asd', 'awsd', '军事', '2017-01-03', '1483450823', 0, 0),
(32, '11111·', 'a', '2017-01-03 21:41', '2017-01-03 21:41', 1, '#778899', 'aa', 'a', '', '', '军事', '2017-01-03', '1483450910', 2, 0),
(34, 'testtest', '1111111111111111 &amp;nbsp;&lt;u&gt;&lt;span style=&quot;font-size:48px;&quot;&gt;&lt;a href=&quot;http://www.baidu.com&quot; target=&quot;_blank&quot;&gt;zkjhnlkahsd&lt;/a&gt;&lt;/span&gt;&lt;/u&gt;', '2017-01-04 20:50', '2017-01-04 20:50', 3, '#778899', 'asdas', 'asd', '', '', '军事', '2017-01-04', '1483534247', 60, 0);

-- --------------------------------------------------------

--
-- 表的结构 `cal_activitytype`
--

CREATE TABLE IF NOT EXISTS `cal_activitytype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `createdate` varchar(20) NOT NULL,
  `creator` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=12 ;

--
-- 转存表中的数据 `cal_activitytype`
--

INSERT INTO `cal_activitytype` (`id`, `type`, `createdate`, `creator`) VALUES
(1, '军事', '2016-12-12 ', 'cch'),
(3, '文艺', '2016-12-12 22:36:36', NULL),
(4, '小说', '2016-12-12 22:36:55', NULL),
(5, '武侠', '2016-12-12 22:37:02', NULL),
(6, '游戏', '2016-12-12 22:37:30', NULL),
(7, '招聘', '2016-12-12 22:37:38', NULL),
(8, '娱乐', '2016-12-12 22:37:59', NULL),
(9, '1111', '2016-12-12 23:34:10', NULL),
(10, '综艺', '2016-12-15 21:26:57', 'test'),
(11, 'jjj', '2016-12-26 22:47:48', 'test');

-- --------------------------------------------------------

--
-- 表的结构 `cal_admin`
--

CREATE TABLE IF NOT EXISTS `cal_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(50) NOT NULL COMMENT '密码',
  `realname` varchar(20) DEFAULT NULL COMMENT '真实姓名',
  `type` int(1) NOT NULL COMMENT '0是主管理员 1是子管理员',
  `creator` varchar(20) DEFAULT NULL COMMENT '创建人',
  `createdate` varchar(20) DEFAULT NULL COMMENT '创建时间',
  `upadatedate` varchar(20) DEFAULT NULL COMMENT '修改时间',
  `updator` varchar(20) DEFAULT NULL COMMENT '修改人',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0未删除 1已经删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=21 ;

--
-- 转存表中的数据 `cal_admin`
--

INSERT INTO `cal_admin` (`id`, `username`, `password`, `realname`, `type`, `creator`, `createdate`, `upadatedate`, `updator`, `status`) VALUES
(1, 'admin', '1', '管理员', 0, 'system', '2016-12-07', NULL, NULL, 0),
(2, 'cch', '1', '陈诚豪', 0, 'system', '2016-12-07', NULL, NULL, 0),
(6, '77', '123456', '777', 1, 'system', '2016-12-07', NULL, NULL, 0),
(17, 'test', '96e79218965eb72c92a549dd5a330112', '测试1', 0, 'system', '2016-12-10 12:57:01', NULL, NULL, 0),
(18, 'test11', '111111', '测试', 1, 'system', '2016-12-10 14:19:09', NULL, NULL, 0),
(19, 'mahongyuan', '111111', '马宏元', 0, 'system', '2016-12-28 23:43:34', NULL, NULL, 0),
(20, 'blin', 'e10adc3949ba59abbe56e057f20f883e', '宋晓梅', 1, 'system', '2016-12-31 08:35:50', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- 表的结构 `cal_color`
--

CREATE TABLE IF NOT EXISTS `cal_color` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `color` varchar(10) NOT NULL,
  `createdate` varchar(20) NOT NULL,
  `creator` varchar(50) DEFAULT NULL,
  `colorname` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=9 ;

--
-- 转存表中的数据 `cal_color`
--

INSERT INTO `cal_color` (`id`, `color`, `createdate`, `creator`, `colorname`) VALUES
(3, '#778899', '2016-12-12 23:00:29', 'system', '灰色'),
(5, '#004080', '2016-12-13 22:33:47', 'system', '深蓝'),
(6, '#bcfb84', '2016-12-13 22:34:39', 'system', '浅绿'),
(7, '#ff0000', '2016-12-26 22:47:37', 'system', '红色'),
(8, '#80ff00', '2016-12-29 21:47:55', 'system', '绿色');

-- --------------------------------------------------------

--
-- 表的结构 `cal_comment`
--

CREATE TABLE IF NOT EXISTS `cal_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键 评论id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `activity_id` int(11) NOT NULL COMMENT '活动id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级 评论id 默认0',
  `comment` varchar(2000) NOT NULL COMMENT '评论',
  `createdate` varchar(20) NOT NULL COMMENT '创建时间',
  `ip_address` varchar(15) DEFAULT NULL,
  `addtime` varchar(20) NOT NULL DEFAULT '' COMMENT '添加事件 时间戳形式',
  `username` varchar(64) DEFAULT NULL COMMENT '发表评论用户名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=108 ;

--
-- 转存表中的数据 `cal_comment`
--

INSERT INTO `cal_comment` (`id`, `user_id`, `activity_id`, `pid`, `comment`, `createdate`, `ip_address`, `addtime`, `username`) VALUES
(1, 29, 1, 0, '你好', '2016-12-08 10:28', '0.0.0.0', '', 'test1'),
(3, 29, 1, 0, '你也好啊', '2016-12-18 02:50:04', '0.0.0.0', '', 'test'),
(5, 29, 4, 0, '这是个好活动——此条有回复', '2016-12-18 02:53:10', '0.0.0.0', '', 'test'),
(6, 0, 0, 5, '这是作者回复', '2016-12-18 02:55:55', '0.0.0.0', '', 'test'),
(7, 29, 4, 0, '这是个好活动——此条有回复', '2016-12-18 02:53:10', '0.0.0.0', '', 'test'),
(8, 29, 4, 0, '这是个好活动——此条有回复', '2016-12-18 02:53:10', '0.0.0.0', '', 'test'),
(9, 29, 4, 0, '这是个好活动——此条有回复', '2016-12-18 02:53:10', '0.0.0.0', '', 'test'),
(10, 29, 4, 0, '这是个好活动——此条有回复', '2016-12-18 02:53:10', '0.0.0.0', '', 'test'),
(11, 29, 4, 0, '这是个好活动——此条有回复', '2016-12-18 02:53:10', '0.0.0.0', '', 'test'),
(12, 29, 4, 0, '这是个好活动——此条有回复', '2016-12-18 02:53:10', '0.0.0.0', '', 'test'),
(13, 29, 4, 0, '这是个好活动——此条有回复', '2016-12-18 02:53:10', '0.0.0.0', '', 'test'),
(14, 29, 4, 0, '这是个好活动——此条有回复', '2016-12-18 02:53:10', '0.0.0.0', '', 'test'),
(16, 0, 0, 6, '这是作者回复', '2016-12-18 02:55:55', '0.0.0.0', '', 'test'),
(17, 0, 0, 7, '这是作者回复', '2016-12-18 02:55:55', '0.0.0.0', '', 'test'),
(18, 0, 0, 8, '这是作者回复', '2016-12-18 02:55:55', '0.0.0.0', '', 'test'),
(20, 0, 0, 10, '这是作者回复', '2016-12-18 02:55:55', '0.0.0.0', '', 'test'),
(21, 0, 0, 11, '这是作者回复', '2016-12-18 02:55:55', '0.0.0.0', '', 'test'),
(22, 0, 0, 12, '这是作者回复', '2016-12-18 02:55:55', '0.0.0.0', '', 'test'),
(61, 29, 4, 0, '这是个好活动——此条有回复', '2016-12-18 02:53:10', '0.0.0.0', '', 'test'),
(62, 33, 1, 0, 'message test', '2016-12-26 10:08:44', '0.0.0.0', '', '459105408@qq.com'),
(63, 33, 1, 0, 'test massage', '2016-12-26 10:22:29', '0.0.0.0', '', '459105408@qq.com'),
(64, 33, 1, 0, 'hahahhahhha', '2016-12-26 10:25:50', '0.0.0.0', '', '459105408@qq.com'),
(65, 33, 1, 0, 'gagag', '2016-12-26 10:36:39', '0.0.0.0', '', '459105408@qq.com'),
(66, 36, 1, 0, 'eww', '2016-12-27 07:35:20', '0.0.0.0', '', '118@qq.com'),
(67, 37, 2, 0, '1', '2016-12-27 07:47:01', '0.0.0.0', '', '11@qq.com'),
(68, 33, 1, 0, 'sdsdsdds', '2016-12-27 09:32:33', '0.0.0.0', '', '459105408@qq.com'),
(69, 35, 21, 0, '', '2016-12-27 10:25:40', '0.0.0.0', '', '315412678@qq.com'),
(70, 36, 1, 0, '121', '2016-12-28 02:34:25', '0.0.0.0', '', '118@qq.com'),
(71, 33, 14, 0, '111', '2016-12-28 10:01:52', '0.0.0.0', '', '459105408@qq.com'),
(72, 33, 14, 0, '123456', '2016-12-28 10:02:02', '0.0.0.0', '', '459105408@qq.com'),
(73, 33, 21, 0, '1', '2016-12-28 10:04:44', '0.0.0.0', '', '459105408@qq.com'),
(74, 33, 2, 0, 'message line', '2016-12-28 10:14:02', '0.0.0.0', '', '459105408@qq.com'),
(75, 33, 2, 0, '你好1111', '2016-12-28 10:11:20', '0.0.0.0', '', '459105408@qq.com'),
(76, 33, 2, 0, '1', '2016-12-28 10:13:15', '0.0.0.0', '', '459105408@qq.com'),
(77, 33, 2, 0, '1', '2016-12-28 10:16:00', '0.0.0.0', '', '459105408@qq.com'),
(78, 40, 1, 0, '哈哈哈啦啦啦', '2016-12-29 12:04:31', '1.204.251.121', '', '15216627701@163.com'),
(79, 40, 1, 0, '啦啦', '2016-12-29 12:04:52', '1.204.251.121', '', '15216627701@163.com'),
(80, 40, 1, 0, '啦啦', '2016-12-29 12:06:47', '1.204.251.121', '', '15216627701@163.com'),
(81, 40, 1, 0, '哈哈哈啦啦啦', '2016-12-29 12:06:51', '1.204.251.121', '', '15216627701@163.com'),
(82, 41, 3, 0, 'test', '2016-12-29 12:07:04', '124.76.53.23', '', '1303881640@qq.com'),
(83, 41, 3, 0, '', '2016-12-29 12:10:39', '124.76.53.23', '', '1303881640@qq.com'),
(84, 41, 3, 0, '', '2016-12-29 12:10:42', '124.76.53.23', '', '1303881640@qq.com'),
(85, 38, 21, 0, '', '2016-12-29 02:01:01', '0.0.0.0', '', '1185021095@qq.com'),
(86, 38, 3, 0, '', '2016-12-29 02:12:14', '0.0.0.0', '', '1185021095@qq.com'),
(87, 38, 3, 0, '1', '2016-12-29 02:27:08', '0.0.0.0', '', '1185021095@qq.com'),
(88, 38, 3, 0, '111', '2016-12-29 02:28:07', '0.0.0.0', '', '1185021095@qq.com'),
(89, 38, 1, 0, '1', '2016-12-29 02:29:23', '0.0.0.0', '', '1185021095@qq.com'),
(90, 38, 21, 0, '1', '2016-12-29 02:40:28', '0.0.0.0', '', '1185021095@qq.com'),
(91, 38, 21, 0, '', '2016-12-29 02:40:35', '0.0.0.0', '', '1185021095@qq.com'),
(92, 38, 21, 0, '2', '2016-12-29 02:45:14', '0.0.0.0', '', '1185021095@qq.com'),
(93, 33, 21, 0, '11111111', '2016-12-29 10:02:59', '0.0.0.0', '', '459105408@qq.com'),
(94, 33, 21, 0, '11', '2016-12-29 10:05:28', '0.0.0.0', '', '459105408@qq.com'),
(95, 33, 21, 0, '121', '2016-12-29 10:08:42', '0.0.0.0', '', '459105408@qq.com'),
(96, 33, 21, 0, '111', '2016-12-29 10:09:19', '0.0.0.0', '', '459105408@qq.com'),
(97, 38, 2, 0, '', '2016-12-29 10:25:27', '180.172.174.122', '', '1185021095@qq.com'),
(98, 41, 2, 0, '          ', '2016-12-30 11:24:27', '117.136.8.67', '', '1303881640@qq.com'),
(99, 41, 2, 0, '11555854855', '2016-12-30 11:24:45', '117.136.8.67', '', '1303881640@qq.com'),
(100, 41, 2, 0, 'wqjjahshw', '2016-12-30 11:25:01', '117.136.8.67', '', '1303881640@qq.com'),
(101, 37, 21, 0, '11121', '2016-12-30 10:04:36', '0.0.0.0', '', '11@qq.com'),
(102, 29, 3, 0, '不错不错不错不错不错不错不错不错', '2016-12-31 04:18:44', '127.0.0.1', '', 'CS_MaleicAcid@163.com'),
(103, 33, 3, 0, 'aaaaaaaaaaaaaaaaa', '2016-12-31 19:58:06', '0.0.0.0', '', '459105408@qq.com'),
(104, 35, 21, 0, '阿斯蒂芬收拾收拾', '2016-12-31 22:32:34', '0.0.0.0', '', '315412678@qq.com'),
(105, 37, 21, 0, '11111111111111362111222345555666666777777777777777773211556677783222222', '2017-01-02 11:12:18', '180.172.174.122', '', '11@qq.com'),
(106, 46, 29, 0, '好单位吗？', '2017-01-03 08:34:49', '116.3.1.237', '', 'houjunkai@163.com'),
(107, 35, 1, 0, '13761953130', '2017-01-03 05:23:32', '183.192.24.84', '', '315412678@qq.com');

-- --------------------------------------------------------

--
-- 表的结构 `cal_user`
--

CREATE TABLE IF NOT EXISTS `cal_user` (
  `username` varchar(64) NOT NULL COMMENT '用户名',
  `user_mail` varchar(64) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL COMMENT '密码',
  `user_relname` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户姓名',
  `user_class` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户班级',
  `age` int(11) DEFAULT NULL COMMENT '年龄',
  `gender` int(1) DEFAULT NULL COMMENT '性别 0是男 1是女',
  `mail_check` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '邮箱是否验证 0未验证1已验证',
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `status` int(1) NOT NULL COMMENT '账户是否有效 0无效 1有效',
  `is_comment` int(1) NOT NULL COMMENT '评论权限 0不能评论 1可以评论',
  `createdate` varchar(20) NOT NULL COMMENT '创建时间',
  `updatedate` varchar(20) DEFAULT NULL COMMENT '修改时间',
  `mail_check_code` varchar(64) DEFAULT NULL,
  `mail_check_code_addtime` int(11) DEFAULT NULL COMMENT '邮箱验证码更新时间 时间戳形式',
  `addtime` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=48 ;

--
-- 转存表中的数据 `cal_user`
--

INSERT INTO `cal_user` (`username`, `user_mail`, `password`, `user_relname`, `user_class`, `age`, `gender`, `mail_check`, `user_id`, `status`, `is_comment`, `createdate`, `updatedate`, `mail_check_code`, `mail_check_code_addtime`, `addtime`) VALUES
('cch@qq.com', 'cch@qq.com', '18c21024111ed12b6e00e148f8b74c9a', 'cch', '', 11, 0, 1, 3, 0, 1, '2016-12-03 21:37:44', NULL, NULL, NULL, '1481359226'),
('陈志强', '', '202cb962ac59075b964b07152d234b70', '', '', 11, 0, 1, 4, 0, 1, '2016-12-03 21:37:44', NULL, '3d8e654e791a274d251c87e25def08d6', NULL, ''),
('csahongyuan@163.com', '1170083805@qq.com', '216101d81dfff31b956362ac13cde2e3', '', '', NULL, NULL, 0, 15, 0, 1, '2016-12-10 04:40:26', NULL, '327fc9a6236533ac657b9c6478697d44', NULL, '1481359226'),
('CS_Maleic@163.com', '2714983402@qq.com', 'a44addfa57e19ed8da8367215746a49f', '', '', NULL, NULL, 0, 16, 0, 1, '2016-12-10 16:42:26', NULL, '74beb2568825f147be27dc8aff6acb9e', NULL, '1481359346'),
('aaaaq@163.com', 'aaaaq@163.com', 'b39bc9a686605c794d239b03a46fbc45', '', '', NULL, NULL, 0, 18, 0, 1, '2016-12-11 01:29:24', NULL, 'd6c62342cf445c7c8ecf74af3088c565', NULL, '1481390964'),
('CS_1@163.com', 'CS_1@163.com', 'a44addfa57e19ed8da8367215746a49f', '', '', NULL, NULL, 0, 26, 0, 1, '2016-12-22 16:19:50', NULL, '62c81ebcb33a90525a046b9685c62e93', 1482394790, '1482394790'),
('mhy@163.com', 'mhy@163.com', 'a44addfa57e19ed8da8367215746a49f', '', '', NULL, NULL, 0, 28, 0, 1, '2016-12-24 17:10:16', NULL, 'd831d071982f244a7822f8067d1bee1c', 1482570618, '1482570616'),
('CS_MaleicAcid@163.com', 'CS_MaleicAcid@163.com', 'a44addfa57e19ed8da8367215746a49f', '12', '21', NULL, NULL, 1, 29, 0, 1, '2016-12-24 17:14:29', NULL, 'd0c4bec9d1f585c41ca349e8a5cecae6', 1482765512, '1482570869'),
('abc@163.com', 'abc@163.com', 'e837443b116508a3fbfcf8566e026e1e', '', '', NULL, NULL, 0, 30, 0, 1, '2016-12-25 20:06:42', NULL, 'b8b8b2e1190852fb71f0aadf955460c9', 1482667602, '1482667602'),
('117083805@qq.com', '1170083805@qq.com', 'a44addfa57e19ed8da8367215746a49f', '', '', NULL, NULL, 0, 31, 0, 1, '2016-12-25 20:08:11', NULL, '80a37f2dfb136ae64b0549b34befd996', 1482667691, '1482667691'),
('root@163.com', 'root@163.com', '216101d81dfff31b956362ac13cde2e3', '', '', NULL, NULL, 0, 32, 0, 1, '2016-12-25 20:10:02', NULL, 'fd46af1ee8b27d7932d648c13825eeee', 1482667802, '1482667802'),
('459105408@qq.com', '459105408@qq.com', '18c21024111ed12b6e00e148f8b74c9a', '陈', '1', NULL, NULL, 1, 33, 0, 1, '2016-12-25 20:26:39', NULL, 'verified', 1482668799, '1482668799'),
('CSmahongyuan@163.com', 'CSmahongyuan@163.com', 'a44addfa57e19ed8da8367215746a49f', '啊啊', '啊啊', NULL, NULL, 0, 34, 0, 1, '2016-12-26 22:57:05', NULL, '642debf638fdbae7c8eac4fa6da68583', 1482764225, '1482764225'),
('315412678@qq.com', '315412678@qq.com', '8d3843f08e22ae41575a9b3729e1e991', 'Vanklin', 'No.1', NULL, NULL, 0, 35, 0, 1, '2016-12-27 18:26:58', NULL, 'bd498ba5fd2143f6a9c98b8d9145a2f4', 1482834418, '1482834418'),
('118@qq.com', '118@qq.com', 'a1aaf12048aec96778ee50732fddd764', '11', '111', NULL, NULL, 0, 36, 0, 1, '2016-12-27 19:30:50', NULL, '8b5d20711e3dc729a3485250221e2c05', 1482838250, '1482838250'),
('11@qq.com', '11@qq.com', 'a1aaf12048aec96778ee50732fddd764', '1', '1', NULL, NULL, 0, 37, 0, 1, '2016-12-27 19:37:30', NULL, 'c2e1a20c6b4541a6060abf9ff2954110', 1482838650, '1482838650'),
('1185021095@qq.com', '1185021095@qq.com', 'a1aaf12048aec96778ee50732fddd764', 'p', 'p', NULL, NULL, 1, 38, 0, 1, '2016-12-27 20:28:08', NULL, '397c3abe0fe8aa12c436e43c875ded56', 1482989987, '1482841688'),
('1170083805@qq.com', '1170083805@qq.com', 'a44addfa57e19ed8da8367215746a49f', '', '', NULL, NULL, 1, 39, 1, 1, '2016-12-28 22:43:50', NULL, 'verified', 1482936230, '1482936230'),
('15216627701@163.com', '15216627701@163.com', '64a3db81bcfb00b005f519a4d99bdcf1', '', '', NULL, NULL, 0, 40, 1, 1, '2016-12-29 00:02:30', NULL, '2c254a4a3607331f974c499ab79602a6', 1482940950, '1482940950'),
('1303881640@qq.com', '1303881640@qq.com', '7dbee64e54a0f7496815d11280dc4d4a', '233', '11', NULL, NULL, 1, 41, 1, 1, '2016-12-29 00:05:10', NULL, 'verified', 1482941110, '1482941110'),
('111@qq.com', '111@qq.com', '65422e7aa0a517bbc1dd6c5a4b7cc9d4', '', '', NULL, NULL, 0, 42, 1, 1, '2016-12-29 13:25:48', NULL, '4962928a9a7b7be4d185dfb67db8354f', 1482989148, '1482989148'),
('1@qq.com', '1@qq.com', 'f8d513b699fdd0bc6c55bc0f0b094ead', '', '', NULL, NULL, 0, 43, 1, 1, '2016-12-29 13:28:59', NULL, 'c95638b72570d1434cf6c27ad6e1eee1', 1482989339, '1482989339'),
('1111@111.com', '1111@111.com', '65422e7aa0a517bbc1dd6c5a4b7cc9d4', '', '', NULL, NULL, 0, 44, 1, 1, '2016-12-29 22:14:48', NULL, 'd37df4b5858289fe2da04b784308c1db', 1483020888, '1483020888'),
('JHON@ABC.com', 'JHON@ABC.com', 'a44addfa57e19ed8da8367215746a49f', '', '', NULL, NULL, 0, 45, 1, 1, '2016-12-31 14:00:16', NULL, '764366d5a4f71f88101dc2413de09323', 1483164016, '1483164016'),
('houjunkai@163.com', 'houjunkai@163.com', 'b24295610feb8af269b692481312221d', '', '', NULL, NULL, 0, 46, 1, 1, '2017-01-03 08:34:23', NULL, 'ba5318350ae76a1ec2fe5c6d2e0b27d3', 1483403663, '1483403663'),
('1147163483@qq.com', '1147163483@qq.com', '2c4d23c87f272768766e8658ff5855f5', '', '', NULL, NULL, 1, 47, 1, 1, '2017-01-03 21:09:00', NULL, 'verified', 1483448940, '1483448940');

-- --------------------------------------------------------

--
-- 表的结构 `cal_user_activity`
--

CREATE TABLE IF NOT EXISTS `cal_user_activity` (
  `collect_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `activity_id` int(11) NOT NULL COMMENT '活动id',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT '状态 0：不可见 1可见',
  `add_time` varchar(20) NOT NULL DEFAULT '' COMMENT '添加时间 时间戳形式',
  `updatedate` varchar(20) NOT NULL COMMENT '最后修改时间',
  `is_remind` tinyint(3) NOT NULL COMMENT '是否邮件提醒 0不提醒 1发送提醒  2 提醒成功 3提醒失败',
  `remind_time` varchar(20) NOT NULL DEFAULT '' COMMENT '邮件提醒时间 时间戳形式',
  `is_comet_remind` tinyint(3) NOT NULL COMMENT '是否页面提醒 0不提醒 1发送提醒  2 提醒成功 3提醒失败',
  `comet_remind_time` varchar(20) NOT NULL DEFAULT '' COMMENT '页面提醒时间 时间戳形式',
  PRIMARY KEY (`collect_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=16 ;

--
-- 转存表中的数据 `cal_user_activity`
--

INSERT INTO `cal_user_activity` (`collect_id`, `user_id`, `activity_id`, `status`, `add_time`, `updatedate`, `is_remind`, `remind_time`, `is_comet_remind`, `comet_remind_time`) VALUES
(1, 37, 21, 0, '1483195669', '1483195669', 0, '', 0, ''),
(2, 37, 2, 1, '1483378988', '1483378988', 0, '0', 0, '0'),
(3, 37, 3, 0, '1483196852', '1483196852', 0, '0', 0, '0'),
(4, 40, 2, 1, '1483315895', '1483315895', 0, '', 0, ''),
(5, 40, 1, 1, '1483315918', '1483315918', 0, '', 0, ''),
(6, 37, 1, 1, '1483378982', '1483378982', 2, '1483405200', 1, '1483405200'),
(7, 37, 26, 1, '1483378992', '1483378992', 0, '', 0, ''),
(8, 46, 29, 0, '1483403700', '1483403700', 0, '', 0, ''),
(9, 46, 1, 1, '1483403908', '1483403908', 2, '1483405200', 1, '1483405200'),
(10, 46, 2, 1, '1483404315', '1483404315', 0, '', 0, ''),
(11, 37, 30, 1, '1483416967', '1483416967', 0, '', 0, ''),
(12, 37, 25, 1, '1483422856', '1483422856', 0, '', 0, ''),
(13, 29, 1, 1, '1483430528', '1483430528', 0, '', 0, ''),
(14, 35, 1, 0, '1483432865', '1483432865', 0, '', 0, ''),
(15, 29, 29, 1, '1483455945', '1483455945', 0, '', 0, '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
