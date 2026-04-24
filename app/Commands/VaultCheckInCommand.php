<?php namespace App\Commands; class VaultCheckInCommand extends BaseCommand { public function __construct(public ?string $item_id = null, public ?string $location = 'Safe A') {} }
