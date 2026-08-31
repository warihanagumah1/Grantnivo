<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CookiePreferencesController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate(['analytics' => ['required', Rule::in(['accepted', 'declined'])]]);
        $response = redirect()->to(route('policies.cookies').'#preferences')
            ->with('cookie_status', 'Your cookie preferences have been saved.');
        $response->withCookie(cookie('grantnivo_analytics', $validated['analytics'], 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'lax'));
        if ($validated['analytics'] === 'declined') {
            $response->withoutCookie('grantnivo_visitor');
        }

        return $response;
    }
}
