<?php

namespace App\View\Components\ecommerce;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MonthlySale extends Component
{
    public $leaves; // Variabel ini yang akan dibaca di blade sebagai $leaves

    public function __construct($leaves = [])
    {
        $this->leaves = $leaves;
    }

    public function render(): View|Closure|string
    {
        return view('components.ecommerce.monthly-sale');
    }
}