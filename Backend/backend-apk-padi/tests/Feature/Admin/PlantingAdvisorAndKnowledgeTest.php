<?php

namespace Tests\Feature\Admin;

use App\Models\Farm;
use App\Models\User;
use App\Services\Agriculture\AgricultureKnowledgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlantingAdvisorAndKnowledgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_calculate_best_planting_window_recommendation(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);

        $farm = Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Advisor Test',
            'latitude' => -6.3031,
            'longitude' => 107.3009,
            'area_ha' => 3.0,
            'irrigation_type' => 'technical',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/planting-calendar/recommend-planting-window', [
            'farm_id' => $farm->id,
            'planned_date' => '2026-11-01',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'farm_name',
                'variety_name',
                'duration_days',
                'recommended_planting_window' => ['start', 'end', 'label'],
                'estimated_harvest_date',
                'climate_suitability',
                'milestones',
            ],
        ]);
    }

    public function test_can_access_knowledge_base_articles_and_detail(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        app(AgricultureKnowledgeService::class)->seedKnowledgeGuides();

        $indexResponse = $this->actingAs($admin)->get('/admin/knowledge');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Pusat Pengetahuan');
        $indexResponse->assertSee('Panduan Pemupukan Berimbang');

        // Test API Endpoint
        $apiResponse = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/knowledge-base');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['category', 'title', 'slug', 'summary', 'content_markdown'],
            ],
        ]);
    }

    public function test_admin_can_create_update_and_delete_knowledge_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        // Create article
        $createResponse = $this->actingAs($admin)->post('/admin/knowledge', [
            'category' => 'pemupukan',
            'title' => 'Panduan Dosis Urea Sawah Test',
            'summary' => 'Ringkasan dosis urea untuk sawah irigasi.',
            'content_markdown' => '### Dosis Urea 100 kg/ha',
            'tags' => 'urea, pupuk',
        ]);

        $article = \App\Models\AgricultureKnowledge::where('slug', 'panduan-dosis-urea-sawah-test')->first();
        $this->assertNotNull($article);
        $createResponse->assertRedirect(route('admin.knowledge.show', $article->slug));

        // Update article
        $updateResponse = $this->actingAs($admin)->patch("/admin/knowledge/{$article->id}", [
            'category' => 'pemupukan',
            'title' => 'Panduan Dosis Urea Sawah Revisi',
            'summary' => 'Ringkasan dosis urea untuk sawah irigasi revisi.',
            'content_markdown' => '### Dosis Urea 150 kg/ha',
            'tags' => 'urea, pupuk, npk',
        ]);

        $article->refresh();
        $this->assertEquals('Panduan Dosis Urea Sawah Revisi', $article->title);
        $updateResponse->assertRedirect(route('admin.knowledge.show', $article->slug));

        // Delete article
        $deleteResponse = $this->actingAs($admin)->delete("/admin/knowledge/{$article->id}");
        $deleteResponse->assertRedirect(route('admin.knowledge.index'));
        $this->assertDatabaseMissing('agriculture_knowledges', ['id' => $article->id]);
    }
}
