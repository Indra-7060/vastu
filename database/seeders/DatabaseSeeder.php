<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeds the admin account plus the dummy Vastutathastu catalogue and journal.
     * Real categories, products and articles are managed from the admin panel.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Dummy Vastutathastu catalogue, journal articles and default homepage sections.
        $this->call([VastuHomeContentSeeder::class]);
    }
}
