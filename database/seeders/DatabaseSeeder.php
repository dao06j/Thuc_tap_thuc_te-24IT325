<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed dữ liệu mẫu cho môi trường test/dev.
     * Bao gồm: vị trí, phòng ban, nhân viên, tài khoản đăng nhập.
     */
    public function run(): void
    {
        $positions = [
            ['name' => 'Thực tập sinh', 'min_salary' => 3000000, 'max_salary' => 5000000, 'status' => 'active'],
            ['name' => 'Nhân viên', 'min_salary' => 8000000, 'max_salary' => 15000000, 'status' => 'active'],
            ['name' => 'Trưởng phòng', 'min_salary' => 18000000, 'max_salary' => 28000000, 'status' => 'active'],
            ['name' => 'Giám đốc', 'min_salary' => 30000000, 'max_salary' => 60000000, 'status' => 'active'],
        ];

        $positionMap = [];
        foreach ($positions as $positionData) {
            $position = Position::create($positionData);
            $positionMap[$positionData['name']] = $position;
        }

        $departments = [
            ['name' => 'Phòng Nhân sự', 'status' => 'active'],
            ['name' => 'Phòng Kinh doanh', 'status' => 'active'],
            ['name' => 'Phòng Kỹ thuật', 'status' => 'active'],
            ['name' => 'Phòng Tài chính', 'status' => 'active'],
        ];

        $departmentMap = [];
        foreach ($departments as $departmentData) {
            $department = Department::create($departmentData);
            $departmentMap[$departmentData['name']] = $department;
        }

        $employees = [
            [
                'name' => 'Nguyễn Văn HR',
                'national_id' => '001234567890',
                'date_of_birth' => '1990-05-12',
                'phone' => '0901234567',
                'email' => 'hr@demo.vn',
                'address' => 'Đà Nẵng',
                'department_id' => $departmentMap['Phòng Nhân sự']->id,
                'position_id' => $positionMap['Trưởng phòng']->id,
                'hire_date' => '2022-01-01',
                'contract_type' => 'official',
                'base_salary' => 20000000,
                'status' => 'active',
            ],
            [
                'name' => 'Trần Thị Lan',
                'national_id' => '001234567891',
                'date_of_birth' => '1995-02-14',
                'phone' => '0907654321',
                'email' => 'lan@demo.vn',
                'address' => 'Huế',
                'department_id' => $departmentMap['Phòng Kinh doanh']->id,
                'position_id' => $positionMap['Nhân viên']->id,
                'hire_date' => '2023-03-10',
                'contract_type' => 'official',
                'base_salary' => 12000000,
                'status' => 'active',
            ],
            [
                'name' => 'Lê Văn Kiên',
                'national_id' => '001234567892',
                'date_of_birth' => '1998-09-20',
                'phone' => '0912345678',
                'email' => 'kien@demo.vn',
                'address' => 'Quảng Nam',
                'department_id' => $departmentMap['Phòng Kỹ thuật']->id,
                'position_id' => $positionMap['Nhân viên']->id,
                'hire_date' => '2024-04-15',
                'contract_type' => 'official',
                'base_salary' => 15000000,
                'status' => 'active',
            ],
            [
                'name' => 'Phạm Hoài Nam',
                'national_id' => '001234567893',
                'date_of_birth' => '1991-11-08',
                'phone' => '0923456789',
                'email' => 'nam@demo.vn',
                'address' => 'Đà Nẵng',
                'department_id' => $departmentMap['Phòng Tài chính']->id,
                'position_id' => $positionMap['Giám đốc']->id,
                'hire_date' => '2021-07-01',
                'contract_type' => 'official',
                'base_salary' => 30000000,
                'status' => 'active',
            ],
        ];

        $createdEmployees = [];
        foreach ($employees as $employeeData) {
            $employee = Employee::create($employeeData);
            $createdEmployees[] = $employee;
        }

        $departmentMap['Phòng Nhân sự']->update(['manager_id' => $createdEmployees[0]->id]);
        $departmentMap['Phòng Kinh doanh']->update(['manager_id' => $createdEmployees[1]->id]);
        $departmentMap['Phòng Kỹ thuật']->update(['manager_id' => $createdEmployees[2]->id]);
        $departmentMap['Phòng Tài chính']->update(['manager_id' => $createdEmployees[3]->id]);

        User::create([
            'username' => 'hr01',
            'password' => 'password',
            'employee_id' => $createdEmployees[0]->id,
            'role' => 'hr',
            'status' => 'active',
        ]);

        User::create([
            'username' => 'staff01',
            'password' => 'password',
            'employee_id' => $createdEmployees[1]->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        User::create([
            'username' => 'staff02',
            'password' => 'password',
            'employee_id' => $createdEmployees[2]->id,
            'role' => 'employee',
            'status' => 'active',
        ]);

        if (Schema::hasTable('attendances')) {
            Attendance::firstOrCreate([
                'employee_id' => $createdEmployees[1]->id,
                'work_date' => now()->toDateString(),
            ], [
                'check_in' => now()->setTime(8, 15, 0),
                'check_out' => now()->setTime(17, 30, 0),
                'note' => 'Đi trễ: 08:15',
                'proof_photo_path' => 'attendance/demo-checkin.jpg',
                'checkout_photo_path' => 'attendance/demo-checkout.jpg',
                'ip_address' => '127.0.0.1',
            ]);
        }
    }
}
