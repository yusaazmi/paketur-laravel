<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
    }

    /** @test */
    public function super_admin_can_create_company_and_manager()
    {
        $superAdmin = User::factory()->asSuperAdmin()->create();
        $headers = ['Authorization' => "Bearer {$this->getToken($superAdmin)}"];
        
        $response = $this->postJson('/api/companies', [
            'name' => 'New Company',
            'email' => 'test@gmail.com',
            'phone' => '08128371827',
            'website' => 'https://test.com',
        ], $headers);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => 201,
                     'message' => 'Company created successfully',
                 ])
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                        'company' => ['id', 'name', 'email', 'phone', 'website'],
                     ]
                 ]);
    }

    /** @test */
    public function manager_can_view_all_managers_and_employees()
    {
        $manager = User::factory()->asManager()->create();
        $employee = User::factory()->asEmployee()->create(['company_id' => $manager->company_id]);

        $headers = ['Authorization' => "Bearer {$this->getToken($manager)}"];

        $response = $this->getJson('/api/users?page=1&page_size=10', $headers);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 200,
                     'message' => 'success',
                 ])
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [  
                        'data' => [
                            '*' => [
                                'id',
                                'name', 
                                'email',
                                'email_verified_at',
                                'phone',
                                'address', 
                                'company_id', 
                                'created_at',
                                'updated_at',
                                'deleted_at',
                                'company' => [
                                    'id',
                                    'name',
                                    'email',
                                    'phone',
                                    'website',
                                    'created_at',
                                    'updated_at',
                                    'deleted_at',
                                ],
                            ],
                        ],
                     ]
                 ]);
    }

    /** @test */
    public function manager_can_update_own_profile()
    {
        $manager = User::factory()->asManager()->create();
        $headers = ['Authorization' => "Bearer {$this->getToken($manager)}"];

        $response = $this->putJson("/api/update-profile", [
            'name' => 'Updated Manager'
        ], $headers);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 200,
                     'message' => 'Profile updated successfully',
                 ]);
    }

    /** @test */
    public function manager_can_manage_employees_in_same_company()
    {
        $manager = User::factory()->asManager()->create();
        $employee = User::factory()->asEmployee()->create(['company_id' => $manager->company_id]);

        $headers = ['Authorization' => "Bearer {$this->getToken($manager)}"];

        // Update Employee
        $response = $this->putJson("/api/users/{$employee->id}", ['name' => 'Updated Employee'], $headers);
        $response->assertStatus(200)
                 ->assertJson(['status' => 200, 'message' => 'User updated successfully']);

        // Soft Delete Employee
        $response = $this->deleteJson("/api/users/{$employee->id}", [], $headers);
        $response->assertStatus(200)
                 ->assertJson(['status' => 200, 'message' => 'User deleted successfully']);

        // Restore Employee
        $response = $this->patchJson("/api/users/{$employee->id}/restore", [], $headers);
        $response->assertStatus(200)
                 ->assertJson(['status' => 200, 'message' => 'User restored successfully']);

        // Force Delete Employee
        $response = $this->deleteJson("/api/users/{$employee->id}/force-delete", [], $headers);
        $response->assertStatus(200)
                 ->assertJson(['status' => 200, 'message' => 'User permanently deleted']);
    }

    /** @test */
    public function employee_can_view_other_employees()
    {
        $employee1 = User::factory()->asEmployee()->create();
        $employee2 = User::factory()->asEmployee()->create(['company_id' => $employee1->company_id]);

        $headers = ['Authorization' => "Bearer {$this->getToken($employee1)}"];

        $response = $this->getJson('/api/users?page=1&page_size=10', $headers);
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 200,
                     'message' => 'success',
                     ])
                     ->assertJsonStructure([
                         'status',
                         'message',
                         'data' => [  
                            'data' => [
                                '*' => [
                                    'id',
                                    'name', 
                                    'email',
                                    'email_verified_at',
                                    'phone',
                                    'address', 
                                    'company_id', 
                                    'created_at',
                                    'updated_at',
                                    'deleted_at',
                                    'company' => [
                                        'id',
                                        'name',
                                        'email',
                                        'phone',
                                        'website',
                                        'created_at',
                                        'updated_at',
                                        'deleted_at',
                                    ],
                                ],
                            ],
                         ]
                     ]);
    }

    private function getToken($user)
    {
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        return $response->json('data.token');
    }
}