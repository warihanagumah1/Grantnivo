<?php

use App\Http\Controllers\LegacyOpportunityRedirectController;
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
    Route::get('opportunities/{grantOpportunity:uuid}', OpportunityDetail::class)->whereUuid('grantOpportunity')->name('opportunities.show');
    Route::get('opportunities/{legacyOpportunity:slug}', [LegacyOpportunityRedirectController::class, 'show'])->name('opportunities.legacy');
});
Route::get('opportunities/{grantOpportunity:uuid}/visit/{kind}', OpportunityLinkController::class)->whereUuid('grantOpportunity')
    ->whereIn('kind', ['apply', 'funder', 'learn_more'])->middleware('throttle:60,1')->name('opportunities.visit');
Route::get('opportunities/{legacyOpportunity:slug}/visit/{kind}', [LegacyOpportunityRedirectController::class, 'visit'])
    ->whereIn('kind', ['apply', 'funder', 'learn_more'])->middleware('throttle:60,1')->name('opportunities.legacy-visit');

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', Admin\Overview::class)->name('overview');
    Route::get('opportunities', Admin\Opportunities::class)->name('opportunities');
    Route::get('opportunities/create', Admin\OpportunityForm::class)->name('opportunities.create');
    Route::get('opportunities/import', Admin\OpportunityImport::class)->name('opportunities.import');
    Route::get('opportunities/import-template', fn () => response()->download(resource_path('examples/opportunities.csv'), 'grantnivo-opportunities-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']))->name('opportunities.import-template');
    Route::get('opportunities/{grantOpportunity:uuid}/preview', Admin\OpportunityPreview::class)->whereUuid('grantOpportunity')->name('opportunities.preview');
    Route::get('opportunities/{grantOpportunity:uuid}/edit', Admin\OpportunityForm::class)->whereUuid('grantOpportunity')->name('opportunities.edit');
    Route::get('opportunities/{legacyOpportunity:id}/edit', [LegacyOpportunityRedirectController::class, 'edit'])->whereNumber('legacyOpportunity')->name('opportunities.legacy-edit');
    Route::get('analytics', Admin\Analytics::class)->name('analytics');
    Route::get('waitlist', Admin\Waitlist::class)->name('waitlist');
});

Route::redirect('login', '/admin/login');
Route::redirect('grant-opportunities', '/opportunities', 301);
Route::redirect('grant-opportunities/{grantOpportunity}', '/opportunities/{grantOpportunity}', 301);
