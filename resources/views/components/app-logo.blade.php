@props(['size' => 'md', 'class' => ''])

@php
$sizes = [
    'xs' => 'h-6 w-auto',
    'sm' => 'h-8 w-auto',
    'md' => 'h-9 w-auto',
    'lg' => 'h-12 w-auto',
    'xl' => 'h-16 w-auto',
    '2xl' => 'h-24 w-auto',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<img src="{{ asset('images/KOPSYA-final-logo-tagline-OL2-Copy-175x96.png') }}" alt="Kopsya Ar-Rahnu" {{ $attributes->merge(['class' => "$sizeClass $class"]) }}>
