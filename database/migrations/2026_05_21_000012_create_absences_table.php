<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('session_de_cours_id')->constrained('session_de_cours')->cascadeOnDelete();
            $table->decimal('nbre_heure', 4, 2);
            $table->date('date');
            // Justification
            $table->date('justification_date')->nullable();
            $table->text('justification_motif')->nullable();
            $table->enum('statut_justification', ['non_justifiee', 'en_attente', 'acceptee', 'refusee'])->default('non_justifiee');
            $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('traite_at')->nullable();
            $table->unique(['etudiant_id', 'session_de_cours_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
