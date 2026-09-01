<?php

namespace App\Exports;

use App\Models\ServiceablePincode;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServiceablePincodesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return ServiceablePincode::orderBy('pincode')->get();
    }

    public function headings(): array
    {
        return [
            'Pincode', 'City', 'State',
            'Same Day Available', 'Midnight Available', 'Express Available', 'Active',
        ];
    }

    public function map($pincode): array
    {
        return [
            $pincode->pincode,
            $pincode->city,
            $pincode->state,
            $pincode->same_day_available ? 'Yes' : 'No',
            $pincode->midnight_available ? 'Yes' : 'No',
            $pincode->express_available ? 'Yes' : 'No',
            $pincode->is_active ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}