<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;
use Shagalni\Shared\Models\CompanyChangeRequest;

class CompanyChangeRequestSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(CompanyChangeRequest::class, 'company_change_requests');
    }
}
