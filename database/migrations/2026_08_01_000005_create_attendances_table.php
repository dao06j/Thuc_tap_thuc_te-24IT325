<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Mã chấm công');

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete()
                ->comment('Nhân viên chấm công (FK -> employees.id)');

            $table->date('work_date')->comment('Ngày làm việc');
            $table->dateTime('check_in')->nullable()->comment('Thời điểm check-in');
            $table->dateTime('check_out')->nullable()->comment('Thời điểm check-out');
            $table->string('proof_photo_path')->nullable()->comment('Ảnh minh chứng chấm công');
            $table->string('ip_address', 45)->nullable()->comment('Địa chỉ IP lúc chấm công');
            $table->string('note', 255)->nullable()->comment('Ghi chú (VD: đi trễ, quên check-out...)');
            $table->timestamps();

            // Mỗi nhân viên chỉ có 1 bản ghi chấm công cho 1 ngày
            $table->unique(['employee_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
