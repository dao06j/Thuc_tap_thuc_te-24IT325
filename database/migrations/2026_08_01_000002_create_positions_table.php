<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Mã chức vụ, VD: CV001');
            $table->string('name', 150)->comment('Tên chức vụ');
            $table->decimal('min_salary', 15, 2)->comment('Lương tối thiểu của chức vụ');
            $table->decimal('max_salary', 15, 2)->comment('Lương tối đa của chức vụ');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Trạng thái chức vụ');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
