<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserStoreRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends APIController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'search' => 'string',
            'sort_by' => 'string|in:id,name',
            'sort_order' => 'string|in:asc,desc',
            'page' => 'integer|required',
            'page_size' => 'integer|required',
        ]);

        if($validator->fails()){
            return $this->respond($validator->errors(), 'Validation Error', 422);
        }

        $params['search'] = $params['search'] ?? '';
        $params['sort'] = $params['sort_by'] ?? 'id';
        $params['order'] = $params['sort_order'] ?? 'asc';

        if(Auth::user()->hasRole('manager')){
            $users = User::with('company')->where('name', 'like', '%'.$params['search'].'%')
                ->orderBy($params['sort'], $params['order'])
                ->whereHas('roles', function($q){
                    $q->whereNot('name', 'super_admin');
                })
                ->paginate($params['page_size']);
        }
        else{
            $users = User::with('company')->where('name', 'like', '%'.$params['search'].'%')
                ->orderBy($params['sort'], $params['order'])
                ->whereHas('roles', function($q){
                    $q->where('name', 'employee');
                })
                ->paginate($params['page_size']);
        }

        return $this->respond($users,'success', 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            if(!Auth::user()->hasRole('manager')){
                return $this->respond(null, 'Unauthorized', 401);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'phone' => 'required|string|numeric|unique:users',
                'address' => 'required|string',
            ]);

            if($validator->fails()){
                return $this->respond($validator->errors(), 'Validation Error', 422);
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->company_id = Auth::user()->company_id;
            $user->save();

            $employeeRole = Role::where('name', 'employee')->first();
            $user->assignRole($employeeRole);
            
            return $this->respond($user, 'User created successfully', 201);
        }catch(\Exception $e){
            return $this->respond(null, $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(Auth::user()->hasRole('manager')){
            $user = User::with('company')->find($id);
        }
        else{
            $user = User::with('company')->whereHas('roles', function($q){
                $q->where('name', 'employee');
            })->find($id);
        }
        if($user){
            return $this->respond($user, 'success', 200);
        }
        return $this->respond(null, 'User not found', 404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if(Auth::user()->hasRole('manager')){
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'email' => 'string|email|max:255|unique:users,email,'.$id,
                'phone' => 'string|numeric|unique:users,phone,'.$id,
                'address' => 'string',
            ]);

            if($validator->fails()){
                return $this->respond($validator->errors(), 'Validation Error', 422);
            }

            $user = User::find($id);
            if($user){
                $user->name = $request->name ?? $user->name;
                $user->email = $request->email ?? $user->email;
                $user->phone = $request->phone ?? $user->phone;
                $user->address = $request->address ?? $user->address;
                $user->save();
                return $this->respond($user, 'User updated successfully', 200);
            }
            return $this->respond(null, 'User not found', 404);
        }
        else{
            return $this->respond(null, 'Unauthorized', 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if(Auth::user()->hasRole('manager')){
            $user = User::find($id);
            if($user){
                $user->delete();
                return $this->respond(null, 'User deleted successfully', 200);
            }
            return $this->respond(null, 'User not found', 404);
        }
        else{
            return $this->respond(null, 'Unauthorized', 401);
        }
    }

    public function restore(string $id)
    {
        if(Auth::user()->hasRole('manager')){
            $user = User::withTrashed()->find($id);
            if($user){
                $user->restore();
                return $this->respond($user, 'User restored successfully', 200);
            }
            return $this->respond(null, 'User not found', 404);
        }
        else{
            return $this->respond(null, 'Unauthorized', 401);
        }
    }

    public function forceDelete(string $id)
    {
        if(Auth::user()->hasRole('manager')){
            $user = User::withTrashed()->find($id);
            if($user){
                $user->forceDelete();
                return $this->respond(null, 'User permanently deleted', 200);
            }
            return $this->respond(null, 'User not found', 404);
        }
        else{
            return $this->respond(null, 'Unauthorized', 401);
        }
    }

    public function me()
    {
        $user = Auth::user()->load('company');
        return $this->respond($user, 'success', 200);
    }

    public function updateProfile(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'email' => 'string|email|max:255|unique:users,email,'.Auth::id(),
                'phone' => 'string|numeric|unique:users,phone,'.Auth::id(),
                'password' => 'string|min:8',
                'address' => 'string',
            ]);

            if($validator->fails()){
                return $this->respond($validator->errors(), 'Validation Error', 400);
            }

            $user = User::with('company')->find(Auth::user()->id);
            if(!$user->hasRole('manager')){
                return $this->respond(null, 'Unauthorized', 401);
            }
            if($user){
                $user->name = $request->name ?? $user->name;
                $user->email = $request->email ?? $user->email;
                $user->password = $request->password ? Hash::make($request->password) : $user->password;
                $user->phone = $request->phone ?? $user->phone;
                $user->address = $request->address ?? $user->address;
                $user->save();

                return $this->respond($user, 'Profile updated successfully', 200);
            }
            return $this->respond(null, 'User not found', 404);
        } catch(\Exception $e){
            return $this->respond(null, $e->getMessage(), 500);
        }
    }
}
