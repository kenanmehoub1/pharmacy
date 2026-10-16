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
    Schema::create('medicines', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('scientific_name')->nullable();
        $table->string('barcode')->unique()->nullable();
        $table->string('category')->nullable();
        $table->decimal('price', 10, 2)->default(0);
        $table->decimal('cost', 10, 2)->default(0);
        $table->integer('stock')->default(0);
        $table->integer('min_stock')->default(10);
        $table->date('expiry_date')->nullable();
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
