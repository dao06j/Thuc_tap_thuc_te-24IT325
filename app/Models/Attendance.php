<?php

namespace App\Models;

use App\Traits\HasAutoCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, HasAutoCode;

    // Mã sinh tự động dạng: CC000001, CC000002...
    // (dùng 6 chữ số vì bảng này phát sinh dữ liệu mỗi ngày, mỗi nhân viên)
    protected static string $codePrefix = 'CC';
    protected static int $codeLength = 6;

    protected $fillable = [
        'code', 'employee_id', 'work_date', 'check_in', 'check_out',
        'proof_photo_path', 'checkout_photo_path', 'ip_address', 'note',
    ];

    protected $casts = [
        'work_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getLateMinutesAttribute(): int
    {
        if (! $this->check_in || empty($this->note)) {
            return 0;
        }

        preg_match('/Đi trễ:\s*(\d{1,2}:\d{2})/i', $this->note, $matches);

        if (! isset($matches[1])) {
            return 0;
        }

        return $this->minutesFromTimeString($matches[1]);
    }

    public function getEarlyMinutesAttribute(): int
    {
        if (! $this->check_out || empty($this->note)) {
            return 0;
        }

        preg_match('/Về sớm:\s*(\d{1,2}:\d{2})/i', $this->note, $matches);

        if (! isset($matches[1])) {
            return 0;
        }

        return $this->minutesFromTimeString($matches[1]);
    }

    protected function minutesFromTimeString(string $time): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return ($hour * 60) + $minute;
    }
}
