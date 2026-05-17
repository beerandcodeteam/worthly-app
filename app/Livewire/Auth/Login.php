<?php

namespace App\Livewire\Auth;

use App\Contracts\SecureTokenStorage;
use App\Services\Worthly\Exceptions\UnauthorizedException;
use App\Services\Worthly\Exceptions\ValidationException;
use App\Services\Worthly\WorthlyApiClient;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public ?string $formError = null;

    public bool $forgotPasswordEnabled = false;

    public bool $ssoEnabled = false;

    public function submit(WorthlyApiClient $api, SecureTokenStorage $tokens): mixed
    {
        $this->resetErrorBag();
        $this->formError = null;

        try {
            $data = $api->post('/api/login', [
                'email' => $this->email,
                'password' => $this->password,
            ]);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                if (is_array($messages) && isset($messages[0])) {
                    $this->addError($field, (string) $messages[0]);
                }
            }

            return null;
        } catch (UnauthorizedException) {
            $this->formError = 'Invalid email or password.';

            return null;
        }

        $token = (string) ($data['token'] ?? '');

        if ($token === '') {
            $this->formError = 'Invalid email or password.';

            return null;
        }

        $tokens->put($token);

        if (isset($data['user']) && is_array($data['user'])) {
            Cache::put('auth.user', $data['user']);
        }

        return $this->redirectRoute('home', navigate: true);
    }

    #[Layout('components.layouts.guest')]
    #[Title('Sign in to Worthly')]
    public function render(): mixed
    {
        return view('livewire.auth.login');
    }
}
