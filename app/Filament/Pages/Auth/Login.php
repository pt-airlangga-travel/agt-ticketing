<?php

namespace App\Filament\Pages\Auth;

use Caresome\FilamentAuthDesigner\Pages\Auth\Login as BaseLogin;
// use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use MarcoGermani87\FilamentCaptcha\Forms\Components\CaptchaField;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                $this->getLoginFormComponent()->extraAttributes(['class' => 'py-1']),
                $this->getPasswordFormComponent()->extraAttributes(['class' => 'py-1']),
                CaptchaField::make('captcha'),
            ])
            ->statePath('data');
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('Email or Username')
            ->required()
            ->autofocus()
            ->prefixIcon('heroicon-o-user')
            ->extraAttributes([
                'class' => 'rounded-xl shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500',
                'tabindex' => 1,
            ])
            ->autocomplete();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login_type = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $login_type => $data['login'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            // 'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
            'data.login' => 'Login failed. Please check your email or Employee ID (NIP) and password.',
        ]);
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        $user = Auth::user()?->load('roles');

        if (! $user || ! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'data.login' => 'Akun Anda tidak aktif.',
            ]);
        }

        if ($user->roles->isEmpty()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'data.login' => 'Akun tidak memiliki akses yang ditetapkan.',
            ]);
        }

        $activeRoles = $user->roles->where('is_active', true);

        if ($activeRoles->isEmpty()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'data.login' => 'Tidak ada akses aktif yang ditetapkan untuk akun ini.',
            ]);
        }

        $selectedRole = $user->roles->firstWhere('id', $user->active_role_id);

        if (! $selectedRole || ! $selectedRole->is_active) {
            $user->update([
                'active_role_id' => $activeRoles->first()->id,
            ]);
        }

        return $response;
    }
}
