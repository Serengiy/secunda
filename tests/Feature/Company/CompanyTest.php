<?php

namespace Tests\Feature\Company;

use App\Models\Activity;
use App\Models\Building;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Building $building;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = $this->getToken();

        // создаём здание и компанию
        $this->building = Building::factory()->create([
            'latitude' => 55.751244,
            'longitude' => 37.618423,
        ]);

        $this->company = Company::factory()->create([
            'building_id' => $this->building->id,
        ]);
    }

    #[Test] public function it_returns_paginated_list_of_companies(): void
    {
        Company::factory(5)->create();

        $response = $this
            ->withHeaders([
                'token' => $this->token,
            ])
            ->getJson('/api/companies');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'building']
                ],
                'links',
                'meta',
            ]);
    }

    #[Test] public function it_returns_companies_by_activity(): void
    {
        $activity = Activity::factory()->create();
        $child = Activity::factory()->create(['parent_id' => $activity->id]);

        $this->company->activities()->attach([$activity->id, $child->id]);

        $response = $this
            ->withHeaders([
                'token' => $this->token,
            ])
            ->getJson("/api/companies/activity/{$activity->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $this->company->id]);
    }

    #[Test] public function it_returns_companies_within_radius(): void
    {
        $response = $this
            ->withHeaders([
                'token' => $this->token,
            ])
            ->getJson('/api/companies/nearest?latitude=55.75&longitude=37.61&radius=2');

        $response->assertOk()
            ->assertJsonFragment(['id' => $this->company->id]);
    }

    #[Test] public function it_returns_a_single_company_with_relations(): void
    {
        $activity = Activity::factory()->create();
        $this->company->activities()->attach($activity);

        $response = $this
            ->withHeaders([
                'token' => $this->token,
            ])
            ->getJson("/api/companies/{$this->company->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'building' => ['id', 'address'],
                    'activities' => [
                        '*' => ['id', 'name']
                    ]
                ]
            ]);
    }

    public function getToken(): string
    {
        $path = base_path('auth_token.json');

        if (!file_exists($path)) {
            throw new \RuntimeException('RUN SETUP COMMAND');
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if($token = $data['token'] ?? null) {
            return $token;
        }

        throw new \RuntimeException('RUN SETUP COMMAND');
    }
}
