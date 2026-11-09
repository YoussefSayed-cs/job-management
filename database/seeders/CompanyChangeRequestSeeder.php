<?php

namespace Database\Seeders;

use App\Models\CompanyChangeRequest;
use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;

class CompanyChangeRequestSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(CompanyChangeRequest::class, 'company_change_requests');
    }
}
