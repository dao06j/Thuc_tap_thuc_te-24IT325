<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Mã phòng ban, VD: PB001');
            $table->string('name', 150)->comment('Tên phòng ban');

            // Trưởng phòng — trỏ tới bảng employees, nhưng employees CHƯA tồn tại lúc này
            // (2 bảng tham chiếu chéo nhau) nên chỉ tạo cột thường ở đây,
            // ràng buộc khóa ngoại thật sẽ thêm ở migration
            // 2026_08_01_000004_add_manager_foreign_key_to_departments_table
            $table->unsignedBigInteger('manager_id')->nullable()
                ->comment('Nhân viên trưởng phòng ban (FK -> employees.id)');

            $table->date('established_date')->nullable()->comment('Ngày thành lập');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Trạng thái phòng ban');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
