<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $company_name;

    public ?string $company_tagline = null;

    public ?string $primary_color = '#2563eb';     // default blue-600

    public ?string $secondary_color = null;

    public ?string $logo_path = null;              // e.g. '/storage/logos/logo.svg'

    public ?string $favicon_path = null;

    public string $theme_mode = 'light';           // 'light', 'dark', 'system'

    public bool $maintenance_mode = false;

    public static function group(): string
    {
        return 'general';
    }
}
