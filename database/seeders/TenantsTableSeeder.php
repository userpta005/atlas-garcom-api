<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantsTableSeeder extends Seeder
{
    public function run(): void
    {
        User::withoutEvents(function () {
            try {
                $messages = [];
                $data = [
                    [
                        'person' => [
                            'nif' => '83643264000190',
                            'full_name' => 'Noah e Osvaldo Padaria Ltda',
                            'name' => 'Noah e Osvaldo Padaria',
                            'birthdate' => '2015-08-12',
                            'state_registration' => '522.799.920.946',
                            'city_registration' => null,
                            'email' => 'noah_padaria@hotmail.com',
                            'phone' => '11981278555',
                            'zip_code' => '06412150',
                            'address' => 'Rua Egeu',
                            'number' => 566,
                            'district' => 'Jardim Regina Alice',
                            'complement' => null,
                            'city_id' => 5179
                        ],
                        'tenant' => [
                            'signature_id' => 3,
                            'due_day_id' => 2,
                            'status' => \App\Enums\TenantStatus::ACTIVE
                        ],
                        'user' => [
                            'name' => 'Noah e Osvaldo Padaria',
                            'email' => 'noah_padaria@hotmail.com',
                            'password' => Hash::make('1234567o'),
                            'is_tenant' => \App\Enums\IsTenant::YES,
                            'status' => \App\Enums\Status::ACTIVE
                        ]
                    ]
                ];

                DB::beginTransaction();

                foreach ($data as $value) {
                    $person = Person::query()
                        ->updateOrCreate(
                            ['nif' => $value['person']['nif']],
                            $value['person']
                        );

                    $tenant = Tenant::query()
                        ->updateOrcreate(
                            ['person_id' => $person->id],
                            $value['tenant']
                        );

                    $user = User::query()
                        ->updateOrcreate(
                            ['person_id' => $person->id],
                            $value['user'] + ['tenant_id' => $tenant->id]
                        );

                    $user->assignRole(['users', 'roles']);

                    array_push($messages, "  {$tenant->id} - Tenant {$person->full_name} criado/atualizado.");
                }

                DB::commit();

                foreach ($messages as $message) {
                    $this->command->info($message);
                }
            } catch (\Throwable $th) {
                DB::rollBack();
                $this->command->alert($th->getMessage());
            }
        });
    }
}
