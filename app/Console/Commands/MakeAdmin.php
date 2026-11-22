<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Creates an admin account, or promotes an existing one.
 * Admins are only ever created here, on the server, never through the website.
 */
class MakeAdmin extends Command
{
    protected $signature = 'nk:admin {email : The admin\'s email (used to sign in)} {--name= : Display name for a new account} {--revoke : Turn an admin back into a regular account}';

    protected $description = 'Create or promote a Next Keepers admin account';

    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('That is not a valid email address.');

            return self::FAILURE;
        }

        if ($email === Str::lower((string) config('nextkeepers.demo_email'))) {
            $this->error('The sample preview account can never be an admin.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($this->option('revoke')) {
            if (! $user?->isAdmin()) {
                $this->warn("{$email} is not an admin.");

                return self::SUCCESS;
            }
            $user->forceFill(['role' => 'buyer'])->save();
            $this->info("{$email} is no longer an admin.");

            return self::SUCCESS;
        }

        if ($user) {
            $user->forceFill(['role' => 'admin'])->save();
            $this->info("{$email} is now an admin. Their password is unchanged.");

            return self::SUCCESS;
        }

        $password = (string) $this->secret('Password for the new admin (at least 12 characters)');
        if (mb_strlen($password) < 12) {
            $this->error('The password must be at least 12 characters.');

            return self::FAILURE;
        }
        if ($password !== (string) $this->secret('Type it again')) {
            $this->error('The passwords do not match.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $this->option('name') ?: Str::before($email, '@'),
            'email' => $email,
            'password' => $password,
            'membership_tier' => 'Free account',
            'member_since' => now()->format('F Y'),
            'nda_status' => 'No NDA on file',
        ]);
        $user->forceFill(['role' => 'admin'])->save();

        $this->info("Admin account created for {$email}.");

        return self::SUCCESS;
    }
}
