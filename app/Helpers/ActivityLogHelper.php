<?php

namespace App\Helpers;

class ActivityLogHelper
{
    public static function log(int $userId, string $action, string $table, mixed $oldData = null, mixed $newData = null): array
    {
        return [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $table,
            'old_data' => $oldData,
            'new_data' => $newData,
            'created_at' => now(),
        ];
    }
}
