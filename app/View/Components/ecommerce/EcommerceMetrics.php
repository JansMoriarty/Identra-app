<?php

namespace App\View\Components\Ecommerce;

use Illuminate\View\Component;

class EcommerceMetrics extends Component
{
    public $totalGuruHadir;
    public $persentaseHadir;
    public $totalKelasTerisi; // Ini bisa tetap dipakai atau diganti
    public $totalRuangan;
    public $totalGuru;
    public $totalJadwalHariIni;

    public function __construct(
        $totalGuruHadir = 0, 
        $persentaseHadir = 0, 
        $totalKelasTerisi = 0, 
        $totalRuangan = 0,
        $totalGuru = 0,
        $totalJadwalHariIni = 0
    ) {
        $this->totalGuruHadir = $totalGuruHadir;
        $this->persentaseHadir = $persentaseHadir;
        $this->totalKelasTerisi = $totalKelasTerisi;
        $this->totalRuangan = $totalRuangan;
        $this->totalGuru = $totalGuru;
        $this->totalJadwalHariIni = $totalJadwalHariIni;
    }

    public function render()
    {
        return view('components.ecommerce.ecommerce-metrics');
    }
}