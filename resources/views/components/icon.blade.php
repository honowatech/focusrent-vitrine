@props(['name'])

@php
    $file = resource_path('svg/'.$name.'.svg');
    $svg = is_file($file) ? file_get_contents($file) : '';
    $svg = preg_replace('/<!--.*?-->/s', '', $svg) ?? $svg;
    $attr = $attributes
        ->class('icon-svg')
        ->merge(['aria-hidden' => 'true', 'focusable' => 'false'])
        ->getAttributes();
    $attrStr = collect($attr)
        ->map(fn ($value, $key) => $key.'="'.e($value).'"')
        ->implode(' ');
    $svg = preg_replace('/<svg\b/', '<svg fill="currentColor" '.$attrStr, $svg, 1);
@endphp
{!! $svg !!}
