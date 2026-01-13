<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin 
                            {--name=Admin : Admin user name}
                            {--email= : Admin email address}
                            {--password= : Admin password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user account';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->option('name') ?: $this->ask('Admin Name', 'Admin');
        $email = $this->option('email') ?: $this->ask('Admin Email');
        $password = $this->option('password') ?: $this->secret('Admin Password');

        if (empty($email)) {
            $this->error('Email is required!');
            return Command::FAILURE;
        }

        if (empty($password)) {
            $this->error('Password is required!');
            return Command::FAILURE;
        }

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email '{$email}' already exists!");
            return Command::FAILURE;
        }

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
            ]);

            $this->info("✓ Admin user created successfully!");
            $this->line("  Name: {$user->name}");
            $this->line("  Email: {$user->email}");
            $this->line("  Role: {$user->role}");
            $this->line("  Status: {$user->status}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create admin user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
