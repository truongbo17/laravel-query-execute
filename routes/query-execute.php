<?php

use Bo\LaravelQueryExecute\Http\Controllers\QueryExecuteController;

Route::get('query-execute', [QueryExecuteController::class, 'home'])->name('query-execute.home');
Route::get('query-execute/query/{id?}', [QueryExecuteController::class, 'query'])->name('query-execute.query');
Route::get('query-execute/version', [QueryExecuteController::class, 'version'])->name('query-execute.version');
Route::get('query-execute/table', [QueryExecuteController::class, 'getTables'])->name('query-execute.table');
Route::post('query-execute/execute', [QueryExecuteController::class, 'execute'])->name('query-execute.execute');
Route::post('query-execute/save', [QueryExecuteController::class, 'saveQuery'])->name('query-execute.save');
