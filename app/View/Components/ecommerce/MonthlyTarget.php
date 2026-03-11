<?php

namespace App\View\Components\Ecommerce;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MonthlyTarget extends Component
{
    // 1. Deklarasikan semua variabel yang akan digunakan
    public $greeting;
    public $holidayDates;
    public $holidayList;

    // 2. Terima semua variabel melalui constructor
    // Berikan default value (array kosong) agar tidak error jika data tidak ada
    public function __construct(
        $greeting = 'Selamat Datang', 
        $holidayDates = [], 
        $holidayList = []
    ) {
        $this->greeting = $greeting;
        $this->holidayDates = $holidayDates;
        $this->holidayList = $holidayList;
    }

    public function render(): View|Closure|string
    {
        return view('components.ecommerce.monthly-target');
    }
}