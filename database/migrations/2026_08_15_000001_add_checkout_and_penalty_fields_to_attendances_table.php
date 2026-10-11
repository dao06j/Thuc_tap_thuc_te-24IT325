<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('checkout_photo_path')->nullable()->after('proof_photo_path')->comment('Ảnh minh chứng lúc check-out');
            $table->unsignedInteger('late_minutes')->nullable()->after('note')->comment('Số phút đi trễ so với giờ chuẩn (dùng để trừ lương)');
            $table->unsignedInteger('early_leave_minutes')->nullable()->after('late_minutes')->comment('Số phút về sớm so với giờ chuẩn (dùng để trừ lương)');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['checkout_photo_path', 'late_minutes', 'early_leave_minutes']);
        });
    }
};
