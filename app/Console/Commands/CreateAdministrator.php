<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdministrator extends Command
{
    protected $signature = 'panel:admin';
    protected $description = 'Crear el único administrador inicial sin credenciales predeterminadas';

    public function handle(): int
    {
        if (User::exists()) {
            $this->error('Ya existe un administrador. Este comando solo sirve para el alta inicial.');
            return self::FAILURE;
        }
        $name = $this->ask('Nombre del administrador');
        $email = $this->ask('Correo');
        $password = $this->secret('Contraseña (mínimo 12 caracteres, letras y números)');
        $confirmation = $this->secret('Repetí la contraseña');
        $validator = Validator::make(['name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirmation], [
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) { $this->error($error); }
            return self::FAILURE;
        }
        User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
        $this->info('Administrador creado. Ya podés iniciar sesión.');
        return self::SUCCESS;
    }
}
