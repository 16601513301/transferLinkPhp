<?php

declare(strict_types=1);

namespace App\OpenApi\Schemas;

class AgreementListItemSchema
{
    public int $id = 0;

    public string $type = '';

    public string $type_name = '';

    public string $version = '';

    public string $title = '';

    public string $summary = '';

    public int $status = 0;

    public string $status_text = '';

    public int $is_required = 0;

    public string $effective_time = '';

    public string $published_by = '';

    public string $published_at = '';

    public string $created_at = '';

    public string $updated_at = '';
}
