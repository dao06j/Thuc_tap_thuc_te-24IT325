<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Đặt SAU employees vì users.employee_id tham chiếu tới employees.id
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique()->comment('Tên đăng nhập');
            $table->string('password')->comment('Mật khẩu đã băm (hash), KHÔNG lưu dạng thường');

            $table->enum('role', ['hr', 'employee'])
                ->default('employee')
                ->comment('Vai trò: hr (quản trị) hoặc employee (nhân viên thường)');

            $table->string('status')
                ->default('active')
                ->comment('Trạng thái tài khoản: active (hoạt động), locked (bị khóa)');

            $table->foreignId('employee_id')
                ->nullable()
                ->unique()
                ->constrained('employees')
                ->cascadeOnDelete()
                ->comment('Nhân viên sở hữu tài khoản này (FK -> employees.id, 1-1)');

            $table->timestamp('last_login_at')->nullable()->comment('Lần đăng nhập gần nhất');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
