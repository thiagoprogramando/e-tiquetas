<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void {
        Schema::create('label_layouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('paper_format');
            $table->decimal('paper_width_mm', 8, 3)->default(0);
            $table->decimal('paper_height_mm', 8, 3)->default(0);
            $table->decimal('label_width_mm', 8, 3)->default(0);
            $table->decimal('label_height_mm', 8, 3)->default(0);
            $table->integer('rows');
            $table->integer('columns');
            $table->decimal('margin_top_mm', 8, 3)->nullable();
            $table->decimal('margin_left_mm', 8, 3)->nullable();
            $table->decimal('gap_x_mm', 8, 3)->nullable();
            $table->decimal('gap_y_mm', 8, 3)->nullable();
            $table->json('canvas_json')->nullable();
            $table->enum('status', ['draft', 'designed', 'active'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('label_layouts');
    }
};
