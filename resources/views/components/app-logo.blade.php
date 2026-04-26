@props(['size' => 'md', 'class' => ''])

@php
$sizes = [
    'xs' => 'w-6 h-6',
    'sm' => 'w-8 h-8',
    'md' => 'w-9 h-9',
    'lg' => 'w-12 h-12',
    'xl' => 'w-16 h-16',
    '2xl' => 'w-24 h-24',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<img src="{{ asset('images/arrahnumation.webp') }}" alt="Arrahnu" {{ $attributes->merge(['class' => "$sizeClass $class"]) }}>
