<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixMigrationStatus extends Command
{
    protected $signature = 'migration:fix-status';
    protected $description = 'Fix migration status for existing tables';

    public function handle()
    {
        $this->info('Fixing migration status...');
        
        // Check if migrations table exists
        if (!Schema::hasTable('migrations')) {
            $this->error('Migrations table does not exist');
            return 1;
        }
        
        // Get the next batch number
        $nextBatch = DB::table('migrations')->max('batch') + 1;
        
        // Add the brands table migration record
        if (Schema::hasTable('brands')) {
            $migrationName = '2024_01_01_000001_create_brands_table';
            $exists = DB::table('migrations')->where('migration', $migrationName)->exists();
            
            if (!$exists) {
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $nextBatch
                ]);
                $this->info("Added migration record for: {$migrationName}");
            } else {
                $this->info("Migration record already exists for: {$migrationName}");
            }
        }
        
        // Add the sizes table migration record
        if (Schema::hasTable('sizes')) {
            $migrationName = '2024_01_01_000002_create_sizes_table';
            $exists = DB::table('migrations')->where('migration', $migrationName)->exists();
            
            if (!$exists) {
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $nextBatch
                ]);
                $this->info("Added migration record for: {$migrationName}");
            } else {
                $this->info("Migration record already exists for: {$migrationName}");
            }
        }
        
        // Add the sales table migration record
        if (Schema::hasTable('sales')) {
            $migrationName = '2024_01_01_000002_create_sales_table';
            $exists = DB::table('migrations')->where('migration', $migrationName)->exists();
            
            if (!$exists) {
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $nextBatch
                ]);
                $this->info("Added migration record for: {$migrationName}");
            } else {
                $this->info("Migration record already exists for: {$migrationName}");
            }
        }
        
        $this->info('Migration status fixed successfully');
        return 0;
    }
} 