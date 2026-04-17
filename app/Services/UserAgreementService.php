<?php

declare(strict_types=1);

namespace App\Services;

use BaseApi\App;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

class UserAgreementService
{
    public const TYPE_USER = 'user';
    public const TYPE_PRIVACY = 'privacy';
    public const TYPE_COOKIE = 'cookie';

    /**
     * @var array<string, string>
     */
    private const TYPE_LABELS = [
        self::TYPE_USER => '用户协议',
        self::TYPE_PRIVACY => '隐私政策',
        self::TYPE_COOKIE => 'Cookie 政策',
    ];

    /**
     * @return array<int, string>
     */
    public function allowedTypes(): array
    {
        return array_keys(self::TYPE_LABELS);
    }

    public function getCurrent(string $type): ?array
    {
        $type = $this->normalizeType($type);

        $statement = $this->pdo()->prepare(
            <<<SQL
            SELECT id, type, version, title, content, summary, status, is_required, effective_time,
                   published_by, published_at, created_at, updated_at
            FROM user_agreements
            WHERE type = :type
              AND status = 1
              AND (effective_time IS NULL OR effective_time <= NOW())
            ORDER BY COALESCE(effective_time, published_at, created_at) DESC, id DESC
            LIMIT 1
            SQL
        );
        $statement->execute(['type' => $type]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->formatAgreement($row, true) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getHistory(string $type): array
    {
        $type = $this->normalizeType($type);

        $statement = $this->pdo()->prepare(
            <<<SQL
            SELECT id, type, version, title, summary, status, is_required, effective_time,
                   published_by, published_at, created_at, updated_at
            FROM user_agreements
            WHERE type = :type
            ORDER BY id DESC
            SQL
        );
        $statement->execute(['type' => $type]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): array => $this->formatAgreement($row, false),
            is_array($rows) ? $rows : []
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAdminList(?string $type = null, ?int $status = null): array
    {
        $conditions = [];
        $bindings = [];

        if ($type !== null && trim($type) !== '') {
            $conditions[] = 'type = :type';
            $bindings['type'] = $this->normalizeType($type);
        }

        if ($status !== null) {
            $conditions[] = 'status = :status';
            $bindings['status'] = $status;
        }

        $sql = <<<SQL
            SELECT id, type, version, title, summary, status, is_required, effective_time,
                   published_by, published_at, created_at, updated_at
            FROM user_agreements
            SQL;

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY id DESC';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute($bindings);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row): array => $this->formatAgreement($row, false),
            is_array($rows) ? $rows : []
        );
    }

    public function getDetail(?int $id = null, ?string $type = null, ?string $version = null): ?array
    {
        if ($id !== null) {
            $statement = $this->pdo()->prepare(
                <<<SQL
                SELECT id, type, version, title, content, summary, status, is_required, effective_time,
                       published_by, published_at, created_at, updated_at
                FROM user_agreements
                WHERE id = :id
                LIMIT 1
                SQL
            );
            $statement->execute(['id' => $id]);
        } else {
            $normalizedType = $this->normalizeType((string) $type);
            $normalizedVersion = $this->normalizeVersion((string) $version);

            $statement = $this->pdo()->prepare(
                <<<SQL
                SELECT id, type, version, title, content, summary, status, is_required, effective_time,
                       published_by, published_at, created_at, updated_at
                FROM user_agreements
                WHERE type = :type
                  AND version = :version
                LIMIT 1
                SQL
            );
            $statement->execute([
                'type' => $normalizedType,
                'version' => $normalizedVersion,
            ]);
        }

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->formatAgreement($row, true) : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function save(array $payload, ?int $id = null): array
    {
        $type = $this->normalizeType((string) ($payload['type'] ?? ''));
        $version = $this->normalizeVersion((string) ($payload['version'] ?? ''));
        $title = $this->normalizeTitle((string) ($payload['title'] ?? ''));
        $content = $this->normalizeContent((string) ($payload['content'] ?? ''));
        $summary = $this->normalizeSummary($payload['summary'] ?? null);
        $status = $this->normalizeFlag($payload['status'] ?? '1', '状态');
        $isRequired = $this->normalizeFlag($payload['is_required'] ?? '1', '是否必须同意');
        $effectiveTime = $this->normalizeDateTime($payload['effective_time'] ?? null, '生效时间');
        $publishedBy = $this->normalizePublisher($payload['published_by'] ?? null);
        $publishedAt = $this->normalizeDateTime($payload['published_at'] ?? null, '发布时间') ?? $this->now();

        if ($status === 1 && $this->isFutureDateTime($effectiveTime)) {
            throw new RuntimeException('未来生效的协议请先保存为禁用状态，到生效时再启用。');
        }

        $pdo = $this->pdo();

        try {
            $pdo->beginTransaction();

            if ($status === 1) {
                $this->deactivateSameType($pdo, $type, $id);
            }

            if ($id !== null) {
                $current = $this->getDetail($id);
                if ($current === null) {
                    throw new RuntimeException('要更新的协议不存在。');
                }

                $statement = $pdo->prepare(
                    <<<SQL
                    UPDATE user_agreements
                    SET type = :type,
                        version = :version,
                        title = :title,
                        content = :content,
                        summary = :summary,
                        status = :status,
                        is_required = :is_required,
                        effective_time = :effective_time,
                        published_by = :published_by,
                        published_at = :published_at
                    WHERE id = :id
                    SQL
                );
                $statement->execute([
                    'id' => $id,
                    'type' => $type,
                    'version' => $version,
                    'title' => $title,
                    'content' => $content,
                    'summary' => $summary,
                    'status' => $status,
                    'is_required' => $isRequired,
                    'effective_time' => $effectiveTime,
                    'published_by' => $publishedBy,
                    'published_at' => $publishedAt,
                ]);
            } else {
                $statement = $pdo->prepare(
                    <<<SQL
                    INSERT INTO user_agreements (
                        type, version, title, content, summary, status, is_required,
                        effective_time, published_by, published_at
                    ) VALUES (
                        :type, :version, :title, :content, :summary, :status, :is_required,
                        :effective_time, :published_by, :published_at
                    )
                    SQL
                );
                $statement->execute([
                    'type' => $type,
                    'version' => $version,
                    'title' => $title,
                    'content' => $content,
                    'summary' => $summary,
                    'status' => $status,
                    'is_required' => $isRequired,
                    'effective_time' => $effectiveTime,
                    'published_by' => $publishedBy,
                    'published_at' => $publishedAt,
                ]);

                $id = (int) $pdo->lastInsertId();
            }

            $pdo->commit();
        } catch (Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($throwable instanceof RuntimeException) {
                throw $throwable;
            }

            throw $this->mapStorageException($throwable);
        }

        $agreement = $this->getDetail($id);
        if ($agreement === null) {
            throw new RuntimeException('协议保存成功，但读取结果失败。');
        }

        return $agreement;
    }

    public function activate(int $id, string $publishedBy = 'system'): array
    {
        $agreement = $this->getDetail($id);
        if ($agreement === null) {
            throw new RuntimeException('协议不存在。');
        }

        if ($this->isFutureDateTime($agreement['effective_time'] ?? null)) {
            throw new RuntimeException('未来生效的协议暂时不能启用。');
        }

        $pdo = $this->pdo();

        try {
            $pdo->beginTransaction();

            $this->deactivateSameType($pdo, (string) $agreement['type'], $id);

            $statement = $pdo->prepare(
                <<<SQL
                UPDATE user_agreements
                SET status = 1,
                    published_by = :published_by,
                    published_at = :published_at
                WHERE id = :id
                SQL
            );
            $statement->execute([
                'id' => $id,
                'published_by' => $this->normalizePublisher($publishedBy),
                'published_at' => $this->now(),
            ]);

            $pdo->commit();
        } catch (Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($throwable instanceof RuntimeException) {
                throw $throwable;
            }

            throw $this->mapStorageException($throwable);
        }

        $fresh = $this->getDetail($id);
        if ($fresh === null) {
            throw new RuntimeException('协议启用成功，但读取结果失败。');
        }

        return $fresh;
    }

    private function pdo(): PDO
    {
        return App::db()->pdo();
    }

    private function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));

        if (!isset(self::TYPE_LABELS[$type])) {
            throw new RuntimeException('协议类型不合法，仅支持 user、privacy、cookie。');
        }

        return $type;
    }

