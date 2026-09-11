<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Creates an administration account interactively.
 *
 * The password is prompted for rather than passed as an argument so that it does
 * not reach the shell history or process list, and no installation ever ships
 * with a seeded account whose credentials are public knowledge.
 */
#[Signature('gsf:create-administrator {--email=} {--name=} {--role=}')]
#[Description('Create or update an administration account for the Global Support Foundation panel')]
class CreateAdministrator extends Command
{
    public function handle(): int
    {
        $name = $this->option('name') ?: text('Full name', required: true);
        $email = $this->option('email') ?: text('Email address', required: true);
        $role = $this->option('role') ?: select('Role', User::ROLES, 'admin');

        if (! array_key_exists($role, User::ROLES)) {
            $this->components->error('Unknown role: '.$role.'. Expected one of '.implode(', ', array_keys(User::ROLES)).'.');

            return self::FAILURE;
        }

        // Never accepted as a command argument: that would put the password into
        // shell history and the process list. Interactive runs prompt for it;
        // automated provisioning passes it in the environment instead.
        $secret = $this->input->isInteractive()
            ? password('Password', required: true)
            : (string) env('GSF_ADMIN_PASSWORD');

        if ($secret === '') {
            $this->components->error('No password supplied. Run this command interactively, or set GSF_ADMIN_PASSWORD in the environment.');

            return self::FAILURE;
        }

        try {
            validator(
                ['email' => $email, 'password' => $secret],
                ['email' => ['required', 'email'], 'password' => ['required', Password::min(12)->letters()->numbers()->symbols()->uncompromised()]],
            )->validate();
        } catch (ValidationException $exception) {
            foreach ($exception->validator->errors()->all() as $message) {
                $this->components->error($message);
            }

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->exists();

        $user = User::updateOrCreate(['email' => $email], [
            'name' => $name,
            'password' => Hash::make($secret),
            'role' => $role,
            'is_active' => true,
        ]);

        $this->components->info(sprintf(
            '%s %s as %s.',
            $existing ? 'Updated' : 'Created',
            $user->email,
            $user->role_label,
        ));

        return self::SUCCESS;
    }
}
