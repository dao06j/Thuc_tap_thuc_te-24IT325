<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_view_monthly_charts_and_department_summary(): void
    {
        $department = Department::create([
            'name' => 'Phòng Kỹ thuật',
            'status' => 'active',
        ]);
        $employee = $this->createEmployee($department, 'staff');
        $hr = $this->createUser('hr', $this->createEmployee($department, 'hr'), 'hr');

        $attendance = new Attendance([
            'employee_id' => $employee->id,
            'work_date' => '2026-10-05',
            'check_in' => '2026-10-05 08:15:00',
        ]);
        $attendance->late_minutes = 15;
        $attendance->save();

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'from_date' => '2026-10-12',
            'to_date' => '2026-10-13',
            'total_days' => 2,
            'reason' => 'Nghỉ phép',
            'status' => 'approved',
        ]);

        Payroll::create([
            'employee_id' => $employee->id,
            'month' => '2026-10',
            'work_days' => 1,
            'paid_leave_days' => 2,
            'unpaid_leave_days' => 0,
            'salary_received' => 12000000,
        ]);

        $this->actingAs($hr)
            ->get(route('admin.reports', ['month' => '2026-10']))
            ->assertOk()
            ->assertViewHas('activeEmployeeCount', 2)
            ->assertViewHas('employeeChart', function (array $chart) use ($department) {
                return $chart['labels'] === ['Phòng Kỹ thuật'] && $chart['data'] === [2];
            })
            ->assertViewHas('attendanceChart', function (array $chart) {
                return count($chart['labels']) === 31
                    && $chart['late'][4] === 1
                    && $chart['onTime'][4] === 0;
            })
            ->assertViewHas('departmentStats', function ($stats) use ($department) {
                $departmentStats = $stats->firstWhere('id', $department->id);

                return $departmentStats['employees'] === 2
                    && $departmentStats['attendance'] === 1
                    && $departmentStats['late'] === 1
                    && $departmentStats['leave_days'] === 2.0
                    && $departmentStats['payroll'] === 12000000.0;
            });
    }

    public function test_non_hr_cannot_view_company_reports(): void
    {
        $employee = $this->createEmployee(null, 'staff');
        $user = $this->createUser('staff', $employee, 'employee');

        $this->actingAs($user)->get(route('admin.reports'))->assertForbidden();
    }

    public function test_report_month_must_be_valid(): void
    {
        $employee = $this->createEmployee(null, 'hr');
        $hr = $this->createUser('hr', $employee, 'hr');

        $this->actingAs($hr)
            ->get(route('admin.reports', ['month' => 'October 2026']))
            ->assertSessionHasErrors('month');
    }

    private function createEmployee(?Department $department, string $suffix): Employee
    {
        static $sequence = 0;
        $sequence++;

        return Employee::create([
            'name' => "Nhân viên {$suffix} {$sequence}",
            'national_id' => sprintf('%09d', $sequence),
            'date_of_birth' => '1995-01-01',
            'phone' => sprintf('090%07d', $sequence),
            'email' => "{$suffix}{$sequence}@example.com",
            'department_id' => $department?->id,
            'hire_date' => '2024-01-01',
            'contract_type' => 'official',
            'base_salary' => 12000000,
            'status' => 'active',
        ]);
    }

    private function createUser(string $username, Employee $employee, string $role): User
    {
        return User::create([
            'username' => $username,
            'password' => 'password',
            'employee_id' => $employee->id,
            'role' => $role,
            'status' => 'active',
        ]);
    }
}
