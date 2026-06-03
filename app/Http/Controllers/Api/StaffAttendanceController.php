<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StaffAttendance::with('staff:id,name,role');

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }
        if ($request->has('month')) {
            [$year, $mon] = explode('-', $request->month);
            $query->whereYear('date', $year)->whereMonth('date', $mon);
        }
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        return response()->json($query->orderBy('date', 'desc')->orderBy('staff_id')->get());
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $request->validate([
            'date'                        => 'required|date',
            'records'                     => 'required|array|min:1',
            'records.*.staff_id'          => 'required|exists:staff,id',
            'records.*.status'            => 'required|in:present,absent,half_day,late',
            'records.*.check_in_time'     => 'nullable|date_format:H:i,H:i:s',
        ]);

        $date  = $request->date;
        $saved = [];

        foreach ($request->records as $rec) {
            $saved[] = StaffAttendance::updateOrCreate(
                ['staff_id' => $rec['staff_id'], 'date' => $date],
                ['status' => $rec['status'], 'check_in_time' => $rec['check_in_time'] ?? null]
            );
        }

        return response()->json($saved, 201);
    }

    public function summary(Request $request): JsonResponse
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);

        $allStaff = Staff::where('is_active', true)->orderBy('name')->get(['id', 'name', 'role', 'salary']);

        $data = $allStaff->map(function (Staff $staff) use ($year, $mon) {
            $records = StaffAttendance::where('staff_id', $staff->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $mon)
                ->get(['status']);

            return [
                'staff_id'    => $staff->id,
                'staff_name'  => $staff->name,
                'role'        => $staff->role,
                'present'     => $records->whereIn('status', ['present', 'late'])->count(),
                'absent'      => $records->where('status', 'absent')->count(),
                'half_day'    => $records->where('status', 'half_day')->count(),
                'late'        => $records->where('status', 'late')->count(),
                'total_logged'=> $records->count(),
            ];
        });

        return response()->json(['month' => $month, 'data' => $data]);
    }
}
