<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('proof_photo_path')->comment('Vĩ độ lúc check-in');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude')->comment('Kinh độ lúc check-in');
            $table->unsignedInteger('distance_meters')->nullable()->after('longitude')->comment('Khoảng cách tới văn phòng lúc check-in (mét)');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'distance_meters']);
        });
    }
};
