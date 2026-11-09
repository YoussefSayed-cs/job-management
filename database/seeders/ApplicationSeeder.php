<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(Application::class, 'applications');
    }
}
