<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Mã đơn nghỉ phép');

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete()
                ->comment('Nhân viên xin nghỉ (FK -> employees.id)');

            $table->enum('leave_type', ['annual', 'sick', 'unpaid', 'wfh'])
                ->comment('Loại nghỉ: phép năm / ốm / không lương / làm từ xa');

            $table->date('from_date')->comment('Ngày nghỉ (bắt đầu)');
            $table->date('to_date')->comment('Tới ngày (kết thúc)');
            $table->decimal('total_days', 4, 1)->comment('Số ngày nghỉ');
            $table->string('reason', 255)->nullable()->comment('Lý do xin nghỉ');

            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->comment('Trạng thái đơn: chờ duyệt / đã duyệt / từ chối');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete()
                ->comment('Nhân viên (Admin/HR) đã duyệt đơn (FK -> employees.id)');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
