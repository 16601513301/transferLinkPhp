-- =====================================================
-- 表名：user_agreements（协议表）
-- 用途：存储用户协议、隐私协议、Cookie 政策等协议的历史版本
-- 说明：
-- 1. 保留历史版本，因此不能直接用 UNIQUE(type, status)，否则同类型只能保留一条禁用记录
-- 2. 这里使用生成列 active_type_key，只在 status = 1 时生成唯一值，保证每种协议同一时间只有一个启用版本
-- =====================================================
CREATE TABLE IF NOT EXISTS user_agreements (
    -- 主键ID
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '协议ID，主键自增',

    -- 协议类型
    type VARCHAR(20) NOT NULL COMMENT '协议类型：user=用户协议，privacy=隐私协议，cookie=Cookie政策',

    -- 版本号
    version VARCHAR(20) NOT NULL COMMENT '协议版本号，格式：1.0.0、1.1.0、2.0.0',

    -- 协议标题
    title VARCHAR(255) NOT NULL COMMENT '协议标题，如：用户服务协议、隐私保护政策',

    -- 协议内容
    content LONGTEXT NOT NULL COMMENT '协议正文内容，支持HTML格式，存储完整协议条款',

    -- 简要说明
    summary TEXT NULL COMMENT '协议简要说明，用于列表预览，建议控制在500字内',

    -- 状态
    status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '状态：1=启用，0=禁用/历史版本',

    -- 是否强制确认
    is_required TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否必须同意：1=必须同意，0=可选同意',

    -- 生效时间
    effective_time DATETIME NULL COMMENT '协议生效时间，NULL表示立即生效',

    -- 发布人
    published_by VARCHAR(100) NULL COMMENT '发布人，记录管理员用户名或系统',

    -- 发布时间
    published_at DATETIME NULL COMMENT '协议发布时间',

    -- 创建时间
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录创建时间',

    -- 更新时间
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',

    -- 仅在启用状态下生成唯一值，保证每种协议只有一个启用版本
    active_type_key VARCHAR(20)
        GENERATED ALWAYS AS (CASE WHEN status = 1 THEN type ELSE NULL END) STORED
        COMMENT '启用状态唯一约束辅助列',

    -- 索引
    INDEX idx_type_status (type, status) COMMENT '按类型和状态查询',
    INDEX idx_effective_time (effective_time) COMMENT '按生效时间查询',

    -- 唯一约束
    UNIQUE KEY uk_type_version (type, version) COMMENT '同一协议类型下版本号唯一',
    UNIQUE KEY uk_active_type (active_type_key) COMMENT '确保每种协议只有一个启用版本'

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='协议表：存储用户协议、隐私协议等各类协议的历史版本';
