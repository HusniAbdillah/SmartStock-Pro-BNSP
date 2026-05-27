@props([
    'title',
    'description' => null,
])

<div class="flex items-start justify-between gap-4 mb-8">
    <div>
        <h3 class="page-title">{{ $title }}</h3>
        @if($description)
        <p class="page-subtitle">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
    <div class="flex items-center gap-3 flex-shrink-0">
        {{ $actions }}
    </div>
    @endisset
</div>
