<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {--name=Admin}';

    protected $description = 'Buat akun administrator (kata sandi dimasukkan secara interaktif)';

    public function handle(): int
    {
        $email = $this->argument('email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
            $this->error('Email tidak valid atau sudah digunakan.');

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi (minimal 12 karakter)');
        if (strlen($password ?? '') < 12 || $password !== $this->secret('Ulangi kata sandi')) {
            $this->error('Kata sandi tidak cocok atau terlalu pendek.');

            return self::FAILURE;
        }

        User::create([
            'name' => $this->option('name'),
            'email' => $email,
            'password' => Hash::make($password),
        ]);
        $this->info('Admin berhasil dibuat.');

        return self::SUCCESS;
    }
}
