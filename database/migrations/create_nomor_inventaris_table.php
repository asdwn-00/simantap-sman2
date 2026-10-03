<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_inventaris', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedBigInteger('nomor_terakhir')->default(0);
        });

        DB::table('nomor_inventaris')->insert(['id' => 1, 'nomor_terakhir' => 0]);
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_inventaris');
    }
};
