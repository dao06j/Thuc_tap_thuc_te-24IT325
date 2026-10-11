<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_create_and_update_a_monthly_payroll(): void
    {
        $employee = $this->createEmployee();
        $hr = $this->createUser('hr', $this->createEmployee(), 'hr');
        $payload = [
            'employee_id' => $employee->id,
            'month' => '2026-10',
            'work_days' => 22,
            'paid_leave_days' => 1,
            'unpaid_leave_days' => 0,
            'salary_received' => 15000000,
        ];

        $this->actingAs($hr)
            ->post(route('admin.payrolls.store'), $payload)
            ->assertRedirect(route('admin.payrolls', ['month' => '2026-10']))
            ->assertSessionHas('success');

        $payroll = Payroll::where('employee_id', $employee->id)->where('month', '2026-10')->firstOrFail();
        $this->assertSame(1, Payroll::where('employee_id', $employee->id)->where('month', '2026-10')->count());

        $payload['work_days'] = 20;
        $payload['salary_received'] = 14000000;
        $this->actingAs($hr)->post(route('admin.payrolls.store'), $payload)->assertSessionHas('success');

        $payroll->refresh();
        $this->assertSame('20.0', $payroll->work_days);
        $this->assertSame('14000000.00', $payroll->salary_received);
        $this->assertSame(1, Payroll::where('employee_id', $employee->id)->where('month', '2026-10')->count());
    }

    public function test_employee_can_only_view_their_own_payroll(): void
    {
        $employee = $this->createEmployee();
        $otherEmployee = $this->createEmployee();
        $user = $this->createUser('staff', $employee, 'employee');
        $ownPayroll = $this->createPayroll($employee);
        $otherPayroll = $this->createPayroll($otherEmployee);

        $this->actingAs($user)
            ->get(route('payroll.index'))
            ->assertOk()
            ->assertSee($ownPayroll->code)
            ->assertDontSee($otherPayroll->code);
    }

    public function test_employee_cannot_manage_payrolls(): void
    {
        $employee = $this->createEmployee();
        $user = $this->createUser('staff', $employee, 'employee');

        $this->actingAs($user)->get(route('admin.payrolls'))->assertForbidden();
        $this->post(route('admin.payrolls.store'), [
            'employee_id' => $employee->id,
            'month' => '2026-10',
            'work_days' => 22,
            'paid_leave_days' => 0,
            'unpaid_leave_days' => 0,
            'salary_received' => 10000000,
        ])->assertForbidden();
    }

    private function createEmployee(): Employee
    {
        static $sequence = 0;
        $sequence++;

        return Employee::create([
            'name' => "Nhân viên {$sequence}",
            'national_id' => sprintf('%09d', $sequence),
            'date_of_birth' => '1995-01-01',
            'phone' => sprintf('090%07d', $sequence),
            'email' => "employee{$sequence}@example.com",
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

    private function createPayroll(Employee $employee): Payroll
    {
        return Payroll::create([
            'employee_id' => $employee->id,
            'month' => '2026-10',
            'work_days' => 22,
            'paid_leave_days' => 0,
            'unpaid_leave_days' => 0,
            'salary_received' => 15000000,
        ]);
    }
}
