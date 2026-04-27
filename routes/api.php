<?php

use App\Http\Controllers\IssueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Issue Intake API
|--------------------------------------------------------------------------
|
| Base URL: /api
|
| GET    /api/issues              – list (filterable by status/priority/category/escalated)
| POST   /api/issues              – create
| GET    /api/issues/{issue}      – view single
| PATCH  /api/issues/{issue}      – update
|
*/

Route::prefix('issues')->name('api.issues.')->group(function () {
    Route::get('/',          [IssueController::class, 'apiIndex'])->name('index');
    Route::post('/',         [IssueController::class, 'apiStore'])->name('store');
    Route::get('/{issue}',   [IssueController::class, 'apiShow'])->name('show');
    Route::patch('/{issue}', [IssueController::class, 'apiUpdate'])->name('update');
});
