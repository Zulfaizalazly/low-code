<?php

use App\Models\User;
use App\Livewire\Runtime\FormEngine;
use Livewire\Livewire;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

Auth::login(User::where('email', 'staff@arrahnu.com')->first());

echo "Simulating Pledge Submission on Local DB...\n";

Livewire::test(FormEngine::class, ['featureKey' => 'new-pledge'])
    ->set('formData', [
        'name' => 'Live Test Customer',
        'ic_number' => 'LIVE-999',
        'email' => 'live@test.com',
        'amount' => 12000,
        'items' => [
            ['item_type' => 'Gold Bar', 'weight_grams' => 50, 'purity' => 999]
        ],
    ])
    ->call('submit');

echo "Pledge Submitted Successfully.\n";
