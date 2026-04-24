<?php namespace App\Commands; class AmlaCheckCommand extends BaseCommand { public function __construct(public ?string $ic_number = null, public ?string $name = null) {} }
