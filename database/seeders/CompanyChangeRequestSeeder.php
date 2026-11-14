<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use App\Models\CompanyChangeRequest;
use Illuminate\Database\Seeder;

class CompanyChangeRequestSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(CompanyChangeRequest::class, 'company_change_requests');
    }
}
