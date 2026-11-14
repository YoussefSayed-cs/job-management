<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(Company::class, 'companies');
    }
}
