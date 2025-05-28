<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TodosController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Lindungi route dengan middleware auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Home/Index route
    Route::get('/', [TodosController::class, 'index'])->name('api.home');
    
    // Category routes
    Route::get('/categories', [TodosController::class, 'categories']);
    Route::post('/categories', [TodosController::class, 'postCategory']);
    
    // Todo routes (samakan dengan web routes)
    Route::post('/todo/add', [TodosController::class, 'addTodo'])->name('api.todo.add');
    Route::put('/todo/{id}/edit', [TodosController::class, 'editTodo'])->name('api.todo.edit');
    Route::delete('/todo/{id}/delete', [TodosController::class, 'deleteTodo'])->name('api.todo.delete');
    Route::patch('/todo/{id}/toggle-status', [TodosController::class, 'toggleStatus'])->name('api.todo.toggle-status');
    
    // Auth route
    Route::post('/logout', [AuthController::class, 'logout']);
});