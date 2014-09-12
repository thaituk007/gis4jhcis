/*
Navicat MySQL Data Transfer

Source Server         : jhcis
Source Server Version : 50154
Source Host           : localhost:3333
Source Database       : jhcisdb3

Target Server Type    : MYSQL
Target Server Version : 50154
File Encoding         : 65001

Date: 2014-09-12 12:55:24
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `menugis`
-- ----------------------------
DROP TABLE IF EXISTS `menugis`;
CREATE TABLE `menugis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menugroup` varchar(255) DEFAULT NULL,
  `menuname` varchar(255) DEFAULT NULL,
  `menulink` varchar(255) DEFAULT NULL,
  `detail` text,
  `mark` varchar(255) DEFAULT NULL,
  `dateupdate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of menugis
-- ----------------------------
INSERT INTO `menugis` VALUES ('1', '1', 'configdefault', '../maps/setconfig.php', 'configdefault', 'navy', '2014-07-02 12:03:08');
INSERT INTO `menugis` VALUES ('2', '1', 'syncdata', '../main/syncdata.php', 'syncdata', 'light-blue', '2014-07-03 12:03:08');
INSERT INTO `menugis` VALUES ('3', '1', 'ส่งออกพิกัดหมู่บ้าน', '../main/export_village.php', 'ส่งออกพิกัดหมู่บ้าน', 'aqua', '2014-07-04 12:03:08');
INSERT INTO `menugis` VALUES ('4', '1', 'ส่งออกพิกัดหลังคาเรือน', '../main/export.php', 'ส่งออกพิกัดหลังคาเรือน', 'red', '2014-07-05 12:03:08');
INSERT INTO `menugis` VALUES ('5', '1', 'importdata', '../main/import.php', 'importdata', 'green', '2014-07-06 12:03:08');
INSERT INTO `menugis` VALUES ('6', '1', 'movepoint', '../maps/movepoints.php', 'movepoint', 'yellow', '2014-07-07 12:03:08');
INSERT INTO `menugis` VALUES ('7', '1', 'กำหนดหมู่บ้านรับผิดชอบ', '../main/update_doctor.php', 'กำหนดหมู่บ้านรับผิดชอบ', 'purple', '2014-07-08 12:03:08');
INSERT INTO `menugis` VALUES ('8', '2', 'พิกัดหมู่บ้าน', '../maps/markvillage.php', 'พิกัดหมู่บ้าน', 'blue', '2014-07-09 12:03:08');
INSERT INTO `menugis` VALUES ('9', '2', 'พิกัดหลังคาเรือน', '../maps/markhouse.php', 'พิกัดหลังคาเรือน', 'maroon', '2014-07-10 12:03:08');
INSERT INTO `menugis` VALUES ('10', '2', 'พิกัดศาสนสถาน', '../maps/marktemple.php', 'พิกัดศาสนสถาน', 'navy', '2014-07-11 12:03:08');
INSERT INTO `menugis` VALUES ('11', '2', 'พิกัดโรงเรียน', '../maps/markschool.php', 'พิกัดโรงเรียน', 'light-blue', '2014-07-12 12:03:08');
INSERT INTO `menugis` VALUES ('12', '3', 'หมู่บ้าน', '../maps/villagegis.php', 'หมู่บ้าน', 'aqua', '2014-07-13 12:03:08');
INSERT INTO `menugis` VALUES ('13', '3', 'ศาสนสถาน', '../maps/templegis.php', 'ศาสนสถาน', 'red', '2014-07-14 12:03:08');
INSERT INTO `menugis` VALUES ('14', '3', 'โรงเรียน', '../maps/schoolgis.php', 'โรงเรียน', 'green', '2014-07-15 12:03:08');
INSERT INTO `menugis` VALUES ('15', '3', 'ปิรามิดปชก.', '../chart/chart_pyramid.php', 'ปิรามิดปชก.', 'yellow', '2014-07-16 12:03:08');
INSERT INTO `menugis` VALUES ('16', '3', 'ค้นหาพิกัดจากชื่อ/CID', '../maps/search.php', 'ค้นหาพิกัดจากชื่อ/CID', 'purple', '2014-07-17 12:03:08');
INSERT INTO `menugis` VALUES ('17', '3', 'ปชก.ตามกลุ่มอายุ', '../maps/person.php', 'ปชก.ตามกลุ่มอายุ', 'blue', '2014-07-18 12:03:08');
INSERT INTO `menugis` VALUES ('18', '3', 'หลังคาเรือนรายหมู่บ้าน', '../maps/housegis.php', 'หลังคาเรือนรายหมู่บ้าน', 'maroon', '2014-07-19 12:03:08');
INSERT INTO `menugis` VALUES ('19', '3', 'บุคคลสำคัญ', '../maps/persontype.php', 'บุคคลสำคัญ', 'navy', '2014-07-20 12:03:08');
INSERT INTO `menugis` VALUES ('20', '3', 'รัศมีจากหลังคาเรือน', '../maps/buffers.php', 'รัศมีจากหลังคาเรือน', 'light-blue', '2014-07-21 12:03:08');
INSERT INTO `menugis` VALUES ('21', '3', 'บ้านที่อสม.รับผิดชอบ', '../maps/personvola.php', 'บ้านที่อสม.รับผิดชอบ', 'aqua', '2014-07-22 12:03:08');
INSERT INTO `menugis` VALUES ('22', '4', 'เยี่ยมบ้านทุกกลุ่ม', '../maps/home_visit_all.php', 'เยี่ยมบ้านทุกกลุ่ม', 'red', '2014-07-23 12:03:08');
INSERT INTO `menugis` VALUES ('23', '4', 'เยี่ยมบ้านผู้สูงอายุ', '../maps/home_visit_old.php', 'เยี่ยมบ้านผู้สูงอายุ', 'green', '2014-07-24 12:03:08');
INSERT INTO `menugis` VALUES ('24', '4', 'เยี่ยมบ้านผู้ป่วยเรื้อรัง', '../maps/home_visit_chronic.php', 'เยี่ยมบ้านผู้ป่วยเรื้อรัง', 'yellow', '2014-07-25 12:03:08');
INSERT INTO `menugis` VALUES ('25', '4', 'เยี่ยมบ้านผู้พิการ', '../maps/home_visit_disorder.php', 'เยี่ยมบ้านผู้พิการ', 'purple', '2014-07-26 12:03:08');
INSERT INTO `menugis` VALUES ('26', '4', 'เยี่ยมบ้านผู้ป่วยวัณโรค', '../maps/home_visit_tb.php', 'เยี่ยมบ้านผู้ป่วยวัณโรค', 'blue', '2014-07-27 12:03:08');
INSERT INTO `menugis` VALUES ('27', '4', 'เยี่ยมบ้านวัณโรค2', '../maps/home_visit_tb2.php', 'เยี่ยมบ้านวัณโรค2', 'maroon', '2014-07-28 12:03:08');
INSERT INTO `menugis` VALUES ('28', '4', 'เยี่ยมหญิงหลังคลอด', '../maps/mch.php', 'เยี่ยมหญิงหลังคลอด', 'navy', '2014-07-29 12:03:08');
INSERT INTO `menugis` VALUES ('29', '4', 'หญิงคั้งครรภ์12week', '../maps/anc_12_w.php', 'หญิงคั้งครรภ์12week', 'light-blue', '2014-07-30 12:03:08');
INSERT INTO `menugis` VALUES ('30', '4', 'ฝากครรภ์ครบคุณภาพ', '../maps/anc_4_q.php', 'ฝากครรภ์ครบคุณภาพ', 'aqua', '2014-07-31 12:03:08');
INSERT INTO `menugis` VALUES ('31', '4', 'ทะเบียนหญิงตั้งครรภ์', '../maps/anc.php', 'ทะเบียนหญิงตั้งครรภ์', 'red', '2014-08-01 12:03:08');
INSERT INTO `menugis` VALUES ('32', '4', 'หญิงตั้งครรภ์ตรวจฟัน', '../maps/anc_tooth.php', 'หญิงตั้งครรภ์ตรวจฟัน', 'green', '2014-08-02 12:03:08');
INSERT INTO `menugis` VALUES ('33', '4', 'เยี่ยมหลังคลอด', '../maps/mch.php', 'เยี่ยมหลังคลอด', 'yellow', '2014-08-03 12:03:08');
INSERT INTO `menugis` VALUES ('34', '4', 'โภชนาการเด็ก', '../maps/nutri.php', 'โภชนาการเด็ก', 'purple', '2014-08-04 12:03:08');
INSERT INTO `menugis` VALUES ('35', '4', 'พัฒนาการเด็ก', '../maps/devolop.php', 'พัฒนาการเด็ก', 'blue', '2014-08-05 12:03:08');
INSERT INTO `menugis` VALUES ('36', '4', 'เด็ก9-24เดือนตรวจฟัน', '../maps/epi_tooth.php', 'เด็ก9-24เดือนตรวจฟัน', 'maroon', '2014-08-06 12:03:08');
INSERT INTO `menugis` VALUES ('37', '4', 'ความครอบคุลมวัคซีน', '../maps/epi_cover_all.php', 'ความครอบคุลมวัคซีน', 'navy', '2014-08-07 12:03:08');
INSERT INTO `menugis` VALUES ('38', '4', 'เด็กขาดนัดวัคซีน', '../maps/epi_appoint_miss.php', 'เด็กขาดนัดวัคซีน', 'light-blue', '2014-08-08 12:03:08');
INSERT INTO `menugis` VALUES ('39', '4', 'ประเภทผู้สูงอายุ', '../maps/old_3d.php', 'ประเภทผู้สูงอายุ', 'aqua', '2014-08-09 12:03:08');
INSERT INTO `menugis` VALUES ('40', '4', 'ผู้สูงอายุพึงประสงค์', '../maps/old_health.php', 'ผู้สูงอายุพึงประสงค์', 'red', '2014-08-10 12:03:08');
INSERT INTO `menugis` VALUES ('41', '4', 'เยี่ยมบ้านผู้สูงอายุ', '../maps/home_visit_old.php', 'เยี่ยมบ้านผู้สูงอายุ', 'green', '2014-08-11 12:03:08');
INSERT INTO `menugis` VALUES ('42', '4', 'จ่ายถุงยางอนามัย', '../maps/condom_fp.php', 'จ่ายถุงยางอนามัย', 'yellow', '2014-07-12 12:03:08');
INSERT INTO `menugis` VALUES ('43', '4', 'หญิงอายุต่ำกว่า20ปีคุมดำเนิด', '../maps/female_mary_20.php', 'หญิงอายุต่ำกว่า20ปีคุมดำเนิด', 'purple', '2014-08-13 12:03:08');
INSERT INTO `menugis` VALUES ('44', '4', 'หญิงอายุ20-44ปีคุมดำเนิด', '../maps/female_mary_44.php', 'หญิงอายุ20-44ปีคุมดำเนิด', 'blue', '2014-08-14 12:03:08');
INSERT INTO `menugis` VALUES ('45', '5', 'ผู้ที่ป่วยด้วยโรค506', '../maps/epe0.php', 'ผู้ที่ป่วยด้วยโรค506', 'maroon', '2014-08-15 12:03:08');
INSERT INTO `menugis` VALUES ('46', '5', 'คัดกรองภาวะซึมเศร้า', '../maps/depress.php', 'คัดกรองภาวะซึมเศร้า', 'navy', '2014-08-16 12:03:08');
INSERT INTO `menugis` VALUES ('47', '5', 'ทะเบียนผู้ป่วยโรคเรื้อรัง', '../maps/chronic.php', 'ทะเบียนผู้ป่วยโรคเรื้อรัง', 'light-blue', '2014-08-17 12:03:08');
INSERT INTO `menugis` VALUES ('48', '5', 'ทะเบียนผู้ป่วยDM/HT', '../maps/chronic_dmht.php', 'ทะเบียนผู้ป่วยDM/HT', 'aqua', '2014-08-18 12:03:08');
INSERT INTO `menugis` VALUES ('49', '5', 'รายงานการคัดกรอง', '../maps/gis_ncd_screen.php', 'รายงานการคัดกรอง', 'red', '2014-08-19 12:03:08');
INSERT INTO `menugis` VALUES ('50', '5', 'Labเบาหวาน/ความดัน', '../maps/dm_lab.php', 'Labเบาหวาน/ความดัน', 'green', '2014-08-20 12:03:08');
INSERT INTO `menugis` VALUES ('51', '5', 'ประเมินลูกบอล7สี', '../maps/ncdrisk.php', 'ประเมินลูกบอล7สี', 'yellow', '2014-08-21 12:03:08');
INSERT INTO `menugis` VALUES ('52', '5', 'มะเร็งปากมดลูก', '../maps/papsmear.php', 'มะเร็งปากมดลูก', 'purple', '2014-08-22 12:03:08');
INSERT INTO `menugis` VALUES ('53', '5', 'วัดรอบเอว', '../maps/ncd_waist.php', 'วัดรอบเอว', 'blue', '2014-08-23 12:03:08');
INSERT INTO `menugis` VALUES ('54', '5', 'ความครอบคลุมวัคซีน', '../maps/epi_cover_all.php', 'ความครอบคลุมวัคซีน', 'maroon', '2014-08-24 12:03:08');
INSERT INTO `menugis` VALUES ('55', '5', 'เด็กที่ขาดนัดวัคซีน', '../maps/appmiss.php', 'เด็กที่ขาดนัดวัคซีน', 'navy', '2014-08-25 12:03:08');
INSERT INTO `menugis` VALUES ('56', '5', 'จ่ายถุงยางอนามัยงานเอดส์', '../maps/condom_aids.php', 'จ่ายถุงยางอนามัย', 'light-blue', '2014-08-26 12:03:08');
INSERT INTO `menugis` VALUES ('57', '5', 'เยี่ยมบ้านวัณโรค', '../maps/home_visit_tb.php', 'เยี่ยมบ้านวัณโรค', 'aqua', '2014-08-27 12:03:08');
INSERT INTO `menugis` VALUES ('59', '5', 'รายงานการตรวจstool', '../maps/stool_ov.php', 'รายงานการตรวจstool', 'green', '2014-08-28 12:03:08');
INSERT INTO `menugis` VALUES ('60', '5', 'รายงานผลตรวจstoolแบบที่2', '../maps/home_stool.php', 'รายงานผลแบบที่2', 'yellow', '2014-08-29 12:03:08');
INSERT INTO `menugis` VALUES ('61', '5', 'รายงานผลอัลตร้าซาวด์', '../maps/ultra.php', 'รายงานผลอัลตร้าซาวด์', 'purple', '2014-08-30 12:03:08');
INSERT INTO `menugis` VALUES ('62', '5', 'การตรวจสารเคมีในเกษตรกร', '../maps/farmer.php', 'การตรวจสารเคมีในเกษตรกร', 'blue', '2014-08-31 12:03:08');
INSERT INTO `menugis` VALUES ('63', '6', 'จ่ายยาสมุนไพร', '../maps/drug_panthai.php', 'จ่ายยาสมุนไพร', 'maroon', '2014-09-01 12:03:08');
INSERT INTO `menugis` VALUES ('64', '6', 'หัตถการแพทย์แผนไทย', '../maps/proced_panthai.php', 'หัตถการแพทย์แผนไทย', 'navy', '2014-09-02 12:03:08');
INSERT INTO `menugis` VALUES ('65', '6', 'รายงานการรับบริการ', '../maps/visit.php', 'รายงานการรับบริการ', 'light-blue', '2014-09-03 12:03:08');
INSERT INTO `menugis` VALUES ('66', '6', 'รายงานอื่นๆ', '../main/service.php', 'รายงานอื่นๆ', 'aqua', '2014-09-04 12:03:08');
INSERT INTO `menugis` VALUES ('67', '6', 'รายงานการนัด', '../maps/appmiss.php', 'รายงานการนัด', 'red', '2014-09-05 12:03:08');
INSERT INTO `menugis` VALUES ('68', '6', 'ผลการตรวจLabต่างๆ', '../maps/lab_all.php', 'ผลการตรวจLabต่างๆ', 'green', '2014-09-06 12:03:08');
INSERT INTO `menugis` VALUES ('69', '7', 'ทะเบียนผู้พิการ', '../maps/unable.php', 'ทะเบียนผู้พิการ', 'yellow', '2014-09-07 12:03:08');
INSERT INTO `menugis` VALUES ('70', '8', 'เลขบัตรประชาชนผิด', '../maps/gis_cid_else.php', 'เลขบัตรประชาชนผิด', 'purple', '2014-09-08 12:03:08');
INSERT INTO `menugis` VALUES ('71', '2', 'พิกัดแหล่งน้ำสาธารณะ', '../maps/markwater.php', 'พิกัดแหล่งน้ำสาธารณะ', 'blue', '2014-09-09 12:03:08');
INSERT INTO `menugis` VALUES ('72', '2', 'พิกัดร้านอาหาร', '../maps/markfoodshop.php', 'พิกัดร้านอาหาร', 'maroon', '2014-09-10 12:03:08');
INSERT INTO `menugis` VALUES ('73', '2', 'พิกัดสถานประกอบการ', '../maps/markbusiness.php', 'พิกัดสถานประกอบการ', 'navy', '2014-09-11 12:03:08');
INSERT INTO `menugis` VALUES ('74', '4', 'โภชนาการผู้สูงอายุ', '../maps/old_nutri.php', 'โภชนาการผู้สูงอายุ', 'light-blue', '2014-07-12 12:03:08');
