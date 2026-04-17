/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 80012
 Source Host           : 127.0.0.1:3306
 Source Schema         : transfer_link_php

 Target Server Type    : MySQL
 Target Server Version : 80012
 File Encoding         : 65001

 Date: 17/04/2026 14:13:44
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for qr_login_ticket
-- ----------------------------
DROP TABLE IF EXISTS `qr_login_ticket`;
CREATE TABLE `qr_login_ticket`  (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `ticket_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `scene` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'xingfan_pc_transfer_login',
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `pc_poll_token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `expires_at` datetime(0) NULL DEFAULT NULL,
  `scanned_at` datetime(0) NULL DEFAULT NULL,
  `confirmed_at` datetime(0) NULL DEFAULT NULL,
  `logged_in_at` datetime(0) NULL DEFAULT NULL,
  `scan_user_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `confirm_user_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uniq_qr_login_ticket_ticket_id`(`ticket_id`) USING BTREE,
  UNIQUE INDEX `uniq_qr_login_ticket_pc_poll_token`(`pc_poll_token`) USING BTREE,
  INDEX `idx_qr_login_ticket_status`(`status`) USING BTREE,
  INDEX `idx_qr_login_ticket_expires_at`(`expires_at`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of qr_login_ticket
-- ----------------------------
INSERT INTO `qr_login_ticket` VALUES ('019d99fd-3ba8-7012-a1bb-9f708cebcc74', '9a0fc22c7df47c7a114253e563fa1c54', 'xingfan_pc_transfer_login', 'pending', '698203917e0b6d5b27003f2495367d44', '2026-04-17 05:53:03', NULL, NULL, NULL, '', '', '2026-04-17 05:50:03', '2026-04-17 05:50:03');
INSERT INTO `qr_login_ticket` VALUES ('019d99ff-6597-70b6-a982-e227fecc844e', 'be39046040a9c7096767d2c608cd0663', 'xingfan_pc_transfer_login', 'pending', 'b1b3d079b64ec1533c69dd338c87f2bd', '2026-04-17 05:55:24', NULL, NULL, NULL, '', '', '2026-04-17 05:52:24', '2026-04-17 05:52:24');
INSERT INTO `qr_login_ticket` VALUES ('019d99ff-f2af-7228-a33b-f4745d2f7438', '762b37ffb5de8309a3c4dffc6faa22da', 'xingfan_pc_transfer_login', 'pending', '14569542059abbed48b3fe731aa23d9b', '2026-04-17 05:56:01', NULL, NULL, NULL, '', '', '2026-04-17 05:53:01', '2026-04-17 05:53:01');
INSERT INTO `qr_login_ticket` VALUES ('019d9a0d-c2fc-710a-a833-616d48a4d38e', '4eee9d48e5a7390386acb95e3a1a0971', 'xingfan_pc_transfer_login', 'expired', '54b13761db649b2fc846df90ef3b861e', '2026-04-17 06:11:06', NULL, NULL, NULL, '', '', '2026-04-17 06:08:06', '2026-04-17 06:08:06');

-- ----------------------------
-- Table structure for sms_login_code
-- ----------------------------
DROP TABLE IF EXISTS `sms_login_code`;
CREATE TABLE `sms_login_code`  (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `purpose` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'login',
  `code_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `expires_at` datetime(0) NULL DEFAULT NULL,
  `used_at` datetime(0) NULL DEFAULT NULL,
  `request_ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_sms_login_code_mobile`(`mobile`) USING BTREE,
  INDEX `idx_sms_login_code_purpose`(`purpose`) USING BTREE,
  INDEX `idx_sms_login_code_expires_at`(`expires_at`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sms_login_code
-- ----------------------------
INSERT INTO `sms_login_code` VALUES ('019d95f7-0f75-762d-89b4-98a0a1f362db', '16601513301', 'login', 'f091e76c7b8f748712dfde65e88ba9add2171aaf31e499b1f9666f7115745ff9', '2026-04-16 11:09:49', '2026-04-16 11:05:14', '127.0.0.1', 1, '2026-04-16 11:04:49', '2026-04-16 11:04:49');
INSERT INTO `sms_login_code` VALUES ('019d95f8-08f5-7563-80e8-88028083cc99', '16601513301', 'login', '891d224b2cd69a5fb001cbdd15308fe571455b5e59c69417e947ba79d9e7fbf7', '2026-04-16 11:10:53', '2026-04-16 11:05:53', '127.0.0.1', 1, '2026-04-16 11:05:53', '2026-04-16 11:05:53');
INSERT INTO `sms_login_code` VALUES ('019d95f8-0a9b-7686-b0ad-ef11c6de95cd', '16601513301', 'login', 'd565951f981693d53a13be0c3ab69d252624372f4aba4ae544af1c3dcb63a545', '2026-04-16 11:10:54', '2026-04-16 11:05:54', '127.0.0.1', 1, '2026-04-16 11:05:54', '2026-04-16 11:05:54');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guest',
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uniq_user_email`(`email`) USING BTREE,
  UNIQUE INDEX `uniq_user_mobile`(`mobile`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('019d95f0-b2c9-769e-b03f-ae2cbc960f01', '16601513301', '16601513301', '16601513301@163.com', '$2y$12$SAQmeQH998faYhkm79doZeNx87MxqfDXvXkUhOT4dlFbUr/DHNMhO', 1, 'user', '2026-04-16 10:57:52', '2026-04-16 18:58:32');

-- ----------------------------
-- Table structure for user_agreements
-- ----------------------------
DROP TABLE IF EXISTS `user_agreements`;
CREATE TABLE `user_agreements`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '协议ID，主键自增',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '协议类型：user=用户协议，privacy=隐私协议，cookie=Cookie政策',
  `version` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '协议版本号，格式：1.0.0、1.1.0、2.0.0',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '协议标题，如：用户服务协议、隐私保护政策',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '协议正文内容，支持HTML格式，存储完整协议条款',
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '协议简要说明，用于列表预览，建议控制在500字内',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：1=启用，0=禁用/历史版本',
  `is_required` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否必须同意：1=必须同意，0=可选同意',
  `effective_time` datetime(0) NULL DEFAULT NULL COMMENT '协议生效时间，NULL表示立即生效',
  `published_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '发布人，记录管理员用户名或系统',
  `published_at` datetime(0) NULL DEFAULT NULL COMMENT '协议发布时间',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '记录创建时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '最后更新时间',
  `active_type_key` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS ((case when (`status` = 1) then `type` else NULL end)) STORED COMMENT '启用状态唯一约束辅助列' NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_type_version`(`type`, `version`) USING BTREE COMMENT '同一协议类型下版本号唯一',
  UNIQUE INDEX `uk_active_type`(`active_type_key`) USING BTREE COMMENT '确保每种协议只有一个启用版本',
  INDEX `idx_type_status`(`type`, `status`) USING BTREE COMMENT '按类型和状态查询',
  INDEX `idx_effective_time`(`effective_time`) USING BTREE COMMENT '按生效时间查询'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '协议表：存储用户协议、隐私协议等各类协议的历史版本' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_agreements
-- ----------------------------
INSERT INTO `user_agreements` VALUES (1, 'user', '1.0.0', '星返PC端转链工具用户服务协议', '<h1>星返PC端转链工具用户服务协议</h1>\n<p>欢迎您使用星返PC端转链工具。为保障您在使用本服务过程中的合法权益，请您在使用前认真阅读并充分理解本协议全部内容。您一旦登录、访问或使用本服务，即视为您已经阅读、理解并同意接受本协议全部内容。</p>\n<h2>一、服务说明</h2>\n<p>1. 星返PC端转链工具为用户提供商品链接整理、推广链接转换、短链生成及相关推广辅助功能。</p>\n<p>2. 您理解并同意，平台可根据业务发展、接口能力、监管要求或系统维护需要，对服务内容进行调整、升级、暂停或终止。</p>\n<p>3. 部分功能可能需要依赖第三方平台或第三方接口服务，相关功能效果以实际接入情况为准。</p>\n<h2>二、账号使用规范</h2>\n<p>1. 您应当使用真实、合法、有效的身份信息注册或登录账号，并妥善保管账号、密码、验证码及登录凭证。</p>\n<p>2. 因您保管不善、主动转借、授权他人使用账号造成的风险、损失或责任，由您自行承担。</p>\n<p>3. 您不得以任何方式从事危害平台安全、影响平台正常运营、绕过接口限制、批量爬取数据、恶意攻击或其他违法违规行为。</p>\n<h2>三、推广及使用规则</h2>\n<p>1. 您应在法律法规及平台规则允许的范围内使用转链、短链和推广工具，不得用于虚假宣传、诱导交易、骚扰传播或其他不当用途。</p>\n<p>2. 您使用本服务所生成的推广链接、短链、文案或素材，仅用于合法合规的推广场景，相关推广行为产生的责任由您自行承担。</p>\n<p>3. 因上游平台规则变化、商品下架、接口异常、网络问题等原因导致的链接失效、收益变化或推广受限，平台不承担赔偿责任，但会尽力优化服务体验。</p>\n<h2>四、收益与结算说明</h2>\n<p>1. 使用本服务进行推广所产生的收益，以星返账号、合作平台或上游渠道最终结算结果为准。</p>\n<p>2. 平台不对预估收益、展示收益或过程收益作任何绝对承诺，最终收益可能因订单状态、风控审核、退款售后、平台政策等因素发生变化。</p>\n<h2>五、知识产权</h2>\n<p>1. 本服务相关的软件、页面设计、界面元素、技术文档及平台标识等知识产权归平台或权利人所有。</p>\n<p>2. 未经书面许可，任何个人或组织不得对本服务进行反向工程、复制、传播、出租、出售、转授权或其他超范围使用。</p>\n<h2>六、免责声明</h2>\n<p>1. 平台将尽商业上的合理努力保障服务连续性和安全性，但对因不可抗力、黑客攻击、系统故障、通信异常、第三方原因等导致的服务中断或数据异常，不承担超出法律规定范围的责任。</p>\n<p>2. 对于您因违反法律法规、第三方平台规则或本协议约定所产生的争议、处罚、损失或责任，均由您自行承担。</p>\n<h2>七、协议变更与生效</h2>\n<p>1. 平台有权根据业务发展和管理需要对本协议内容进行修订。修订后的协议将在平台公布后生效。</p>\n<p>2. 若您在协议更新后继续使用本服务，视为您接受修订后的协议内容。</p>\n<p>3. 如您不同意更新后的协议，应立即停止使用本服务。</p>\n<h2>八、其他</h2>\n<p>1. 本协议的订立、执行和解释适用中华人民共和国法律。</p>\n<p>2. 因本协议引起的或与本协议有关的争议，双方应优先协商解决；协商不成的，提交平台所在地有管辖权的人民法院处理。</p>', '适用于星返PC端转链工具的基础服务协议，约定账号使用、推广规则、收益说明、免责声明及争议处理等内容。', 1, 1, NULL, 'system', '2026-04-16 18:00:00', '2026-04-16 18:51:50', '2026-04-16 18:53:24', DEFAULT);
INSERT INTO `user_agreements` VALUES (2, 'privacy', '1.0.0', '星返PC端转链工具隐私政策', '<h1>星返PC端转链工具隐私政策</h1>\n<p>我们非常重视您的个人信息和隐私保护。为帮助您了解我们如何收集、使用、存储和保护您的信息，请您在使用星返PC端转链工具前认真阅读本隐私政策。</p>\n<h2>一、我们收集的信息</h2>\n<p>1. 账号信息：当您使用账号密码登录、手机号验证码登录或扫码登录时，我们可能收集您的账号、手机号、登录标识、昵称、用户编号等必要信息。</p>\n<p>2. 设备与日志信息：为保障服务稳定运行，我们可能记录设备类型、浏览器信息、IP地址、访问时间、操作日志、错误日志等技术信息。</p>\n<p>3. 业务数据：当您使用转链、短链、推广文案等功能时，我们可能处理您主动提交的商品链接、推广内容、备注信息及相关操作结果。</p>\n<h2>二、我们如何使用信息</h2>\n<p>1. 用于账号认证、登录验证、身份识别、风险控制和安全防护。</p>\n<p>2. 用于提供转链、短链生成、推广辅助、结果展示及客户支持等核心功能。</p>\n<p>3. 用于统计分析、服务优化、问题排查、产品改进和体验提升。</p>\n<p>4. 在符合法律法规要求的前提下，用于履行法定义务、配合监管要求或处理投诉争议。</p>\n<h2>三、信息共享与披露</h2>\n<p>1. 我们不会向无关第三方出售您的个人信息。</p>\n<p>2. 为实现基础功能，我们可能在必要范围内与短信服务商、云服务商、第三方接口服务商、合作平台等共享必要信息。</p>\n<p>3. 在法律法规要求、司法机关或行政机关依法要求的情况下，我们可能依据规定披露相关信息。</p>\n<h2>四、信息存储与保护</h2>\n<p>1. 我们会采取合理可行的安全措施保护您的信息，防止数据遭到未经授权的访问、披露、篡改或丢失。</p>\n<p>2. 您的个人信息将保存于实现本服务所必需的期限内；超出保存期限后，我们将根据法律要求及业务需要进行删除或匿名化处理。</p>\n<p>3. 尽管我们会尽力保障信息安全，但互联网并非绝对安全环境，请您妥善保管账号和登录凭证。</p>\n<h2>五、您的权利</h2>\n<p>1. 您有权查询、更正或更新您的相关账号信息。</p>\n<p>2. 在符合法律法规及平台规则的前提下，您可申请注销账号、停止使用相关服务。</p>\n<p>3. 如您对个人信息处理有疑问或投诉建议，可通过平台运营方提供的联系方式与我们联系。</p>\n<h2>六、未成年人保护</h2>\n<p>如您为未成年人，请在监护人指导下阅读并决定是否同意本隐私政策。未经监护人同意，未成年人不应自行注册、登录或使用本服务。</p>\n<h2>七、政策更新</h2>\n<p>1. 我们可能根据法律法规、监管要求或产品调整对本隐私政策进行修订。</p>\n<p>2. 更新后的隐私政策将在平台公布后生效。您继续使用本服务，即表示您已阅读并同意更新后的政策。</p>', '用于说明星返PC端转链工具在登录、转链与推广功能中对账号信息、日志信息及业务数据的收集、使用和保护方式。', 1, 1, NULL, 'system', '2026-04-16 18:00:00', '2026-04-16 18:51:50', '2026-04-16 18:53:24', DEFAULT);

-- ----------------------------
-- Table structure for user_api_token
-- ----------------------------
DROP TABLE IF EXISTS `user_api_token`;
CREATE TABLE `user_api_token`  (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `last_used_at` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uniq_user_api_token_token_hash`(`token_hash`(191)) USING BTREE,
  INDEX `idx_user_api_token_user_id`(`user_id`) USING BTREE,
  CONSTRAINT `fk_user_api_token_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_api_token
-- ----------------------------
INSERT INTO `user_api_token` VALUES ('019d95f0-b2e8-7fdf-8a6e-a6463b86da03', '019d95f0-b2c9-769e-b03f-ae2cbc960f01', 'signup-login', '2d5af51db8891c5a3788f6859960c068c859d676fc99b4438105aa082f7c96be', '2026-05-16 10:57:52', NULL, '2026-04-16 10:57:52', '2026-04-16 10:57:52');
INSERT INTO `user_api_token` VALUES ('019d95f7-70bd-7b66-a882-53ddade62560', '019d95f0-b2c9-769e-b03f-ae2cbc960f01', 'sms-login', '7f5503138333b89a4c8a690875dc36103186b2523a91d261b89731f6995c0cf2', '2026-05-16 11:05:14', NULL, '2026-04-16 11:05:14', '2026-04-16 11:05:14');
INSERT INTO `user_api_token` VALUES ('019d95f8-09af-76a8-b0e0-46bc6ef8d1e3', '019d95f0-b2c9-769e-b03f-ae2cbc960f01', 'sms-login', '74097d3af3b3c769802851e317696c4f46f20d7e339b84779d410542c2fbb839', '2026-05-16 11:05:53', '2026-04-16 11:05:53', '2026-04-16 11:05:53', '2026-04-16 11:05:53');
INSERT INTO `user_api_token` VALUES ('019d95f8-0b57-747f-ae28-0fe18e564fce', '019d95f0-b2c9-769e-b03f-ae2cbc960f01', 'sms-login', '1042a05317fbed53f6d318c28dd5e2aeab679d0c2e8cd76c9d06793168fced26', '2026-05-16 11:05:54', NULL, '2026-04-16 11:05:54', '2026-04-16 11:05:54');
INSERT INTO `user_api_token` VALUES ('019d9964-4ade-73aa-a929-9281c0f8bc02', '019d95f0-b2c9-769e-b03f-ae2cbc960f01', 'password-login', 'de964ad4d6419a27bbd8586b872775e216bed46f376222c527945de733701ac2', '2026-05-17 03:03:00', '2026-04-17 03:35:36', '2026-04-17 03:03:00', '2026-04-17 03:03:00');

SET FOREIGN_KEY_CHECKS = 1;
