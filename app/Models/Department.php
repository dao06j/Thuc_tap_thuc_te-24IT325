<?php

namespace App\Models;

use App\Traits\HasAutoCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory, HasAutoCode;

    // Mã sinh tự động dạng: PB0001, PB0002...
    protected static string $codePrefix = 'PB';
    protected static int $codeLength = 4;

    protected $fillable = [
        'code', 'name', 'manager_id', 'established_date', 'status',
    ];

    protected $casts = [
        'established_date' => 'date',
    ];

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
