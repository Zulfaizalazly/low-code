<?php namespace App\Commands; class PostGLEntryCommand extends BaseCommand { public function __construct(public ?float $amount = null, public ?string $reference = null) {} }
