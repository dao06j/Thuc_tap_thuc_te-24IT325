<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_can_store_checkout_photo_and_penalty_note_format(): void
    {
        $department = Department::create([
            'name' => 'Kinh doanh',
            'status' => 'active',
        ]);

        $position = Position::create([
            'name' => 'Nhân viên',
            'min_salary' => 7000000,
            'max_salary' => 15000000,
            'status' => 'active',
        ]);

        $employee = Employee::create([
            'code' => 'NV0001',
            'name' => 'Nguyễn Văn A',
            'national_id' => '123456789',
            'date_of_birth' => '1998-01-01',
            'phone' => '0900000001',
            'email' => 'a@example.com',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'hire_date' => '2024-01-01',
            'contract_type' => 'official',
            'base_salary' => 12000000,
            'status' => 'active',
        ]);

        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => '2026-08-15',
            'check_in' => '2026-08-15 08:15:00',
            'check_out' => '2026-08-15 16:45:00',
            'note' => 'Đi trễ: 08:15; Về sớm: 16:45',
            'proof_photo_path' => 'attendance/checkin.jpg',
            'checkout_photo_path' => 'attendance/checkout.jpg',
        ]);

        $attendance = Attendance::first();

        $this->assertNotNull($attendance);
        $this->assertSame('attendance/checkout.jpg', $attendance->checkout_photo_path);
        $this->assertStringContainsString('Đi trễ', $attendance->note);
        $this->assertStringContainsString('Về sớm', $attendance->note);
    }
}
