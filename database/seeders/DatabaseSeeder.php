<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Building;
use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создаём здания
        $buildings = Building::factory(10)->create();

        // Создаём виды деятельности (часть с родителями)
        $parentActivities = Activity::factory(5)->create();
        $childActivities = Activity::factory(10)
            ->state(fn () => ['parent_id' => $parentActivities->random()->id])
            ->create();

        $activities = $parentActivities->merge($childActivities);

        // Создаём компании, привязанные к зданиям
        $companies = Company::factory(20)
            ->state(fn () => ['building_id' => $buildings->random()->id])
            ->create();

        // Привязываем к компаниям случайные виды деятельности
        foreach ($companies as $company) {
            $company->activities()->attach(
                $activities->random(rand(1, 3))->pluck('id')->toArray()
            );
        }

        $this->command->info('Database seeded successfully!');
    }
}
