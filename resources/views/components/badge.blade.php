@props([
    'type'  => 'neutral',
    'size'  => 'sm',
])

@php
$classes = match($type) {
    'success' => 'badge-success',
    'error', 'alert', 'danger' => 'badge-alert',
    'warning' => 'badge-warning',
    'info', 'purple' => 'badge-info',
    'orange'  => 'badge-orange',
    default   => 'badge-neutral',
};
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
