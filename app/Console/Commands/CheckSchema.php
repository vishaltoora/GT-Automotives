<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckSchema extends Command
{
    protected $signature = 'schema:check';
    protected $description = 'Check the database schema for missing columns';

    public function handle()
    {
        $this->info('Checking database schema...');
        
        // Check brands table
        if (Schema::hasTable('brands')) {
            $this->info('Brands table exists');
            $columns = Schema::getColumnListing('brands');
            $this->info('Columns: ' . implode(', ', $columns));
            
            if (!in_array('is_active', $columns)) {
                $this->error('Missing is_active column in brands table');
            }
        } else {
            $this->error('Brands table does not exist');
        }
        
        // Check products table
        if (Schema::hasTable('products')) {
            $this->info('Products table exists');
            $columns = Schema::getColumnListing('products');
            $this->info('Columns: ' . implode(', ', $columns));
        } else {
            $this->error('Products table does not exist');
        }
        
        // Check sizes table
        if (Schema::hasTable('sizes')) {
            $this->info('Sizes table exists');
            $columns = Schema::getColumnListing('sizes');
            $this->info('Columns: ' . implode(', ', $columns));
        } else {
            $this->error('Sizes table does not exist');
        }
        
        return 0;
    }
} 