<?php

namespace App\Models;

use App\Traits\HasAutoCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory, HasAutoCode;

    // Mã sinh tự động dạng: CV0001, CV0002...
    protected static string $codePrefix = 'CV';
    protected static int $codeLength = 4;

    protected $fillable = [
        'code', 'name', 'min_salary', 'max_salary', 'status',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }
}
