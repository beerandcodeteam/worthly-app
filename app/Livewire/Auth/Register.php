<?php

namespace App\Livewire\Auth;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\ValidationException;
use App\Services\Worthly\WorthlyApiClient;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function submit(WorthlyApiClient $api, SecureTokenStorage $tokens): mixed
    {
        $this->resetErrorBag();

        try {
            $data = $api->post('/api/register', [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ]);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                if (is_array($messages) && isset($messages[0])) {
                    $this->addError($field, (string) $messages[0]);
                }
            }

            return null;
        }

        $token = (string) ($data['token'] ?? '');

        if ($token === '') {
            $this->addError('email', 'Registration failed. Please try again.');

            return null;
        }

        $tokens->put($token);

        if (isset($data['user']) && is_array($data['user'])) {
            Cache::put('auth.user', $data['user']);
        }

        return $this->redirectRoute('home', navigate: true);
    }

    #[Layout('components.layouts.guest')]
    #[Title('Create your Worthly account')]
    public function render(): mixed
    {
        return view('livewire.auth.register');
    }
}
