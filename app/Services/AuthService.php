<?php

namespace App\Services;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\CreateUserDTO;
use App\DTOs\LoginDTO;
use App\DTOs\SocialLoginDTO;
use App\Models\User;
use App\Models\Package;
use App\Models\Subscription;
use App\Repositories\Eloquent\EloquentDeviceRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AccountRepositoryInterface $accountRepository,
        private EloquentDeviceRepository $deviceRepository,
    ) {
    }

    /**
     * Register a new user with account.
     */
    public function register(CreateUserDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            // Create user
            $userData = $dto->toArray();
            $userData['password'] = Hash::make($dto->password);
            
            $user = $this->userRepository->create($userData);

            // Get or create free package first
            $freePackage = $this->getOrCreateFreePackage();

            // Create default account for user with package_id
            $account = $this->accountRepository->create([
                'user_id' => $user->id,
                'package_id' => $freePackage->id,
                'account_name' => $user->name . "'s Account",
                'status' => 'active',
            ]);

            // Create default profile
            $account->profiles()->create([
                'name' => $user->name,
                'is_primary' => true,
                'maturity_rating' => 'pg13',
            ]);

            // Auto-subscribe to free package
            $this->subscribeToFreePackage($account, $freePackage);

            // Fire registered event
            event(new Registered($user));

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'account' => $account->load('profiles'),
                'token' => $token,
            ];
        });
    }

    /**
     * Login with email and password.
     */
    public function login(LoginDTO $dto, ?array $deviceData = null): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Register or update device if device data provided
        $device = null;
        if ($deviceData) {
            $device = $this->deviceRepository->register($user->id, $deviceData);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load user's primary account
        $account = $this->accountRepository->getPrimaryAccount($user->id);

        return [
            'user' => $user,
            'account' => $account?->load('profiles'),
            'device' => $device,
            'token' => $token,
        ];
    }

    /**
     * Login or register with social provider.
     */
    public function loginWithSocial(SocialLoginDTO $dto, ?array $deviceData = null): array
    {
        return DB::transaction(function () use ($dto, $deviceData) {
            // Try to find existing user by social provider
            $user = $this->userRepository->findBySocialProvider($dto->provider, $dto->providerId);

            if (!$user) {
                // Try to find by email
                $user = $this->userRepository->findByEmail($dto->email);

                if ($user) {
                    // Update existing user with social provider info
                    $user->update([
                        'social_provider' => $dto->provider,
                        'social_provider_id' => $dto->providerId,
                        'avatar_url' => $dto->avatarUrl,
                    ]);
                } else {
                    // Create new user
                    $user = $this->userRepository->create([
                        'name' => $dto->name,
                        'email' => $dto->email,
                        'social_provider' => $dto->provider,
                        'social_provider_id' => $dto->providerId,
                        'avatar_url' => $dto->avatarUrl,
                        'password' => Hash::make(uniqid()), // Random password
                        'email_verified_at' => now(),
                    ]);

                    // Get or create free package first
                    $freePackage = $this->getOrCreateFreePackage();

                    // Create default account with package_id
                    $account = $this->accountRepository->create([
                        'user_id' => $user->id,
                        'package_id' => $freePackage->id,
                        'account_name' => $user->name . "'s Account",
                        'status' => 'active',
                    ]);

                    // Create default profile
                    $account->profiles()->create([
                        'name' => $user->name,
                        'is_primary' => true,
                        'maturity_rating' => 'pg13',
                    ]);

                    // Auto-subscribe to free package
                    $this->subscribeToFreePackage($account, $freePackage);
                }
            }

            // Register or update device if device data provided
            $device = null;
            if ($deviceData) {
                $device = $this->deviceRepository->register($user->id, $deviceData);
            }

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Load user's primary account
            $account = $this->accountRepository->getPrimaryAccount($user->id);

            return [
                'user' => $user,
                'account' => $account?->load('profiles'),
                'device' => $device,
                'token' => $token,
            ];
        });
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(User $user): bool
    {
        // Revoke all tokens
        $user->tokens()->delete();

        return true;
    }

    /**
     * Get authenticated user with account.
     */
    public function me(User $user): array
    {
        $account = $this->accountRepository->getPrimaryAccount($user->id);

        return [
            'user' => $user,
            'account' => $account?->load('profiles'),
        ];
    }

    /**
     * Send password reset link.
     */
    public function sendResetLink(string $email): string
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['We could not find a user with that email address.'],
            ]);
        }

        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    /**
     * Reset password.
     */
    public function resetPassword(string $email, string $password, string $token): string
    {
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $token,
            ],
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    /**
     * Update trial end date for user.
     */
    public function startTrial(int $userId, int $days = 30): bool
    {
        $trialEndsAt = now()->addDays($days);
        
        return $this->userRepository->updateTrialEndDate($userId, $trialEndsAt);
    }

    /**
     * End trial for user.
     */
    public function endTrial(int $userId): bool
    {
        return $this->userRepository->updateTrialEndDate($userId, now());
    }

    /**
     * Get or create free package.
     */
    private function getOrCreateFreePackage(): Package
    {
        // Find free package
        $freePackage = Package::where('key', 'free')
            ->where('is_active', true)
            ->first();

        if (!$freePackage) {
            // If no free package exists, create one
            $freePackage = Package::create([
                'key' => 'free',
                'title' => 'Free Plan',
                'description' => 'Basic streaming with limited content',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'trial_days' => 0,
                'max_profiles' => 1,
                'features' => json_encode([
                    'Limited content library',
                    'SD quality',
                    'With ads',
                    '1 device',
                ]),
                'is_active' => true,
            ]);
        }

        return $freePackage;
    }

    /**
     * Subscribe account to free package.
     */
    private function subscribeToFreePackage($account, Package $freePackage): void
    {
        // Create subscription
        Subscription::create([
            'account_id' => $account->id,
            'package_id' => $freePackage->id,
            'provider' => 'system',
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'started_at' => now(),
            'current_period_start' => now(),
            'current_period_end' => now()->addYears(10), // 10 years for free package
        ]);
    }
}
