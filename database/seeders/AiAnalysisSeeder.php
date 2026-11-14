<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use App\Models\AiAnalysis;
use Illuminate\Database\Seeder;

class AiAnalysisSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(AiAnalysis::class, 'ai_analyses');
    }
}
