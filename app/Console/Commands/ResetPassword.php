<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password
                            {user : Username, email, atau ID user}
                            {--password= : Password baru (jika kosong akan ditanyakan)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset password user berdasarkan username, email, atau ID';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = $this->argument('user');

        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->when(ctype_digit($identifier), fn ($q) => $q->orWhere('id', $identifier))
            ->first();

        if (! $user) {
            $this->error("User '{$identifier}' tidak ditemukan.");

            return self::FAILURE;
        }

        $password = $this->option('password');

        if (! $password) {
            $password = $this->secret('Password baru');
            $confirm = $this->secret('Konfirmasi password baru');

            if ($password !== $confirm) {
                $this->error('Konfirmasi password tidak cocok.');

                return self::FAILURE;
            }
        }

        if (blank($password)) {
            $this->error('Password tidak boleh kosong.');

            return self::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password untuk user '{$user->username}' ({$user->name}) berhasil direset.");

        return self::SUCCESS;
    }
}
