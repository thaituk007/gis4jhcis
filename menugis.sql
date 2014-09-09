/*
Navicat MySQL Data Transfer

Source Server         : jhcis
Source Server Version : 50154
Source Host           : localhost:3333
Source Database       : jhcisdb3

Target Server Type    : MYSQL
Target Server Version : 50154
File Encoding         : 65001

Date: 2014-09-09 00:35:58
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of menugis
-- ----------------------------
INSERT INTO `menugis` VALUES ('1', '1', 'configdefault', '../maps/setconfig.php', 'configdefault', null);
INSERT INTO `menugis` VALUES ('2', '1', 'syncdata', '../main/syncdata.php', 'syncdata', null);
INSERT INTO `menugis` VALUES ('3', '1', 'ส่งออกพิกัดหมู่บ้าน', '../main/export_village.php', 'ส่งออกพิกัดหมู่บ้าน', null);
INSERT INTO `menugis` VALUES ('4', '1', 'ส่งออกพิกัดหลังคาเรือน', '../main/export.php', 'ส่งออกพิกัดหลังคาเรือน', null);
INSERT INTO `menugis` VALUES ('5', '1', 'importdata', '../main/import.php', 'importdata', null);
INSERT INTO `menugis` VALUES ('6', '1', 'movepoint', '../maps/movepoints.php', 'movepoint', null);
INSERT INTO `menugis` VALUES ('7', '1', 'กำหนดหมู่บ้านรับผิดชอบ', '../main/update_doctor.php', 'กำหนดหมู่บ้านรับผิดชอบ', null);
INSERT INTO `menugis` VALUES ('8', '2', 'พิกัดหมู่บ้าน', '../maps/markvillage.php', 'พิกัดหมู่บ้าน', null);
INSERT INTO `menugis` VALUES ('9', '2', 'พิกัดหลังคาเรือน', '../maps/markhouse.php', 'พิกัดหลังคาเรือน', null);
INSERT INTO `menugis` VALUES ('10', '2', 'พิกัดศาสนสถาน', '../maps/marktemple.php', 'พิกัดศาสนสถาน', null);
INSERT INTO `menugis` VALUES ('11', '2', 'พิกัดโรงเรียน', '../maps/markschool.php', 'พิกัดโรงเรียน', null);
INSERT INTO `menugis` VALUES ('12', '3', 'หมู่บ้าน', '../maps/villagegis.php', 'หมู่บ้าน', null);
INSERT INTO `menugis` VALUES ('13', '3', 'ศาสนสถาน', '../maps/templegis.php', 'ศาสนสถาน', null);
INSERT INTO `menugis` VALUES ('14', '3', 'โรงเรียน', '../maps/schoolgis.php', 'โรงเรียน', null);
INSERT INTO `menugis` VALUES ('15', '3', 'ปิรามิดปชก.', '../chart/chart_pyramid.php', 'ปิรามิดปชก.', null);
INSERT INTO `menugis` VALUES ('16', '3', 'ค้นหาพิกัดจากชื่อ/CID', '../maps/search.php', 'ค้นหาพิกัดจากชื่อ/CID', null);
INSERT INTO `menugis` VALUES ('17', '3', 'ปชก.ตามกลุ่มอายุ', '../maps/person.php', 'ปชก.ตามกลุ่มอายุ', null);
INSERT INTO `menugis` VALUES ('18', '3', 'หลังคาเรือนรายหมู่บ้าน', '../maps/housegis.php', 'หลังคาเรือนรายหมู่บ้าน', null);
INSERT INTO `menugis` VALUES ('19', '3', 'บุคคลสำคัญ', '../maps/persontype.php', 'บุคคลสำคัญ', null);
INSERT INTO `menugis` VALUES ('20', '3', 'รัศมีจากหลังคาเรือน', '../maps/buffers.php', 'รัศมีจากหลังคาเรือน', null);
INSERT INTO `menugis` VALUES ('21', '3', 'บ้านที่อสม.รับผิดชอบ', '../maps/personvola.php', 'บ้านที่อสม.รับผิดชอบ', null);
INSERT INTO `menugis` VALUES ('22', '4', 'เยี่ยมบ้านทุกกลุ่ม', '../maps/home_visit_all.php', 'เยี่ยมบ้านทุกกลุ่ม', null);
INSERT INTO `menugis` VALUES ('23', '4', 'เยี่ยมบ้านผู้สูงอายุ', '../maps/home_visit_old.php', 'เยี่ยมบ้านผู้สูงอายุ', null);
INSERT INTO `menugis` VALUES ('24', '4', 'เยี่ยมบ้านผู้ป่วยเรื้อรัง</span></a><li>', '../maps/home_visit_chronic.php', 'เยี่ยมบ้านผู้ป่วยเรื้อรัง</span></a><li>', null);
INSERT INTO `menugis` VALUES ('25', '4', 'เยี่ยมบ้านผู้พิการ', '../maps/home_visit_disorder.php', 'เยี่ยมบ้านผู้พิการ', null);
INSERT INTO `menugis` VALUES ('26', '4', 'เยี่ยมบ้านผู้ป่วยวัณโรค', '../maps/home_visit_tb.php', 'เยี่ยมบ้านผู้ป่วยวัณโรค', null);
INSERT INTO `menugis` VALUES ('27', '4', 'เยี่ยมบ้านวัณโรค2', '../maps/home_visit_tb2.php', 'เยี่ยมบ้านวัณโรค2', null);
INSERT INTO `menugis` VALUES ('28', '4', 'เยี่ยมหญิงหลังคลอด', '../maps/mch.php', 'เยี่ยมหญิงหลังคลอด', null);
INSERT INTO `menugis` VALUES ('29', '4', 'หญิงคั้งครรภ์12week', '../maps/anc_12_w.php', 'หญิงคั้งครรภ์12week', null);
INSERT INTO `menugis` VALUES ('30', '4', 'ฝากครรภ์ครบคุณภาพ', '../maps/anc_4_q.php', 'ฝากครรภ์ครบคุณภาพ', null);
INSERT INTO `menugis` VALUES ('31', '4', 'ทะเบียนหญิงตั้งครรภ์', '../maps/anc.php', 'ทะเบียนหญิงตั้งครรภ์', null);
INSERT INTO `menugis` VALUES ('32', '4', 'หญิงตั้งครรภ์ตรวจฟัน', '../maps/anc_tooth.php', 'หญิงตั้งครรภ์ตรวจฟัน', null);
INSERT INTO `menugis` VALUES ('33', '4', 'เยี่ยมหลังคลอด', '../maps/mch.php', 'เยี่ยมหลังคลอด', null);
INSERT INTO `menugis` VALUES ('34', '4', 'โภชนาการเด็ก', '../maps/nutri.php', 'โภชนาการเด็ก', null);
INSERT INTO `menugis` VALUES ('35', '4', 'พัฒนาการเด็ก', '../maps/devolop.php', 'พัฒนาการเด็ก', null);
INSERT INTO `menugis` VALUES ('36', '4', 'เด็ก9-24เดือนตรวจฟัน', '../maps/epi_tooth.php', 'เด็ก9-24เดือนตรวจฟัน', null);
INSERT INTO `menugis` VALUES ('37', '4', 'ความครอบคุลมวัคซีน', '../maps/epi_cover_all.php', 'ความครอบคุลมวัคซีน', null);
INSERT INTO `menugis` VALUES ('38', '4', 'เด็กขาดนัดวัคซีน', '../maps/epi_appoint_miss.php', 'เด็กขาดนัดวัคซีน', null);
INSERT INTO `menugis` VALUES ('39', '4', 'ประเภทผู้สูงอายุ', '../maps/old_3d.php', 'ประเภทผู้สูงอายุ', null);
INSERT INTO `menugis` VALUES ('40', '4', 'ผู้สูงอายุพึงประสงค์', '../maps/old_health.php', 'ผู้สูงอายุพึงประสงค์', null);
INSERT INTO `menugis` VALUES ('41', '4', 'เยี่ยมบ้านผู้สูงอายุ', '../maps/home_visit_old.php', 'เยี่ยมบ้านผู้สูงอายุ', null);
INSERT INTO `menugis` VALUES ('42', '4', 'จ่ายถุงยางอนามัย', '../maps/condom_fp.php', 'จ่ายถุงยางอนามัย', null);
INSERT INTO `menugis` VALUES ('43', '4', 'หญิงอายุต่ำกว่า20ปีคุมดำเนิด', '../maps/female_mary_20.php', 'หญิงอายุต่ำกว่า20ปีคุมดำเนิด', null);
INSERT INTO `menugis` VALUES ('44', '4', 'หญิงอายุ20-44ปีคุมดำเนิด', '../maps/female_mary_44.php', 'หญิงอายุ20-44ปีคุมดำเนิด', null);
INSERT INTO `menugis` VALUES ('45', '4', 'ผู้ที่ป่วยด้วยโรค506', '../maps/epe0.php', 'ผู้ที่ป่วยด้วยโรค506', null);
INSERT INTO `menugis` VALUES ('46', '5', 'คัดกรองภาวะซึมเศร้า', '../maps/depress.php', 'คัดกรองภาวะซึมเศร้า', null);
INSERT INTO `menugis` VALUES ('47', '5', 'ทะเบียนผู้ป่วยโรคเรื้อรัง', '../maps/chronic.php', 'ทะเบียนผู้ป่วยโรคเรื้อรัง', null);
INSERT INTO `menugis` VALUES ('48', '5', 'ทะเบียนผู้ป่วยDM/HT', '../maps/chronic_dmht.php', 'ทะเบียนผู้ป่วยDM/HT', null);
INSERT INTO `menugis` VALUES ('49', '5', 'รายงานการคัดกรอง', '../maps/gis_ncd_screen.php', 'รายงานการคัดกรอง', null);
INSERT INTO `menugis` VALUES ('50', '5', 'Labเบาหวาน/ความดัน', '../maps/dm_lab.php', 'Labเบาหวาน/ความดัน', null);
INSERT INTO `menugis` VALUES ('51', '5', 'ประเมินลูกบอล7สี', '../maps/ncdrisk.php', 'ประเมินลูกบอล7สี', null);
INSERT INTO `menugis` VALUES ('52', '5', 'มะเร็งปากมดลูก', '../maps/papsmear.php', 'มะเร็งปากมดลูก', null);
INSERT INTO `menugis` VALUES ('53', '5', 'วัดรอบเอว', '../maps/ncd_waist.php', 'วัดรอบเอว', null);
INSERT INTO `menugis` VALUES ('54', '5', 'ความครอบคลุมวัคซีน', '../maps/epi_cover_all.php', 'ความครอบคลุมวัคซีน', null);
INSERT INTO `menugis` VALUES ('55', '5', 'เด็กที่ขาดนัดวัคซีน', '../maps/appmiss.php', 'เด็กที่ขาดนัดวัคซีน', null);
INSERT INTO `menugis` VALUES ('56', '5', 'จ่ายถุงยางอนามัย', '../maps/condom_aids.php', 'จ่ายถุงยางอนามัย', null);
INSERT INTO `menugis` VALUES ('57', '5', 'เยี่ยมบ้านวัณโรค', '../maps/home_visit_tb.php', 'เยี่ยมบ้านวัณโรค', null);
INSERT INTO `menugis` VALUES ('58', '5', 'เยี่ยมบ้านวัณโรค2', '../maps/home_visit_tb2.php', 'เยี่ยมบ้านวัณโรค2', null);
INSERT INTO `menugis` VALUES ('59', '5', 'รายงานการตรวจstool', '../maps/stool_ov.php', 'รายงานการตรวจstool', null);
INSERT INTO `menugis` VALUES ('60', '5', 'รายงานผลแบบที่2', '../maps/home_stool.php', 'รายงานผลแบบที่2', null);
INSERT INTO `menugis` VALUES ('61', '5', 'รายงานผลอัลตร้าซาวด์', '../maps/ultra.php', 'รายงานผลอัลตร้าซาวด์', null);
INSERT INTO `menugis` VALUES ('62', '5', 'รายงานการตรวจ', '../maps/farmer.php', 'รายงานการตรวจ', null);
INSERT INTO `menugis` VALUES ('63', '5', 'จ่ายยาสมุนไพร', '../maps/drug_panthai.php', 'จ่ายยาสมุนไพร', null);
INSERT INTO `menugis` VALUES ('64', '5', 'หัตถการแพทย์แผนไทย</a></li', '../maps/proced_panthai.php', 'หัตถการแพทย์แผนไทย</a></li', null);
INSERT INTO `menugis` VALUES ('65', '5', 'รายงานการรับบริการ', '../maps/visit.php', 'รายงานการรับบริการ', null);
INSERT INTO `menugis` VALUES ('66', '5', 'รายงานอื่นๆ', '../main/service.php', 'รายงานอื่นๆ', null);
INSERT INTO `menugis` VALUES ('67', '5', 'รายงานการนัด', '../maps/appmiss.php', 'รายงานการนัด', null);
INSERT INTO `menugis` VALUES ('68', '5', 'ผลการตรวจLabต่างๆ', '../maps/lab_all.php', 'ผลการตรวจLabต่างๆ', null);
INSERT INTO `menugis` VALUES ('69', '5', 'ทะเบียนผู้พิการ', '../maps/unable.php', 'ทะเบียนผู้พิการ', null);
INSERT INTO `menugis` VALUES ('70', '5', 'เลขบัตรประชาชนผิด', '../maps/gis_cid_else.php', 'เลขบัตรประชาชนผิด', null);
