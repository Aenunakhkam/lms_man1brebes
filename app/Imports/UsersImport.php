<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    protected $roleName;
    protected $roleId;

    public function __construct($roleName)
    {
        $this->roleName = $roleName;
        $role = Role::where('name', $roleName)->first();
        $this->roleId = $role ? $role->id : null;
    }

    public function model(array $row)
    {
        if (!$this->roleId || !isset($row['nama']) || !isset($row['email'])) {
            return null;
        }

        $defaultPassword = !empty($row['password']) ? $row['password'] : 'password123';
        $userData = [
            'name'         => $row['nama'],
            'email'        => $row['email'],
            'role_id'      => $this->roleId,
            'address'      => $row['alamat'] ?? null,
            'phone'        => $row['telepon'] ?? null,
            'password'     => Hash::make($defaultPassword),
            'password_raw' => $defaultPassword,
            'is_active'    => true,
        ];

        if ($this->roleName === 'siswa') $userData['nis'] = $row['nis'] ?? null;
        if ($this->roleName === 'guru') $userData['nip'] = $row['nip'] ?? null;

        return User::updateOrCreate(
            ['email' => $row['email']],
            $userData
        );
    }
}
