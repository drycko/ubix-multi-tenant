<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixTenantModelNamespaces extends Command
{
    protected $signature = 'fix:tenant-models';
    protected $description = 'Fix namespaces and add soft deletes to tenant models';

    public function handle()
    {
        $path = app_path('Models/Tenant');
        $files = File::files($path);

        foreach ($files as $file) {
            $content = file_get_contents($file->getPathname());
            
            // Fix namespace - ensure it's exactly what we want
            $content = preg_replace(
                '/namespace App\\\\.*?;/',
                'namespace App\\Models\\Tenant;',
                $content
            );

            // Add SoftDeletes use statement if not exists
            if (!str_contains($content, 'use Illuminate\Database\Eloquent\SoftDeletes;')) {
                $content = str_replace(
                    'use Illuminate\Database\Eloquent\Model;',
                    "use Illuminate\Database\Eloquent\Model;\nuse Illuminate\Database\Eloquent\SoftDeletes;\nuse Illuminate\Database\Eloquent\Relations\BelongsTo;\nuse Illuminate\Database\Eloquent\Relations\HasMany;\nuse Illuminate\Database\Eloquent\Relations\HasOne;",
                    $content
                );
            }

            // Fix PropertyScope import
            $content = str_replace(
                ['use App\App\Models\Tenant\Tenant\Scopes\PropertyScope', 'use App\Models\Tenant\Scopes\PropertyScope'],
                'use App\Models\Scopes\PropertyScope',
                $content
            );

            // Add SoftDeletes trait if not exists (avoid duplicates)
            if (!str_contains($content, 'use SoftDeletes')) {
                $content = preg_replace(
                    '/use HasFactory(?:, SoftDeletes)?;/',
                    'use HasFactory, SoftDeletes;',
                    $content
                );
            }

            // Remove any duplicate SoftDeletes mentions
            $content = str_replace(', SoftDeletes, SoftDeletes', ', SoftDeletes', $content);

            // Add return types to relationships if missing
            $content = preg_replace(
                '/public function (belongs[^(]+|has[^(]+)\(\)/',
                'public function $1(): $1',
                $content
            );

            file_put_contents($file->getPathname(), $content);
            $this->info("Fixed {$file->getFilename()}");
        }

        $this->info('All tenant models have been fixed!');
    }
}