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
    Schema::create('siswas', function (Blueprint $table) {
        $table->id();
        $table->string('nis', 10)->unique();
        $table->string('nama_siswa', 100);
        $table->string('kelas', 20);
        $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
        $table->string('foto', 255)->nullable();
        $table->text('face_descriptor')->nullable();   // JSON 128 angka
        $table->float('akurasi_vektor')->nullable();
        $table->foreignId('guru_pendaftar_id')->nullable()->constrained('users')->nullOnDelete();
        $table->dateTime('waktu_daftar_wajah')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
