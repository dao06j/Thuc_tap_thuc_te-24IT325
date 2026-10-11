<?php

namespace App\Models;

use App\Traits\HasAutoCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory, HasAutoCode;

    // Mã sinh tự động dạng: BL0001, BL0002... (Bảng Lương)
    protected static string $codePrefix = 'BL';
    protected static int $codeLength = 4;

    protected $fillable = [
        'code', 'employee_id', 'month', 'work_days',
        'paid_leave_days', 'unpaid_leave_days', 'salary_received',
    ];

    protected $casts = [
        'work_days' => 'decimal:1',
        'paid_leave_days' => 'decimal:1',
        'unpaid_leave_days' => 'decimal:1',
        'salary_received' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
