<?php

namespace App\Http\Controllers;

use App\Models\GrantOpportunity;
use Illuminate\Contracts\View\View;

class MarketingHomeController extends Controller
{
    public function __invoke(): View
    {
        return view('welcome', [
            'featuredOpportunities' => GrantOpportunity::query()
                ->open()
                ->orderByDesc('featured')
                ->orderBy('deadline')
                ->limit(3)
                ->get(),
        ]);
    }
}
