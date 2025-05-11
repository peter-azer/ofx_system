<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->with('team')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $role = $user->getRoleNames();
        $permissions = $user->getAllPermissions(); // This will return a collection of permissions

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'role' => $role,
            'team' => $user->team,
            'userName' => $user->name,
            'permissions' => $permissions,
        ], 200);
    }

    public function register(Request $request)
    {
        try{

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'phone' => 'required|string',
                'role' => 'required|string|exists:roles,name',
                'national_id' => 'required|string|unique:users,national_id',
                'birth_date' => 'required|date',
                'team_id' => 'integer|exists:teams,id',
                'department_id' => 'required|integer|exists:departments,id',
                'permissions' => 'sometimes|array',
                'permissions.*' => 'string|exists:permissions,name',
            ]);
            

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'National_id' => $request->national_id,
            'birth_date' => $request->birth_date,
            'team_id' => $request->team_id,
            'department_id' => $request->department_id,
        ]);

        
        $user->assignRole($request->role);
        
        if ($request->permissions) {
            $user->syncPermissions($request->permissions);
        }
        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
     }catch(\Exception $error){
        return response()->json(['message' => $error->getMessage()], 500);
        }
    }

}
