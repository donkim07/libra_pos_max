<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.company_name', 'Your Company Name');
        $this->migrator->add('general.company_tagline', 'Building great software in Dar es Salaam');
        $this->migrator->add('general.primary_color', '#2563eb');
        $this->migrator->add('general.secondary_color', null);
        $this->migrator->add('general.logo_path', null);
        $this->migrator->add('general.favicon_path', null);
        $this->migrator->add('general.theme_mode', 'light');
        $this->migrator->add('general.maintenance_mode', false);
    }

    public function down(): void
    {
        // optional: remove keys if rollback needed
    }
};
