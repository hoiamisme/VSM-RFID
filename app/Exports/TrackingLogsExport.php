<?php

namespace App\Exports;

use App\Models\TrackingLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrackingLogsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $dateFrom;
    protected $dateTo;
    protected $locationCode;

    public function __construct($dateFrom, $dateTo, $locationCode = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->locationCode = $locationCode;
    }

    public function query()
    {
        $query = TrackingLog::query()
            ->with(['user', 'location'])
            ->whereHas('user')
            ->whereHas('location')
            ->whereBetween('created_at', [
                $this->dateFrom . ' 00:00:00',
                $this->dateTo . ' 23:59:59'
            ])
            ->orderBy('created_at', 'desc');

        if ($this->locationCode) {
            $query->where('location_code', $this->locationCode);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Waktu',
            'Nama Tamu',
            'Institusi',
            'Lokasi',
            'Aksi',
            'Status',
            'RFID UID'
        ];
    }

    public function map($log): array
    {
        return [
            $log->created_at->format('d/m/Y H:i:s'),
            $log->user->name,
            $log->user->institution ?? '-',
            $log->location->name,
            $log->action_type === 'entry' ? 'Masuk' : 'Keluar',
            $log->status === 'accepted' ? 'Diterima' : ucfirst($log->status),
            $log->rfid_uid
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 30,
            'C' => 30,
            'D' => 25,
            'E' => 15,
            'F' => 15,
            'G' => 25,
        ];
    }
}
