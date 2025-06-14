<?php

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
        Schema::create('to_dos', function (Blueprint $table) {
            $table->id();
            $table->text('foto_tugas')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('judul_tugas');
            $table->text('deskripsi_tugas');
            $table->date('tanggal_selesai')->nullable();
            $table->dateTime('tanggal_diselesaikan')->nullable();
            $table->enum('status', ['belum_dikerjakan', 'terlambat', 'selesai'])->default('belum_dikerjakan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('to_dos');
    }
};
