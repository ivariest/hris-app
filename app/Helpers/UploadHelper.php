<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadHelper
{
    public static function store(UploadedFile $file, string $directory = 'uploads'): string
    {
        $name = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();

        return $file->storeAs($directory, $name, 'public');
    }

    public static function delete(?string $path, string $disk = 'public'): bool
    {
        if (! $path) {
            return false;
        }

        return Storage::disk($disk)->delete($path);
    }
}
