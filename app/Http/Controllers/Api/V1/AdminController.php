<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminController extends Controller
{
    public function users()
    {
        // Don’t expose passwords/remember tokens anyway, but still keep it minimal
        $users = User::query()
            ->select(['id','username','name','surname','email','phone','created_at'])
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($users);
    }

    public function roles()
    {
        return response()->json([
            'data' => Role::query()->select(['id','name'])->orderBy('name')->get()
        ]);
    }

    public function permissions()
    {
        return response()->json([
            'data' => Permission::query()->select(['id','name'])->orderBy('name')->get()
        ]);
    }
}
