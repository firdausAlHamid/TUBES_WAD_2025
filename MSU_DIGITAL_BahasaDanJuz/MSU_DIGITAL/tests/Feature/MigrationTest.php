<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_editions_table_exists()
    {
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable('available_editions'));
    }
}