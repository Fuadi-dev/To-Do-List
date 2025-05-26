<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodosController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TodosController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('login-post', [AuthController::class, 'loginPost'])->name('login.post');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('register-post', [AuthController::class, 'registerPost'])->name('register.post');

// Add todo routes
Route::post('/todo/add', [TodosController::class, 'addTodo'])->name('todo.add');
Route::put('/todo/{id}/edit', [TodosController::class, 'editTodo'])->name('todo.edit');
Route::delete('/todo/{id}/delete', [TodosController::class, 'deleteTodo'])->name('todo.delete');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');