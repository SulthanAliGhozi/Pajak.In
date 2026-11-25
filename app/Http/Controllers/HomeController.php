<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\RegionProviderInterface;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    public function __construct(
        private readonly RegionProviderInterface $regions,
    ) {
    }

    public function index(): View
    {
        try {
            $provinces = $this->regions->getProvinces();
        } catch (Throwable) {
            $provinces = [];
        }

        return view('welcome', compact('provinces'));
    }
}
