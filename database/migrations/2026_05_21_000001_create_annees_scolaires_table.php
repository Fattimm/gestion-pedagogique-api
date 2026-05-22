<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annees_scolaires', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('etat', ['planifiee', 'en_cours', 'terminee'])->default('planifiee');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annees_scolaires');
    }
};
