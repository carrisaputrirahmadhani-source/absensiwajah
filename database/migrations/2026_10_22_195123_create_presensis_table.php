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
    Schema::create('presensis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
        $table->date('tanggal');
        $table->dateTime('waktu_absen')->nullable();
        $table->enum('status', ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa']);
        $table->string('keterangan', 255)->nullable();
        $table->float('confidence_score')->nullable();
        $table->string('foto_snapshot', 255)->nullable();
        $table->string('perangkat', 50)->nullable();
        $table->timestamps();

        $table->unique(['siswa_id', 'tanggal']);   // 1 siswa 1 baris per hari
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
