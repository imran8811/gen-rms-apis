<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffAdvance;
use App\Models\StaffAttendance;
use App\Models\StaffFoodLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffPayrollController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $month       = $request->get('month', now()->format('Y-m'));
        $workingDays = (int) $request->get('working_days', 26);

        [$year, $mon] = explode('-', $month);

        $allStaff = Staff::where('is_active', true)->orderBy('name')->get();

        $payroll = $allStaff->map(function (Staff $staff) use ($year, $mon, $month, $workingDays) {
            $base      = $staff->salary;
            $dailyRate = $workingDays > 0 ? $base / $workingDays : 0;

            $attendance = StaffAttendance::where('staff_id', $staff->id)
                ->whereYear('date', $year)->whereMonth('date', $mon)
                ->get(['status']);

            $absentDays  = $attendance->where('status', 'absent')->count();
            $halfDays    = $attendance->where('status', 'half_day')->count();

            $absentDeduct   = (int) round($absentDays * $dailyRate);
            $halfDayDeduct  = (int) round($halfDays   * ($dailyRate / 2));

            $foodDeduct = (int) StaffFoodLog::where('staff_id', $staff->id)
                ->whereYear('consumed_at', $year)->whereMonth('consumed_at', $mon)
                ->sum('total_amount');

            $advanceDeduct = (int) StaffAdvance::where('staff_id', $staff->id)
                ->where('repayment_month', $month)
                ->sum('amount');

            $totalDeductions = $absentDeduct + $halfDayDeduct + $foodDeduct + $advanceDeduct;
            $netPayable      = max(0, $base - $totalDeductions);

            return [
                'staff_id'        => $staff->id,
                'staff_name'      => $staff->name,
                'role'            => $staff->role,
                'base_salary'     => $base,
                'daily_rate'      => (int) round($dailyRate),
                'present_days'    => $attendance->whereIn('status', ['present', 'late'])->count(),
                'absent_days'     => $absentDays,
                'half_days'       => $halfDays,
                'absent_deduct'   => $absentDeduct,
                'half_day_deduct' => $halfDayDeduct,
                'food_deduct'     => $foodDeduct,
                'advance_deduct'  => $advanceDeduct,
                'total_deductions'=> $totalDeductions,
                'net_payable'     => $netPayable,
            ];
        });

        return response()->json([
            'month'             => $month,
            'working_days'      => $workingDays,
            'total_base_salary' => $payroll->sum('base_salary'),
            'total_payable'     => $payroll->sum('net_payable'),
            'staff'             => $payroll->values(),
        ]);
    }
}
