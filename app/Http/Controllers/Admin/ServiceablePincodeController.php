<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ServiceablePincodesExport;
use App\Http\Controllers\Controller;
use App\Imports\ServiceablePincodesImport;
use App\Models\ServiceablePincode;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ServiceablePincodeController extends Controller
{
    public function index()
    {
        return view('admin.pincodes.index');
    }

    public function data()
    {
        $pincodes = ServiceablePincode::select('serviceable_pincodes.*');

        return DataTables::of($pincodes)
            ->addColumn('same_day', function (ServiceablePincode $p) {
                return $p->same_day_available
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>';
            })
            ->addColumn('midnight', function (ServiceablePincode $p) {
                return $p->midnight_available
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>';
            })
            ->addColumn('express', function (ServiceablePincode $p) {
                return $p->express_available
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>';
            })
            ->addColumn('status', function (ServiceablePincode $p) {
                $checked = $p->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input status-toggle" role="switch"
                                   data-id="' . $p->id . '" ' . $checked . '>
                        </div>';
            })
            ->addColumn('actions', function (ServiceablePincode $p) {
                return '
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(' . $p->id . ')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(' . $p->id . ')">Delete</button>
                    <form id="deleteForm' . $p->id . '" action="' . route('admin.pincodes.destroy', $p) . '" method="POST" class="d-none">
                        ' . csrf_field() . method_field('DELETE') . '
                    </form>
                ';
            })
            ->rawColumns(['same_day', 'midnight', 'express', 'status', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        ServiceablePincode::create($validated);

        return redirect()->route('admin.pincodes.index')->with('success', 'Pincode added successfully.');
    }

    public function fetch(ServiceablePincode $pincode)
    {
        return response()->json($pincode);
    }

    public function update(Request $request, ServiceablePincode $pincode)
    {
        $validated = $this->validateData($request, $pincode->id);

        $pincode->update($validated);

        return redirect()->route('admin.pincodes.index')->with('success', 'Pincode updated successfully.');
    }

    public function destroy(ServiceablePincode $pincode)
    {
        $pincode->delete();

        return back()->with('success', 'Pincode deleted successfully.');
    }

    public function toggleStatus(ServiceablePincode $pincode)
    {
        $pincode->update(['is_active' => ! $pincode->is_active]);

        return response()->json(['is_active' => $pincode->is_active]);
    }

    public function export()
    {
        return Excel::download(new ServiceablePincodesExport, 'serviceable-pincodes.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new ServiceablePincodesImport;
        Excel::import($import, $request->file('excel_file'));

        $failures = $import->failures();

        if ($failures->count() > 0) {
            $messages = $failures->map(fn ($f) => "Row {$f->row()}: " . implode(', ', $f->errors()))->take(10);

            return back()->with('error', 'Some rows were skipped: ' . $messages->implode(' | '));
        }

        return back()->with('success', 'Pincodes imported successfully.');
    }

    public function downloadSampleTemplate()
    {
        $path = storage_path('app/templates/pincode-sample-template.xlsx');

        if (! file_exists($path)) {
            // Build a minimal sample on the fly if the static file isn't present
            return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function array(): array
                {
                    return [
                        ['110001', 'New Delhi', 'Delhi', 'Yes', 'Yes', 'No', 'Yes'],
                        ['400001', 'Mumbai', 'Maharashtra', 'Yes', 'No', 'Yes', 'Yes'],
                    ];
                }
                public function headings(): array
                {
                    return ['pincode', 'city', 'state', 'same_day_available', 'midnight_available', 'express_available', 'active'];
                }
            }, 'pincode-sample-template.xlsx');
        }

        return response()->download($path);
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'pincode' => ['required', 'string', 'max:10', 'unique:serviceable_pincodes,pincode' . ($ignoreId ? ",$ignoreId" : '')],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'same_day_available' => ['nullable', 'boolean'],
            'midnight_available' => ['nullable', 'boolean'],
            'express_available' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['same_day_available'] = $request->boolean('same_day_available');
        $data['midnight_available'] = $request->boolean('midnight_available');
        $data['express_available'] = $request->boolean('express_available');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}