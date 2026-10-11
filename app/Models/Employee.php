<?php

namespace App\Models;

use App\Traits\HasAutoCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory, HasAutoCode;

    // Mã sinh tự động dạng: NV0001, NV0002...
    protected static string $codePrefix = 'NV';
    protected static int $codeLength = 4;

    protected $fillable = [
        'code', 'name', 'national_id', 'date_of_birth', 'phone', 'email',
        'address', 'photo_path', 'department_id', 'position_id',
        'hire_date', 'contract_type', 'base_salary', 'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'base_salary' => 'decimal:2',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'employee_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    // Các đơn nghỉ phép mà nhân viên này (với vai trò Admin/HR) đã duyệt
    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }
}
