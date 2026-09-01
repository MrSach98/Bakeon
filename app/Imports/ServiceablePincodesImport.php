<?php

namespace App\Imports;

use App\Models\ServiceablePincode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ServiceablePincodesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    /**
     * Expected Excel columns (case-insensitive, spaces become underscores):
     * pincode | city | state | same_day_available | midnight_available | express_available | active
     */
    public function model(array $row)
    {
        return ServiceablePincode::updateOrCreate(
            ['pincode' => trim($row['pincode'])],
            [
                'city' => $row['city'] ?? '',
                'state' => $row['state'] ?? null,
                'same_day_available' => $this->toBool($row['same_day_available'] ?? 'yes'),
                'midnight_available' => $this->toBool($row['midnight_available'] ?? 'no'),
                'express_available' => $this->toBool($row['express_available'] ?? 'no'),
                'is_active' => $this->toBool($row['active'] ?? 'yes'),
            ]
        );
    }

    public function rules(): array
    {
        return [
            'pincode' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
        ];
    }

    private function toBool($value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['yes', '1', 'true', 'y']);
    }
}