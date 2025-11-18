<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin 
                            {--name= : The name of the admin user}
                            {--email= : The email address of the admin user}
                            {--password= : The password for the admin user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating Admin User...');
        $this->newLine();

        // Get or ask for name
        $name = $this->option('name') ?: $this->ask('Enter admin name', 'Admin User');

        // Get or ask for email
        $email = $this->option('email') ?: $this->ask('Enter admin email', 'admin@ubiqent.com');

        // Validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first('email'));
            return self::FAILURE;
        }

        // Get or ask for password
        $password = $this->option('password') ?: $this->secret('Enter admin password');

        if (!$password) {
            $this->error('Password is required!');
            return self::FAILURE;
        }

        // Confirm password if not provided via option
        if (!$this->option('password')) {
            $passwordConfirm = $this->secret('Confirm password');
            
            if ($password !== $passwordConfirm) {
                $this->error('Passwords do not match!');
                return self::FAILURE;
            }
        }

        // Validate password strength
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long!');
            return self::FAILURE;
        }

        // Create the admin user
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
                'onboarding_completed' => true,
            ]);

            $this->newLine();
            $this->info('✓ Admin user created successfully!');
            $this->newLine();
            
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $user->id],
                    ['Name', $user->name],
                    ['Email', $user->email],
                    ['Role', $user->role],
                    ['Created At', $user->created_at->format('Y-m-d H:i:s')],
                ]
            );

            $this->newLine();
            $this->comment('You can now login at: https://ubiqent.com/backend/admin/login');
            
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to create admin user: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
