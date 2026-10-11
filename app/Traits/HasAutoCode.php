<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Tự sinh mã kiểu NV0001, PB0001, CC000001... khi tạo bản ghi mới.
 * Mỗi Model dùng trait này cần khai báo 2 thuộc tính tĩnh:
 *
 *   protected static string $codePrefix = 'NV';   // tiền tố
 *   protected static int $codeLength = 4;          // số chữ số, VD 4 -> 0001
 */
trait HasAutoCode
{
    protected static function bootHasAutoCode(): void
    {
        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = $model->generateNextCode();
            }
        });
    }

    public function generateNextCode(): string
    {
        $prefix = static::$codePrefix;
        $length = static::$codeLength ?? 4;
        $table = $this->getTable();

        // Khóa bảng trong 1 transaction để tránh 2 request cùng lúc sinh trùng mã
        // (VD: 2 nhân viên được thêm gần như đồng thời)
        return DB::transaction(function () use ($prefix, $length, $table) {
            $lastCode = DB::table($table)
                ->where('code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByRaw('LENGTH(code) desc, code desc')
                ->value('code');

            $nextNumber = 1;
            if ($lastCode) {
                $numericPart = (int) substr($lastCode, strlen($prefix));
                $nextNumber = $numericPart + 1;
            }

            return $prefix . str_pad((string) $nextNumber, $length, '0', STR_PAD_LEFT);
        });
    }
}
