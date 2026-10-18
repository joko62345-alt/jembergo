<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_rejects_invalid_name_email_and_phone(): void
    {
        $customer = Customer::create([
            'nama' => 'Nama Awal',
            'email' => 'customer@gmail.com',
            'no_hp' => '081234567890',
            'alamat' => 'Jember',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $validData = [
            'nama' => 'Nama Baru',
            'email' => 'customer@gmail.com',
            'no_hp' => '081234567890',
        ];
        $invalidInputs = [
            ['field' => 'nama', 'value' => 'Nama1'],
            ['field' => 'nama', 'value' => str_repeat('A', 101)],
            ['field' => 'email', 'value' => 'customer@example.com'],
            ['field' => 'no_hp', 'value' => '08123abc'],
            ['field' => 'no_hp', 'value' => '123456789'],
            ['field' => 'no_hp', 'value' => '1234567890123'],
        ];

        foreach ($invalidInputs as ['field' => $field, 'value' => $value]) {
            $response = $this->withSession([
                'jg_user_id' => $customer->id_customer,
                'jg_role' => 'CUSTOMER',
            ])->from(route('customer.profile'))->put(route('customer.profile.update'), [
                ...$validData,
                $field => $value,
            ]);

            $response->assertRedirect(route('customer.profile'))
                ->assertSessionHasErrors($field);
        }

        $this->assertDatabaseHas('customer', [
            'id_customer' => $customer->id_customer,
            'nama' => 'Nama Awal',
            'email' => 'customer@gmail.com',
            'no_hp' => '081234567890',
        ]);
    }

    public function test_profile_accepts_valid_personal_information(): void
    {
        $customer = Customer::create([
            'nama' => 'Nama Awal',
            'email' => 'customer@gmail.com',
            'no_hp' => '081234567890',
            'alamat' => 'Jember',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);

        foreach (['0812345678', '081234567890', '+6281234567890'] as $phoneNumber) {
            $response = $this->withSession([
                'jg_user_id' => $customer->id_customer,
                'jg_role' => 'CUSTOMER',
            ])->put(route('customer.profile.update'), [
                'nama' => 'Siti Aminah',
                'email' => 'siti@gmail.com',
                'no_hp' => $phoneNumber,
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('customer', [
                'id_customer' => $customer->id_customer,
                'nama' => 'Siti Aminah',
                'email' => 'siti@gmail.com',
                'no_hp' => $phoneNumber,
            ]);
        }
    }

    public function test_profile_changes_are_shared_between_website_and_mobile_api(): void
    {
        $customer = Customer::create([
            'nama' => 'Nama Awal',
            'email' => 'customer@gmail.com',
            'no_hp' => '081234567890',
            'alamat' => 'Jember',
            'password' => bcrypt('secret123'),
            'auth_provider' => 'manual',
        ]);
        $session = [
            'jg_user_id' => $customer->id_customer,
            'jg_user_name' => $customer->nama,
            'jg_role' => 'CUSTOMER',
        ];

        $this->withSession($session)
            ->put(route('customer.profile.update'), [
                'nama' => 'Website Name',
                'email' => 'website@gmail.com',
                'no_hp' => '081234567891',
            ])
            ->assertRedirect();

        $token = $customer->createToken('profile-sync-test')->plainTextToken;
        $this->withToken($token)
            ->getJson('/api/v1/customer/profil')
            ->assertOk()
            ->assertJsonPath('data.nama', 'Website Name')
            ->assertJsonPath('data.email', 'website@gmail.com')
            ->assertJsonPath('data.no_hp', '081234567891');

        $this->withToken($token)
            ->putJson('/api/v1/customer/profil', [
                'nama' => 'Mobile Name',
                'email' => 'mobile@gmail.com',
                'no_hp' => '+6281234567891',
                'password' => 'mobileSecret123',
                'password_confirmation' => 'mobileSecret123',
            ])
            ->assertOk()
            ->assertJsonPath('data.nama', 'Mobile Name')
            ->assertJsonPath('data.email', 'mobile@gmail.com')
            ->assertJsonPath('data.no_hp', '+6281234567891')
            ->assertJsonMissingPath('data.password');

        $this->withSession($session)
            ->get(route('customer.profile'))
            ->assertOk()
            ->assertSee('value="Mobile Name"', false)
            ->assertSee('value="mobile@gmail.com"', false)
            ->assertSee('Halo, <strong', false)
            ->assertSee('Mobile Name</strong>', false);

        $updatedCustomer = $customer->fresh();
        $this->assertSame('Mobile Name', $updatedCustomer->nama);
        $this->assertTrue(Hash::check('mobileSecret123', $updatedCustomer->password));
    }
}
