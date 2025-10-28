<?php

use App\Models\Activity;
use App\Models\Building;
use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->unsignedBigInteger('building_id')->nullable()->index();
            $table->timestamps();


            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('set null');
        });

        Schema::create('activity_company', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->timestamps();

            $table->primary(['activity_id', 'company_id']);

            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
        Schema::dropIfExists('activity_company');
    }
};
