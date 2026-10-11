<?php

namespace App\Models;

use App\Traits\HasAutoCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory, HasAutoCode;

    // Mã sinh tự động dạng: NP0001, NP0002...
    protected static string $codePrefix = 'NP';
    protected static int $codeLength = 4;

    protected $fillable = [
        'code', 'employee_id', 'leave_type', 'from_date', 'to_date',
        'total_days', 'reason', 'status', 'approved_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'total_days' => 'decimal:1',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
