<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(Company::class, 'companies');
    }
}
