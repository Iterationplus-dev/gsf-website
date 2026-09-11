<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'action' => 'updated',
            'subject_type' => Content::class,
            'subject_id' => 1,
            'changed_fields' => ['status'],
        ];
    }
}
