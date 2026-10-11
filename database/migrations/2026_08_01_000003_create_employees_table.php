<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->comment('Mã nhân viên, VD: NV0248');
            $table->string('name', 150)->comment('Họ và tên');
            $table->string('national_id', 20)->unique()->comment('Số CCCD');
            $table->date('date_of_birth')->comment('Ngày sinh');
            $table->string('phone', 15)->comment('Số điện thoại');
            $table->string('email', 150)->unique()->comment('Email');
            $table->string('address', 255)->nullable()->comment('Địa chỉ');
            $table->string('photo_path')->nullable()->comment('Đường dẫn ảnh thẻ nhân viên');

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete()
                ->comment('Phòng ban (FK -> departments.id)');

            $table->foreignId('position_id')
                ->nullable()
                ->constrained('positions')
                ->nullOnDelete()
                ->comment('Chức vụ (FK -> positions.id)');

            $table->date('hire_date')->comment('Ngày vào công ty');
            $table->enum('contract_type', ['probation', 'official', 'seasonal'])
                ->default('probation')
                ->comment('Loại hợp đồng: thử việc / chính thức / thời vụ');
            $table->decimal('base_salary', 15, 2)->comment('Lương cơ bản');
            $table->enum('status', ['active', 'resigned', 'suspended'])
                ->default('active')
                ->comment('Trạng thái: đang làm việc / đã nghỉ việc / tạm ngưng');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
