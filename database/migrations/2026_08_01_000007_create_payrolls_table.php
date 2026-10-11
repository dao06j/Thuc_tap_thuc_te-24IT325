<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Mã bảng lương');

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete()
                ->comment('Nhân viên (FK -> employees.id)');

            $table->char('month', 7)->comment('Tháng lương, định dạng YYYY-MM, VD: 2026-08');
            $table->decimal('work_days', 4, 1)->default(0)->comment('Số ngày chấm công thực tế');
            $table->decimal('paid_leave_days', 4, 1)->default(0)->comment('Số ngày nghỉ có lương');
            $table->decimal('unpaid_leave_days', 4, 1)->default(0)->comment('Số ngày nghỉ không phép');
            $table->decimal('salary_received', 15, 2)->comment('Lương thực nhận');
            $table->timestamps();

            // Mỗi nhân viên chỉ có 1 bảng lương cho 1 tháng
            $table->unique(['employee_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
