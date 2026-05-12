<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role; // Import the Role model from Spatie
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view users', only: ['index']),
            new Middleware('can:manage users', only: ['store']),
        ];
    }

    // Show the user creation form
    public function index()
    {
        $data = [
            'users' => User::all(),
            'roles' =>Role::all(),
        ];
         // Fetch all roles
        return view('pages.admin.users.index', compact('data'));
    }

    // Store the newly created user
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'username' => 'required|string|max:255|unique:users,username',
                'name' => 'required|string|max:255',
                'surname' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'role_id' => 'required|exists:roles,id',
            ]);

            $user = User::create([
                'username' => $validated['username'],
                'name' => $validated['name'],
                'surname' => $validated['surname'] ?? null,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $role = Role::findOrFail($validated['role_id']);
            $user->syncRoles([$role->name]);

            DB::commit();

            return redirect()->route('admin.users')->with('success', 'User created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withErrors('Create user failed: '.$e->getMessage())
                ->withInput();
        }
    }



}
