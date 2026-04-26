<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class LoginModal extends Component
{
    /**
     * SSO Login Gate Modal (Req 2, 3, 7)
     *
     * Handles authentication via a modal popup on the landing page.
     * Unauthenticated users see this modal when clicking any workspace card.
     * On successful login, redirects to the target workspace URL.
     */

    // Workspace card → target route mapping
    const WORKSPACE_ROUTES = [
        'teller'  => '/portal/operations/new-pledge',
        'manager' => '/branch',
        'studio'  => '/studio',
        'admin'   => '/admin',
    ];

    // Form inputs
    public string $email = '';
    public string $password = '';

    // Modal state
    public string $targetUrl = '/';
    public bool $showModal = false;
    public bool $isLoading = false;

    /**
     * Open the login modal with the target workspace URL.
     * Resets form fields and displays the modal.
     */
    #[On('openLoginModal')]
    public function openLoginModal(string $targetUrl = '/'): void
    {
        $this->targetUrl = $targetUrl;
        $this->reset(['email', 'password']);
        $this->resetErrorBag();
        $this->isLoading = false;
        $this->showModal = true;
    }

    /**
     * Attempt authentication with submitted credentials.
     * On success: redirect to stored target URL.
     * On failure: show inline error message.
     */
    public function login(): void
    {
        $this->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:1'],
        ]);

        $this->isLoading = true;

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            $this->redirect($this->targetUrl);
            return;
        }

        $this->isLoading = false;
        $this->addError('email', 'Invalid credentials. Please try again.');
    }

    /**
     * Close the modal and reset all state.
     */
    public function closeModal(): void
    {
        $this->reset(['email', 'password', 'targetUrl', 'showModal', 'isLoading']);
        $this->resetErrorBag();
        $this->showModal = false;
    }

    public function render(): View
    {
        return view('livewire.login-modal');
    }
}
