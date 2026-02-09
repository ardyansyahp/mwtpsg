<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;

class GeneratePermission extends Command
{
    protected $signature = 'permission:add {module} {--actions=view,create,edit,delete}';
    protected $description = 'Generate permissions untuk module baru (simpel & cepat)';

    public function handle()
    {
        $module = $this->argument('module');
        $actions = explode(',', $this->option('actions'));
        
        $this->info("🚀 Generating permissions untuk: {$module}");
        $this->newLine();
        
        $created = 0;
        $skipped = 0;
        
        foreach ($actions as $action) {
            $action = trim($action);
            $permissionSlug = strtolower("{$action}.{$module}");
            $permissionName = ucfirst($action) . ' ' . ucwords(str_replace(['_', '-'], ' ', $module));
            
            if (Permission::where('slug', $permissionSlug)->exists()) {
                $this->warn("  ⚠ {$permissionSlug} sudah ada");
                $skipped++;
                continue;
            }
            
            Permission::create([
                'name' => $permissionName,
                'slug' => $permissionSlug,
                'category' => $module,
                'description' => $permissionName,
            ]);
            
            $this->info("  ✓ Created: {$permissionSlug}");
            $created++;
        }
        
        $this->newLine();
        $this->info("✅ Done! Created: {$created}, Skipped: {$skipped}");
        $this->newLine();
        $this->comment("💡 Cara pakai di code:");
        $this->comment("   @can('{$actions[0]}.{$module}')");
        $this->comment("   if (auth()->user()->hasPermission('{$actions[0]}.{$module}'))");
        
        return 0;
    }
}
