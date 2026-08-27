<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembayaranExport implements FromView, WithStyles, ShouldAutoSize
{
    protected $pembayaran;
    protected $firstDetail;

    public function __construct($pembayaran, $firstDetail)
    {
        $this->pembayaran = $pembayaran;
        $this->firstDetail = $firstDetail;
    }

    public function view(): View
    {
        return view('exports.pembayaran', [
            'pembayaran' => $this->pembayaran,
            'firstDetail' => $this->firstDetail,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }
}
