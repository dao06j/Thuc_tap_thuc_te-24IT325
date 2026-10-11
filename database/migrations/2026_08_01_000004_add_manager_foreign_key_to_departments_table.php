<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Bảng employees đã tồn tại (migration trước) nên giờ mới thêm được
    // ràng buộc khóa ngoại thật cho manager_id trong departments.
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('manager_id')
                ->references('id')->on('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });
    }
};
