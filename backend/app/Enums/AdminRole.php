<?php

namespace App\Enums;

enum AdminRole: string
{
    case SuperAdmin = 'super_admin';
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Editor => 'Editor',
        };
    }

    /**
     * What the role allows, shown beside the role picker.
     */
    public function description(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Everything an editor can do, plus adding, editing and removing admin accounts.',
            self::Editor => 'Writes, schedules and publishes posts, and manages categories.',
        };
    }
}
