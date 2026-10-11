<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'username', 'password', 'role', 'status', 'employee_id',
    ];

    // Không bao giờ trả password/remember_token ra ngoài (VD: khi trả JSON qua API)
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed', // Laravel tự băm (bcrypt) khi gán giá trị, không cần Hash::make() thủ công
        'last_login_at' => 'datetime',
        'status' => 'string',
    ];

    // Đăng nhập bằng "username" chỉ cần Auth::attempt(['username'=>..,'password'=>..]) là đủ —
    // KHÔNG override getAuthIdentifierName(), vì đó là cột khóa chính (id) dùng để lưu vào session,
    // không phải cột đăng nhập. Override sai khiến Auth::id() trả về username thay vì id thật.

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function isHr(): bool
    {
        return $this->role === 'hr';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }
}