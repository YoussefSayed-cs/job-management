<?php

namespace Database\Seeders;

use App\Concerns\SeedsFromJson;
use Illuminate\Database\Seeder;
use Shagalni\Shared\Models\AiAnalysis;

class AiAnalysisSeeder extends Seeder
{
    use SeedsFromJson;

    public function run(): void
    {
        $this->seedFromJson(AiAnalysis::class, 'ai_analyses');
    }
}
