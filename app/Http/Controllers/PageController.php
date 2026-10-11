<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    // TODO: đổi thành tọa độ THẬT của văn phòng công ty (lấy từ Google Maps: chuột phải vào vị trí -> xem tọa độ)
    private const OFFICE_LATITUDE = 16.0544;
    private const OFFICE_LONGITUDE = 108.2022;
    private const OFFICE_RADIUS_METERS = 300; // chỉ để tham khảo hiển thị, KHÔNG chặn check-in nếu vượt quá

    private const STANDARD_CHECKIN_TIME = '08:00:00';
    private const STANDARD_CHECKOUT_TIME = '17:30:00';


    /**
     * Dữ liệu người dùng đang đăng nhập, dùng chung cho mọi trang
     * (initial hiển thị trên avatar góc phải + tên nhân viên).
     */
    private function viewerData(): array
    {
        $user = Auth::user();
        $employee = $user?->employee;
        $name = $employee?->name ?? $user?->username ?? 'Người dùng';

        return [
            'userInitial' => mb_substr($name, 0, 1),
            'role' => $user?->role,
            'employee' => ['name' => $name],
            'pendingLeaveCount' => LeaveRequest::where('status', 'pending')->count(),
        ];
    }

    public function dashboard()
    {
        $employee = Auth::user()->employee;
        $today = $this->todayAttendance($employee);

        $monthAttendanceCount = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->whereYear('work_date', now()->year)
                ->whereMonth('work_date', now()->month)
                ->whereNotNull('check_in')
                ->count()
            : 0;

        $recentAttendances = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->orderByDesc('work_date')
                ->limit(3)
                ->get()
            : collect();

        return view('dashboard', array_merge($this->viewerData(), [
            'todayAttendance' => $today,
            'monthAttendanceCount' => $monthAttendanceCount,
            'recentAttendances' => $recentAttendances,
            'officeLat' => self::OFFICE_LATITUDE,
            'officeLng' => self::OFFICE_LONGITUDE,
        ]));
    }

    public function attendanceHistory()
    {
        $employee = Auth::user()->employee;

        $records = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->orderByDesc('work_date')
                ->limit(60)
                ->get()
            : collect();

        return view('attendance.history', array_merge($this->viewerData(), [
            'records' => $records,
        ]));
    }

    public function payrollIndex()
    {
        $employee = Auth::user()->employee;
        $payrolls = $employee
            ? Payroll::where('employee_id', $employee->id)
                ->orderByDesc('month')
                ->paginate(12)
            : collect();

        return view('payroll.index', array_merge($this->viewerData(), [
            'payrolls' => $payrolls,
            'hasEmployeeProfile' => $employee !== null,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | CHẤM CÔNG (check-in / check-out) — nhân viên tự thao tác
    |--------------------------------------------------------------------------
    */

    private function todayAttendance(?Employee $employee): ?Attendance
    {
        if (! $employee) {
            return null;
        }

        return Attendance::where('employee_id', $employee->id)
            ->whereDate('work_date', now()->toDateString())
            ->first();
    }

    public function checkIn(Request $request)
    {
        $employee = Auth::user()->employee;

        if (! $employee) {
            return response()->json(['message' => 'Tài khoản của bạn chưa được gắn với hồ sơ nhân viên nào.'], 422);
        }

        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:3072'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'photo.required' => 'Cần chụp ảnh để chấm công.',
            'photo.image' => 'File chấm công phải là hình ảnh.',
            'photo.max' => 'Ảnh không được vượt quá 3MB.',
            'latitude.required' => 'Không lấy được vị trí — vui lòng cấp quyền định vị cho trình duyệt.',
            'longitude.required' => 'Không lấy được vị trí — vui lòng cấp quyền định vị cho trình duyệt.',
        ]);

        $attendance = $this->todayAttendance($employee);

        if ($attendance && $attendance->check_in) {
            return response()->json(['message' => 'Bạn đã check-in hôm nay rồi lúc ' . $attendance->check_in->format('H:i:s') . '.'], 422);
        }

        $photoPath = $this->storeAttendancePhoto($request->file('photo'));
        if (! $photoPath) {
            return response()->json(['message' => 'Không lưu được ảnh chấm công, vui lòng thử lại.'], 500);
        }

        $distance = $this->distanceInMeters(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            self::OFFICE_LATITUDE,
            self::OFFICE_LONGITUDE
        );

        $checkInTime = now();
        $standardStart = $checkInTime->copy()->setTimeFromTimeString(self::STANDARD_CHECKIN_TIME);
        $lateMinutes = $checkInTime->gt($standardStart) ? abs($checkInTime->diffInMinutes($standardStart)) : 0;

        $attendance = $attendance ?? new Attendance([
            'employee_id' => $employee->id,
            'work_date' => $checkInTime->toDateString(),
        ]);

        $attendance->check_in = $checkInTime;
        $attendance->proof_photo_path = $photoPath;
        $attendance->ip_address = $request->ip();
        $attendance->latitude = $validated['latitude'];
        $attendance->longitude = $validated['longitude'];
        $attendance->distance_meters = round($distance);
        $attendance->late_minutes = $lateMinutes > 0 ? $lateMinutes : null;
        $attendance->note = $this->buildAttendanceNote($lateMinutes, 0);
        $attendance->save();

        return response()->json([
            'message' => 'Check-in thành công.',
            'check_in' => $checkInTime->format('H:i:s'),
            'distance' => round($distance),
            'late' => $lateMinutes > 0,
            'note' => $attendance->note,
        ]);
    }

    public function checkOut(Request $request)
    {
        $employee = Auth::user()->employee;

        if (! $employee) {
            return response()->json(['message' => 'Tài khoản của bạn chưa được gắn với hồ sơ nhân viên nào.'], 422);
        }

        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:3072'],
        ], [
            'photo.required' => 'Cần chụp ảnh để check-out.',
            'photo.image' => 'File chấm công phải là hình ảnh.',
            'photo.max' => 'Ảnh không được vượt quá 3MB.',
        ]);

        $attendance = $this->todayAttendance($employee);

        if (! $attendance || ! $attendance->check_in) {
            return response()->json(['message' => 'Bạn cần check-in trước khi check-out.'], 422);
        }

        if ($attendance->check_out) {
            return response()->json(['message' => 'Bạn đã check-out hôm nay rồi lúc ' . $attendance->check_out->format('H:i:s') . '.'], 422);
        }

        $photoPath = $this->storeAttendancePhoto($request->file('photo'));
        if (! $photoPath) {
            return response()->json(['message' => 'Không lưu được ảnh check-out, vui lòng thử lại.'], 500);
        }

        $checkOutTime = now();
        $standardEnd = $checkOutTime->copy()->setTimeFromTimeString(self::STANDARD_CHECKOUT_TIME);
        $earlyMinutes = $checkOutTime->lt($standardEnd) ? abs($standardEnd->diffInMinutes($checkOutTime)) : 0;

        $attendance->check_out = $checkOutTime;
        $attendance->checkout_photo_path = $photoPath;
        $attendance->early_leave_minutes = $earlyMinutes > 0 ? $earlyMinutes : null;
        $attendance->note = $this->buildAttendanceNote($attendance->late_minutes ?? 0, $earlyMinutes);
        $attendance->save();

        return response()->json([
            'message' => 'Check-out thành công.',
            'check_out' => $checkOutTime->format('H:i:s'),
            'note' => $attendance->note,
        ]);
    }

    /**
     * Ghi chú chấm công dạng chuẩn hóa "Đi trễ X phút; Về sớm Y phút" —
     * số phút được lưu RIÊNG ở late_minutes/early_leave_minutes để sau này
     * module Lương tính trừ tiền theo phút (VD: mỗi phút trễ trừ X đồng),
     * note chỉ là bản tóm tắt hiển thị cho người đọc, không dùng để tính toán.
     */
    private function buildAttendanceNote(int $lateMinutes, int $earlyMinutes): ?string
    {
        $parts = [];

        if ($lateMinutes > 0) {
            $parts[] = "Đi trễ {$lateMinutes} phút";
        }
        if ($earlyMinutes > 0) {
            $parts[] = "Về sớm {$earlyMinutes} phút";
        }

        return $parts ? implode('; ', $parts) : null;
    }

    private function distanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function storeAttendancePhoto($photoFile)
    {
        if (! $photoFile) {
            return null;
        }

        try {
            $uploadDir = public_path('storage/attendance');

            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $extension = $photoFile->getClientOriginalExtension();
            $filename = 'att_' . time() . '_' . Str::random(8) . '.' . $extension;

            $photoFile->move($uploadDir, $filename);

            return 'attendance/' . $filename;
        } catch (\Exception $e) {
            \Log::error('Attendance photo storage error: ' . $e->getMessage());
            return null;
        }
    }

    public function leaveIndex()
    {
        $employee = Auth::user()->employee;
        $leaveRequests = $employee
            ? LeaveRequest::with('approver')
                ->where('employee_id', $employee->id)
                ->orderByDesc('created_at')
                ->paginate(10)
            : collect();

        return view('leave.index', array_merge($this->viewerData(), [
            'leaveRequests' => $leaveRequests,
            'hasEmployeeProfile' => $employee !== null,
        ]));
    }

    public function storeLeaveRequest(Request $request)
    {
        $employee = Auth::user()->employee;

        if (! $employee) {
            return back()->with('error', 'Tài khoản của bạn chưa được gắn với hồ sơ nhân viên nào.');
        }

        $validated = $request->validate([
            'leave_type' => ['required', Rule::in(['annual', 'sick', 'unpaid', 'wfh'])],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'leave_type.required' => 'Vui lòng chọn loại nghỉ.',
            'leave_type.in' => 'Loại nghỉ không hợp lệ.',
            'from_date.required' => 'Vui lòng chọn ngày bắt đầu nghỉ.',
            'to_date.required' => 'Vui lòng chọn ngày kết thúc nghỉ.',
            'to_date.after_or_equal' => 'Ngày kết thúc phải từ ngày bắt đầu trở đi.',
            'reason.required' => 'Vui lòng nhập lý do xin nghỉ.',
            'reason.max' => 'Lý do không được vượt quá 255 ký tự.',
        ]);

        $totalDays = Carbon::parse($validated['from_date'])->diffInDays($validated['to_date']) + 1;

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $validated['leave_type'],
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect('/leave')->with('success', 'Đơn nghỉ phép đã được gửi và đang chờ duyệt.');
    }



    public function profile()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('employee.department', 'employee.position');

        return view('profile.show', array_merge($this->viewerData(), [
            'me' => $user->employee,
        ]));
    }

    public function updateProfile(Request $request)
    {
        $employee = Auth::user()->employee;

        if (! $employee) {
            return back()->with('error', 'Tài khoản của bạn chưa được gắn với hồ sơ nhân viên nào.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'national_id' => ['required', 'string', 'max:20', Rule::unique('employees', 'national_id')->ignore($employee->id)],
            'date_of_birth' => ['required', 'date'],
            'phone' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'max:150', Rule::unique('employees', 'email')->ignore($employee->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'national_id.required' => 'Vui lòng nhập số CCCD.',
            'national_id.unique' => 'Số CCCD này đã được dùng bởi nhân viên khác.',
            'date_of_birth.required' => 'Vui lòng chọn ngày sinh.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được dùng bởi nhân viên khác.',
            'photo.image' => 'File tải lên phải là hình ảnh.',
            'photo.max' => 'Ảnh không được vượt quá 2MB.',
        ]);

        if ($request->hasFile('photo')) {
            $this->deleteEmployeePhoto($employee->photo_path);
            $storedPath = $this->storeEmployeePhoto($request->file('photo'));
            if ($storedPath) {
                $validated['photo_path'] = $storedPath;
            }
        }

        if (isset($validated['photo'])) {
            unset($validated['photo']);
        }

        $employee->update($validated);

        return back()->with('success', 'Cập nhật hồ sơ thành công.');
    }

    public function updateProfilePassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->with('error', 'Mật khẩu hiện tại không đúng.');
        }

        $user->password = $validated['password'];
        $user->save();

        return back()->with('success', 'Đổi mật khẩu thành công.');
    }

    /*
    |--------------------------------------------------------------------------
    | QUẢN LÝ NHÂN VIÊN (HR)
    |--------------------------------------------------------------------------
    */

    public function adminEmployees(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $employees = Employee::with(['department', 'position'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->get();

        return view('admin.employees.index', array_merge($this->viewerData(), [
            'employeesList' => $employees,
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
            'positions' => Position::where('status', 'active')->orderBy('name')->get(),
            'search' => $search,
        ]));
    }

    public function storeEmployee(Request $request)
    {
        $validated = $this->validateEmployee($request);

        if ($request->hasFile('photo')) {
            $storedPath = $this->storeEmployeePhoto($request->file('photo'));
            if ($storedPath) {
                $validated['photo_path'] = $storedPath;
            }
        }
        if (isset($validated['photo'])) {
            unset($validated['photo']);
        }

        $validated['status'] = 'active';

        Employee::create($validated);

        return back()->with('success', 'Thêm hồ sơ nhân viên mới thành công.');
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $validated = $this->validateEmployee($request, $employee->id);

        if ($request->hasFile('photo')) {
            $this->deleteEmployeePhoto($employee->photo_path);
            $storedPath = $this->storeEmployeePhoto($request->file('photo'));
            if ($storedPath) {
                $validated['photo_path'] = $storedPath;
            }
        }
        if (isset($validated['photo'])) {
            unset($validated['photo']);
        }

        $employee->update($validated);

        return back()->with('success', 'Cập nhật thông tin nhân viên thành công.');
    }

    public function toggleEmployeeStatus(Employee $employee)
    {
        $employee->status = $employee->status === 'active' ? 'resigned' : 'active';
        $employee->save();

        return back()->with('success', $employee->status === 'active'
            ? 'Đã chuyển trạng thái: đang làm việc.'
            : 'Đã chuyển trạng thái: nghỉ việc.');
    }

    private function validateEmployee(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'national_id' => ['required', 'string', 'max:20', Rule::unique('employees', 'national_id')->ignore($ignoreId)],
            'date_of_birth' => ['required', 'date'],
            'phone' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email', 'max:150', Rule::unique('employees', 'email')->ignore($ignoreId)],
            'address' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'hire_date' => ['required', 'date'],
            'contract_type' => ['required', 'in:probation,official,seasonal'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'status' => $ignoreId ? ['required', 'in:active,resigned,suspended'] : ['sometimes'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'national_id.required' => 'Vui lòng nhập số CCCD.',
            'national_id.unique' => 'Số CCCD này đã tồn tại.',
            'date_of_birth.required' => 'Vui lòng chọn ngày sinh.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã tồn tại.',
            'department_id.required' => 'Vui lòng chọn phòng ban.',
            'department_id.exists' => 'Phòng ban không hợp lệ.',
            'position_id.required' => 'Vui lòng chọn chức vụ.',
            'position_id.exists' => 'Chức vụ không hợp lệ.',
            'hire_date.required' => 'Vui lòng chọn ngày vào công ty.',
            'contract_type.required' => 'Vui lòng chọn loại hợp đồng.',
            'base_salary.required' => 'Vui lòng nhập lương cơ bản.',
            'base_salary.numeric' => 'Lương cơ bản phải là số.',
        ]);
    }

    private function storeEmployeePhoto($photoFile)
    {
        if (!$photoFile) {
            return null;
        }

        try {
            // Tạo đường dẫn tuyệt đối đến thư mục lưu ảnh
            $uploadDir = public_path('storage/employees');
            
            // Tạo thư mục nếu chưa tồn tại
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            // Tạo tên file duy nhất
            $extension = $photoFile->getClientOriginalExtension();
            $filename = 'employee_' . time() . '_' . Str::random(8) . '.' . $extension;
            
            // Lưu file trực tiếp vào public/storage/employees
            $photoFile->move($uploadDir, $filename);
            
            // Trả về đường dẫn tương đối để lưu vào database
            return 'employees/' . $filename;
        } catch (\Exception $e) {
            \Log::error('Photo storage error: ' . $e->getMessage());
            return null;
        }
    }

    private function deleteEmployeePhoto($photoPath)
    {
        if (!$photoPath) {
            return true;
        }

        try {
            $photoPath = trim($photoPath);
            if ($photoPath && $photoPath !== '') {
                $fullPath = public_path('storage/' . $photoPath);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
            return true;
        } catch (\Exception $e) {
            \Log::error('Photo delete error: ' . $e->getMessage());
            return true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PHÒNG BAN
    |--------------------------------------------------------------------------
    */

    public function adminDepartments()
    {
        $departments = Department::with(['manager', 'employees'])
            ->orderBy('name')
            ->get();

        $employees = Employee::orderBy('name')->get();

        return view('admin.departments.index', array_merge($this->viewerData(), [
            'departments' => $departments,
            'employees' => $employees,
        ]));
    }

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:departments,name'],
            'manager_id' => ['nullable', 'exists:employees,id'],
            'established_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name.required' => 'Vui lòng nhập tên phòng ban.',
            'name.unique' => 'Tên phòng ban đã tồn tại.',
            'manager_id.exists' => 'Trưởng phòng không tồn tại.',
            'established_date.date' => 'Ngày thành lập không hợp lệ.',
            'status.required' => 'Vui lòng chọn trạng thái phòng ban.',
        ]);

        Department::create([
            'name' => $validated['name'],
            'manager_id' => $validated['manager_id'] ?? null,
            'established_date' => $validated['established_date'] ?? null,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Thêm phòng ban thành công.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:departments,name,' . $department->id],
            'manager_id' => ['nullable', 'exists:employees,id'],
            'established_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'name.required' => 'Vui lòng nhập tên phòng ban.',
            'name.unique' => 'Tên phòng ban đã tồn tại.',
            'manager_id.exists' => 'Trưởng phòng không tồn tại.',
            'established_date.date' => 'Ngày thành lập không hợp lệ.',
            'status.required' => 'Vui lòng chọn trạng thái phòng ban.',
        ]);

        $departmentEmployeeIds = Employee::where('department_id', $department->id)->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (!empty($validated['manager_id']) && !in_array((int) $validated['manager_id'], $departmentEmployeeIds, true)) {
            return back()->with('error', 'Chỉ được chọn trưởng phòng thuộc phòng ban này.');
        }

        $department->update([
            'name' => $validated['name'],
            'manager_id' => $validated['manager_id'] ?? null,
            'established_date' => $validated['established_date'] ?? null,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Cập nhật thông tin phòng ban thành công.');
    }

    public function toggleDepartmentStatus(Department $department)
    {
        if ($department->status === 'active' && $department->employees()->exists()) {
            return back()->with('error', 'Không thể khóa phòng ban vì vẫn còn nhân viên trong phòng ban này.');
        }

        $department->status = $department->status === 'active' ? 'inactive' : 'active';
        $department->save();

        return back()->with('success', $department->status === 'active' ? 'Phòng ban đã được kích hoạt.' : 'Phòng ban đã được khóa.');
    }

    /*
    |--------------------------------------------------------------------------
    | QUẢN LÝ CHẤM CÔNG (HR xem chấm công toàn bộ nhân viên)
    |--------------------------------------------------------------------------
    */

    public function adminAttendance(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $date = trim((string) $request->query('date', ''));
        $mode = $request->query('mode', 'name'); // 'date' hoặc 'name'

        $query = Attendance::with(['employee.department', 'employee.position']);

        if ($mode === 'date') {
            // Nút "Tìm theo ngày": lọc theo ngày, ĐỒNG THỜI quét luôn ô tìm tên bên cạnh nếu có nhập
            if ($date !== '') {
                $query->whereDate('work_date', $date);
            }
            if ($search !== '') {
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }
        } else {
            // Nút "Tìm theo tên": CHỈ lọc theo tên, bỏ qua ngày dù ô ngày có giá trị
            if ($search !== '') {
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }
        }

        $records = $query->orderByDesc('work_date')->orderByDesc('check_in')->paginate(20)->withQueryString();

        return view('admin.attendance.index', array_merge($this->viewerData(), [
            'records' => $records,
            'search' => $search,
            'date' => $date,
            'mode' => $mode,
        ]));
    }

    public function adminLeaveApprovals(Request $request)
    {
        abort_unless(Auth::user()->isHr(), 403);

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
        ]);
        $status = $validated['status'] ?? 'pending';
        $leaveRequests = LeaveRequest::with(['employee.department', 'approver'])
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.leave-approvals.index', array_merge($this->viewerData(), [
            'leaveRequests' => $leaveRequests,
            'status' => $status,
            'statusCounts' => LeaveRequest::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'currentEmployeeId' => Auth::user()->employee_id,
        ]));
    }

    public function adminPayrolls(Request $request)
    {
        abort_unless(Auth::user()->isHr(), 403);

        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'search' => ['nullable', 'string', 'max:150'],
        ]);
        $month = $validated['month'] ?? now()->format('Y-m');
        $search = trim($validated['search'] ?? '');

        $payrolls = Payroll::with('employee.department')
            ->where('month', $month)
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('employee', function ($employeeQuery) use ($search) {
                    $employeeQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        return view('admin.payrolls.index', array_merge($this->viewerData(), [
            'payrolls' => $payrolls,
            'employees' => Employee::orderBy('name')->get(),
            'month' => $month,
            'search' => $search,
        ]));
    }

    public function storePayroll(Request $request)
    {
        abort_unless(Auth::user()->isHr(), 403);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'month' => ['required', 'date_format:Y-m'],
            'work_days' => ['required', 'numeric', 'min:0', 'max:31'],
            'paid_leave_days' => ['required', 'numeric', 'min:0', 'max:31'],
            'unpaid_leave_days' => ['required', 'numeric', 'min:0', 'max:31'],
            'salary_received' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
        ], [
            'employee_id.required' => 'Vui lòng chọn nhân viên.',
            'employee_id.exists' => 'Nhân viên không tồn tại.',
            'month.required' => 'Vui lòng chọn tháng lương.',
            'month.date_format' => 'Tháng lương không hợp lệ.',
            'work_days.required' => 'Vui lòng nhập số ngày công.',
            'paid_leave_days.required' => 'Vui lòng nhập số ngày nghỉ hưởng lương.',
            'unpaid_leave_days.required' => 'Vui lòng nhập số ngày nghỉ không lương.',
            'salary_received.required' => 'Vui lòng nhập lương thực nhận.',
        ]);

        DB::transaction(function () use ($validated) {
            Employee::whereKey($validated['employee_id'])->lockForUpdate()->firstOrFail();

            Payroll::updateOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'month' => $validated['month'],
                ],
                [
                    'work_days' => $validated['work_days'],
                    'paid_leave_days' => $validated['paid_leave_days'],
                    'unpaid_leave_days' => $validated['unpaid_leave_days'],
                    'salary_received' => $validated['salary_received'],
                ],
            );
        });

        return redirect()->route('admin.payrolls', ['month' => $validated['month']])
            ->with('success', 'Bảng lương đã được lưu.');
    }

    public function updateLeaveApproval(LeaveRequest $leaveRequest, string $decision)
    {
        $user = Auth::user();
        abort_unless($user->isHr(), 403);

        if (! $user->employee_id) {
            return back()->with('error', 'Tài khoản HR chưa được gắn với hồ sơ nhân viên để ghi nhận người duyệt.');
        }

        if ((int) $leaveRequest->employee_id === (int) $user->employee_id) {
            return back()->with('error', 'Bạn không thể duyệt hoặc từ chối đơn nghỉ phép của chính mình.');
        }

        $nextStatus = match ($decision) {
            'approve' => 'approved',
            'reject' => 'rejected',
            default => abort(404),
        };

        $updated = DB::transaction(function () use ($leaveRequest, $nextStatus, $user) {
            $lockedRequest = LeaveRequest::whereKey($leaveRequest->id)->lockForUpdate()->firstOrFail();

            if ($lockedRequest->status !== 'pending') {
                return false;
            }

            $lockedRequest->update([
                'status' => $nextStatus,
                'approved_by' => $user->employee_id,
            ]);

            return true;
        });

        if (! $updated) {
            return back()->with('error', 'Đơn này đã được xử lý trước đó.');
        }

        return back()->with('success', $nextStatus === 'approved'
            ? 'Đơn nghỉ phép đã được duyệt.'
            : 'Đơn nghỉ phép đã bị từ chối.');
    }

    public function adminReports(Request $request)
    {
        abort_unless(Auth::user()->isHr(), 403);

        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $month = $validated['month'] ?? now()->format('Y-m');
        $monthStart = Carbon::createFromFormat('!Y-m', $month);
        $monthEnd = $monthStart->copy()->endOfMonth();

        $employeeCounts = Employee::query()
            ->where('status', 'active')
            ->selectRaw('department_id, COUNT(*) as total')
            ->groupBy('department_id')
            ->pluck('total', 'department_id');

        $departmentStats = Department::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Department $department) => [
                'id' => $department->id,
                'name' => $department->name,
                'employees' => (int) ($employeeCounts[$department->id] ?? 0),
                'attendance' => 0,
                'late' => 0,
                'leave_days' => 0.0,
                'payroll' => 0.0,
            ]);

        $unassignedEmployeeCount = (int) ($employeeCounts[null] ?? 0);
        if ($unassignedEmployeeCount > 0) {
            $departmentStats->push([
                'id' => null,
                'name' => 'Chưa phân phòng ban',
                'employees' => $unassignedEmployeeCount,
                'attendance' => 0,
                'late' => 0,
                'leave_days' => 0.0,
                'payroll' => 0.0,
            ]);
        }

        $statsByDepartment = $departmentStats->keyBy(fn (array $department) => $department['id'] ?? 'unassigned');
        $attendanceByDay = [];
        $attendanceSummary = Attendance::query()
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->whereBetween('attendances.work_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereNotNull('attendances.check_in')
            ->selectRaw('employees.department_id as department_id, DATE(attendances.work_date) as work_day, COUNT(*) as total, SUM(CASE WHEN COALESCE(attendances.late_minutes, 0) > 0 THEN 1 ELSE 0 END) as late_count')
            ->groupBy('employees.department_id', 'work_day')
            ->get();

        foreach ($attendanceSummary as $row) {
            $departmentKey = $row->department_id ?? 'unassigned';
            if ($statsByDepartment->has($departmentKey)) {
                $stats = $statsByDepartment->get($departmentKey);
                $stats['attendance'] += (int) $row->total;
                $stats['late'] += (int) $row->late_count;
                $statsByDepartment->put($departmentKey, $stats);
            }

            $day = Carbon::parse($row->work_day)->day;
            $attendanceByDay[$day]['total'] = ($attendanceByDay[$day]['total'] ?? 0) + (int) $row->total;
            $attendanceByDay[$day]['late'] = ($attendanceByDay[$day]['late'] ?? 0) + (int) $row->late_count;
        }

        $payrollByDepartment = Payroll::query()
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->where('payrolls.month', $month)
            ->selectRaw('employees.department_id as department_id, SUM(payrolls.salary_received) as total')
            ->groupBy('employees.department_id')
            ->get();

        foreach ($payrollByDepartment as $row) {
            $departmentKey = $row->department_id ?? 'unassigned';
            if ($statsByDepartment->has($departmentKey)) {
                $stats = $statsByDepartment->get($departmentKey);
                $stats['payroll'] = (float) $row->total;
                $statsByDepartment->put($departmentKey, $stats);
            }
        }

        $approvedLeaves = LeaveRequest::with('employee:id,department_id')
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', $monthEnd->toDateString())
            ->whereDate('to_date', '>=', $monthStart->toDateString())
            ->get();

        foreach ($approvedLeaves as $leave) {
            $departmentKey = $leave->employee?->department_id ?? 'unassigned';
            if (! $statsByDepartment->has($departmentKey)) {
                continue;
            }

            $leaveStart = $leave->from_date->greaterThan($monthStart) ? $leave->from_date : $monthStart;
            $leaveEnd = $leave->to_date->lessThan($monthEnd) ? $leave->to_date : $monthEnd;
            $stats = $statsByDepartment->get($departmentKey);
            $stats['leave_days'] += $leaveStart->diffInDays($leaveEnd) + 1;
            $statsByDepartment->put($departmentKey, $stats);
        }

        $departmentStats = $statsByDepartment->values();
        $activeEmployeeCount = (int) $employeeCounts->sum();
        $monthlyAttendanceCount = (int) $attendanceSummary->sum('total');
        $monthlyLateCount = (int) $attendanceSummary->sum('late_count');
        $onTimeRate = $monthlyAttendanceCount > 0
            ? round((($monthlyAttendanceCount - $monthlyLateCount) / $monthlyAttendanceCount) * 100)
            : 0;
        $daysInMonth = $monthStart->daysInMonth;
        $attendanceChart = [
            'labels' => range(1, $daysInMonth),
            'onTime' => [],
            'late' => [],
        ];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $attendanceChart['late'][] = $attendanceByDay[$day]['late'] ?? 0;
            $attendanceChart['onTime'][] = ($attendanceByDay[$day]['total'] ?? 0) - ($attendanceByDay[$day]['late'] ?? 0);
        }

        return view('admin.reports.index', array_merge($this->viewerData(), [
            'month' => $month,
            'activeEmployeeCount' => $activeEmployeeCount,
            'todayAttendanceCount' => Attendance::whereDate('work_date', today())->whereNotNull('check_in')->count(),
            'pendingLeaveCount' => LeaveRequest::where('status', 'pending')->count(),
            'monthlyPayrollTotal' => Payroll::where('month', $month)->sum('salary_received'),
            'onTimeRate' => $onTimeRate,
            'departmentStats' => $departmentStats,
            'employeeChart' => [
                'labels' => $departmentStats->pluck('name')->all(),
                'data' => $departmentStats->pluck('employees')->all(),
            ],
            'attendanceChart' => $attendanceChart,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | TÀI KHOẢN ĐĂNG NHẬP
    |--------------------------------------------------------------------------
    */

    public function adminAccounts(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::with('employee')
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('username')
            ->get();

        $employees = Employee::query()
            ->whereDoesntHave('user')
            ->orderBy('name')
            ->get();

        return view('admin.accounts.index', array_merge($this->viewerData(), [
            'users' => $users,
            'employees' => $employees,
            'search' => $search,
        ]));
    }

    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'employee_id' => ['required', 'exists:employees,id'],
            'role' => ['required', 'in:hr,employee'],
        ], [
            'username.required' => 'Vui lòng nhập tên tài khoản.',
            'username.unique' => 'Tên tài khoản này đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'employee_id.required' => 'Vui lòng chọn nhân viên.',
            'employee_id.exists' => 'Nhân viên không tồn tại.',
            'role.required' => 'Vui lòng chọn vai trò.',
        ]);

        User::create([
            'username' => $validated['username'],
            'password' => $validated['password'],
            'employee_id' => $validated['employee_id'],
            'role' => $validated['role'],
            'status' => 'active',
        ]);

        return back()->with('success', 'Tạo tài khoản thành công.');
    }

    public function toggleAccountStatus(User $user)
    {
        if ((int) Auth::id() === (int) $user->id) {
            return back()->with('error', 'Bạn không thể khóa hoặc mở khóa tài khoản của chính mình.');
        }

        $user->status = $user->isLocked() ? 'active' : 'locked';
        $user->save();

        return back()->with('success', $user->isLocked() ? 'Tài khoản đã bị khóa.' : 'Tài khoản đã được mở khóa.');
    }

    public function deleteAccount(User $user)
    {
        if ((int) Auth::id() === (int) $user->id) {
            return back()->with('error', 'Bạn không thể xóa tài khoản của chính mình.');
        }

        $user->delete();

        return back()->with('success', 'Đã xóa tài khoản thành công.');
    }
}