    private function normalizeVersion(string $version): string
    {
        $version = trim($version);

        if ($version === '') {
            throw new RuntimeException('协议版本号不能为空。');
        }

        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            throw new RuntimeException('协议版本号格式不正确，请使用 1.0.0 这种格式。');
        }

        return $version;
    }

    private function normalizeTitle(string $title): string
    {
        $title = trim($title);

        if ($title === '') {
            throw new RuntimeException('协议标题不能为空。');
        }

        if (mb_strlen($title) > 255) {
            throw new RuntimeException('协议标题长度不能超过 255 个字符。');
        }

        return $title;
    }

    private function normalizeContent(string $content): string
    {
        $content = trim($content);

        if ($content === '') {
            throw new RuntimeException('协议正文不能为空。');
        }

        return $content;
    }

    private function normalizeSummary(mixed $summary): ?string
    {
        if ($summary === null) {
            return null;
        }

        $summary = trim((string) $summary);
        if ($summary === '') {
            return null;
        }

        if (mb_strlen($summary) > 500) {
            throw new RuntimeException('协议简要说明不能超过 500 个字符。');
        }

        return $summary;
    }

    private function normalizePublisher(mixed $publishedBy): string
    {
        $publishedBy = trim((string) $publishedBy);

        if ($publishedBy === '') {
            return 'system';
        }

        if (mb_strlen($publishedBy) > 100) {
            throw new RuntimeException('发布人长度不能超过 100 个字符。');
        }

        return $publishedBy;
    }

    private function normalizeDateTime(mixed $value, string $fieldLabel): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            throw new RuntimeException($fieldLabel . '格式不正确，请使用 YYYY-MM-DD HH:MM:SS。');
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function normalizeFlag(mixed $value, string $fieldLabel): int
    {
        $value = strtolower(trim((string) $value));

        if ($value === '' || $value === '1' || $value === 'true') {
            return 1;
        }

        if ($value === '0' || $value === 'false') {
            return 0;
        }

        throw new RuntimeException($fieldLabel . '仅支持 0 或 1。');
    }

    private function isFutureDateTime(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $timestamp = strtotime($value);

        return $timestamp !== false && $timestamp > time();
    }

    private function deactivateSameType(PDO $pdo, string $type, ?int $excludeId = null): void
    {
        $sql = 'UPDATE user_agreements SET status = 0 WHERE type = :type AND status = 1';
        $bindings = ['type' => $type];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $bindings['exclude_id'] = $excludeId;
        }

        $statement = $pdo->prepare($sql);
        $statement->execute($bindings);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatAgreement(array $row, bool $includeContent): array
    {
        $agreement = [
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'type' => (string) ($row['type'] ?? ''),
            'type_name' => self::TYPE_LABELS[(string) ($row['type'] ?? '')] ?? (string) ($row['type'] ?? ''),
            'version' => (string) ($row['version'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'summary' => (string) ($row['summary'] ?? ''),
            'status' => isset($row['status']) ? (int) $row['status'] : 0,
            'status_text' => ((int) ($row['status'] ?? 0)) === 1 ? '启用' : '禁用',
            'is_required' => isset($row['is_required']) ? (int) $row['is_required'] : 0,
            'effective_time' => (string) ($row['effective_time'] ?? ''),
            'published_by' => (string) ($row['published_by'] ?? ''),
            'published_at' => (string) ($row['published_at'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];

        if ($includeContent) {
            $agreement['content'] = (string) ($row['content'] ?? '');
        }

        return $agreement;
    }

    private function mapStorageException(Throwable $throwable): RuntimeException
    {
        if ($throwable instanceof PDOException) {
            $message = (string) $throwable->getMessage();

            if (str_contains($message, 'uk_active_type')) {
                return new RuntimeException('当前协议类型已经有启用版本，请先停用旧版本。');
            }

            if (str_contains($message, 'uk_type_version') || str_contains($message, 'Duplicate entry')) {
                return new RuntimeException('同一协议类型下的版本号已经存在，请更换版本号。');
            }
        }

        return new RuntimeException('协议保存失败，请检查数据后重试。');
    }

    private function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
