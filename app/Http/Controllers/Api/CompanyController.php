<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Http\Controllers\APIController;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CompanyController extends APIController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if(!auth()->user()->hasRole('super_admin')){
            return $this->respond(null, 'Unauthorized', 401);
        }
        
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
        try{
            $params['search'] = $params['search'] ?? '';
            $params['sort'] = $params['sort_by'] ?? 'id';
            $params['order'] = $params['sort_order'] ?? 'asc';
    
            $companies = Company::where('name', 'like', '%'.$params['search'].'%')
                ->orderBy($params['sort'], $params['order'])
                ->paginate($params['page_size']);
            
            return $this->respond($companies, 'success', 200);
        } catch (\Exception $e) {
            return $this->respond($e->getMessage(), 'Internal Server Error', 500);
        }
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
        if(!auth()->user()->hasRole('super_admin')){
            return $this->respond(null, 'Unauthorized', 401);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:companies',
            'email' => 'required|email|unique:companies',
            'phone' => 'required|string|unique:companies',
            'website' => 'required|url',
        ]);

        if($validator->fails()){
            return $this->respond($validator->errors(), 'Validation Error', 422);
        }
        try{
            $company = Company::create($request->all());

            $manager = new User();
            $manager->name = 'Manager '. $company->name;
            $manager->email = 'manager@'. explode('@', $company->email)[1];
            $manager->password = bcrypt('password');
            $manager->company_id = $company->id;
            $manager->save();

            $managerRole = Role::where('name', 'manager')->first();
            $manager->assignRole($managerRole);

            // show default password
            $manager->password_default = 'password';
            $data = [
                'company' => $company,
                'manager' => $manager
            ];

            return $this->respond($data, 'Company created successfully', 201);
        } catch(\Exception $e){
            return $this->respond($e->getMessage(), 'Internal Server Error', 500);
        }
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
