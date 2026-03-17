<?php

use App\Livewire\Admin\Page;
use App\Livewire\Admin\Permission;
use App\Livewire\Admin\Role;
use App\Livewire\Admin\User\Index;
use App\Livewire\Admin\User\Show;
use Illuminate\Support\Facades\Route;

Route::get('users', Index::class)->name('admin.users.index');
Route::get('users/{user}', Show::class)->name('admin.users.show');
Route::get('pages', Page::class)->name('admin.pages');
Route::get('roles', Role::class)->name('admin.roles');
Route::get('permissions', Permission::class)->name('admin.permissions');