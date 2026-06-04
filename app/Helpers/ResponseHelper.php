<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success(string $message, mixed $data = null): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function error(string $message, mixed $data = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];
    }
}
