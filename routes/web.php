<?php

use App\Http\Controllers\MarketingAboutController;
use App\Http\Controllers\MarketingHomeController;
use App\Http\Controllers\OpportunityLinkController;
use App\Http\Middleware\TrackPublicVisit;
use App\Livewire\Admin;
use App\Livewire\Marketing\OpportunityDetail;
use App\Livewire\Marketing\OpportunityLibrary;
use App\Livewire\Marketing\Waitlist;
use Illuminate\Support\Facades\Route;

Route::middleware(TrackPublicVisit::class)->group(function () {
    Route::get('/', MarketingHomeController::class)->name('home');
    Route::view('platform', 'pages.marketing.platform')->name('platform');
    Route::get('about', MarketingAboutController::class)->name('about');
    Route::get('waitlist', Waitlist::class)->name('waitlist');
    Route::get('opportunities', OpportunityLibrary::class)->name('opportunities.index');
    Route::get('opportunities/{grantOpportunity:slug}', OpportunityDetail::class)->name('opportunities.show');
});
Route::get('opportunities/{grantOpportunity:slug}/visit/{kind}', OpportunityLinkController::class)
    ->whereIn('kind', ['apply', 'funder', 'learn_more'])->middleware('throttle:60,1')->name('opportunities.visit');

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', Admin\Overview::class)->name('overview');
    Route::get('opportunities', Admin\Opportunities::class)->name('opportunities');
    Route::get('opportunities/create', Admin\OpportunityForm::class)->name('opportunities.create');
    Route::get('opportunities/{grantOpportunity}/edit', Admin\OpportunityForm::class)->name('opportunities.edit');
    Route::get('analytics', Admin\Analytics::class)->name('analytics');
    Route::get('waitlist', Admin\Waitlist::class)->name('waitlist');
});

Route::redirect('login', '/admin/login');
Route::redirect('grant-opportunities', '/opportunities', 301);
Route::redirect('grant-opportunities/{grantOpportunity}', '/opportunities/{grantOpportunity}', 301);
