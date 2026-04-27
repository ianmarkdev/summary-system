<?php

use App\Http\Controllers\IssueController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('issues.index'));

Route::prefix('issues')->name('issues.')->group(function () {
    Route::get('/',          [IssueController::class, 'index'])->name('index');
    Route::get('/create',    [IssueController::class, 'create'])->name('create');
    Route::post('/',         [IssueController::class, 'store'])->name('store');
    Route::get('/{issue}',   [IssueController::class, 'show'])->name('show');
    Route::get('/{issue}/edit', [IssueController::class, 'edit'])->name('edit');
    Route::patch('/{issue}', [IssueController::class, 'update'])->name('update');
});
