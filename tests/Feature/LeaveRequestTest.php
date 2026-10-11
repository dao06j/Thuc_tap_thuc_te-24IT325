<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_leave_request_and_see_its_status(): void
    {
        $employee = $this->createEmployee('staff');
        $user = $this->createUser('staff', $employee, 'employee');

        $response = $this->actingAs($user)->post(route('leave.store'), [
            'leave_type' => 'annual',
            'from_date' => '2026-10-12',
            'to_date' => '2026-10-14',
            'reason' => 'Giải quyết việc gia đình',
        ]);

        $response->assertRedirect('/leave')
            ->assertSessionHas('success');

        $leaveRequest = LeaveRequest::firstOrFail();
        $this->assertSame($employee->id, $leaveRequest->employee_id);
        $this->assertSame('pending', $leaveRequest->status);
        $this->assertEquals(3, $leaveRequest->total_days);

        $this->get(route('leave.index'))
            ->assertOk()
            ->assertSee('Giải quyết việc gia đình')
            ->assertSee('Chờ duyệt');
    }

    public function test_leave_request_rejects_end_date_before_start_date(): void
    {
        $employee = $this->createEmployee('staff');
        $user = $this->createUser('staff', $employee, 'employee');

        $this->actingAs($user)->from(route('leave.index'))->post(route('leave.store'), [
            'leave_type' => 'sick',
            'from_date' => '2026-10-14',
            'to_date' => '2026-10-12',
            'reason' => 'Khám bệnh',
        ])->assertRedirect(route('leave.index'))
            ->assertSessionHasErrors('to_date');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_hr_can_approve_another_employees_request(): void
    {
        $hrEmployee = $this->createEmployee('hr');
        $hrUser = $this->createUser('hr', $hrEmployee, 'hr');
        $staffEmployee = $this->createEmployee('staff');
        $leaveRequest = $this->createLeaveRequest($staffEmployee);

        $this->actingAs($hrUser)
            ->post(route('admin.leave-approvals.update', [$leaveRequest, 'approve']))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('approved', $leaveRequest->fresh()->status);
        $this->assertSame($hrEmployee->id, $leaveRequest->fresh()->approved_by);
    }

    public function test_hr_cannot_approve_or_reject_their_own_request(): void
    {
        $hrEmployee = $this->createEmployee('hr');
        $hrUser = $this->createUser('hr', $hrEmployee, 'hr');
        $leaveRequest = $this->createLeaveRequest($hrEmployee);

        foreach (['approve', 'reject'] as $decision) {
            $this->actingAs($hrUser)
                ->post(route('admin.leave-approvals.update', [$leaveRequest, $decision]))
                ->assertRedirect()
                ->assertSessionHas('error');
        }

        $this->assertSame('pending', $leaveRequest->fresh()->status);
        $this->assertNull($leaveRequest->fresh()->approved_by);
    }

    public function test_employee_cannot_access_hr_leave_approval_page_or_endpoint(): void
    {
        $staffEmployee = $this->createEmployee('staff');
        $staffUser = $this->createUser('staff', $staffEmployee, 'employee');
        $otherEmployee = $this->createEmployee('other');
        $leaveRequest = $this->createLeaveRequest($otherEmployee);

        $this->actingAs($staffUser)
            ->get(route('admin.leave-approvals'))
            ->assertForbidden();

        $this->post(route('admin.leave-approvals.update', [$leaveRequest, 'approve']))
            ->assertForbidden();
    }

    private function createEmployee(string $suffix): Employee
    {
        static $sequence = 0;
        $sequence++;

        return Employee::create([
            'name' => "Nhân viên {$suffix}",
            'national_id' => sprintf('%09d', $sequence),
            'date_of_birth' => '1995-01-01',
            'phone' => sprintf('090%07d', $sequence),
            'email' => "{$suffix}{$sequence}@example.com",
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

    private function createLeaveRequest(Employee $employee): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'from_date' => '2026-10-12',
            'to_date' => '2026-10-12',
            'total_days' => 1,
            'reason' => 'Việc cá nhân',
            'status' => 'pending',
        ]);
    }
}
