<?php

namespace Database\Seeders;

use App\Services\Agriculture\AgricultureKnowledgeService;
use Illuminate\Database\Seeder;

class AgricultureKnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(AgricultureKnowledgeService::class);
        $service->seedKnowledgeGuides();
    }
}
