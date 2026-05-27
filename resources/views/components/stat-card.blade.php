@props([
    'label'   => '',
    'value'   => '0',
    'icon'    => null,
    'color'   => '#533AFD',
    'trend'   => null,
    'trendUp' => null,
    'detail'  => null,
])

<div class="ss-card" style="padding:24px; position:relative; overflow:hidden;">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <p style="font-size:12px; font-weight:500; color:#64748D; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">
                {{ $label }}
            </p>
            <p style="font-size:28px; font-weight:300; color:#061B31; line-height:1.1; margin-bottom:4px;">
                {{ $value }}
            </p>
            @if($detail)
            <p style="font-size:12px; color:#64748D; margin-top:6px;">{{ $detail }}</p>
            @endif
            @if($trend)
            <div class="flex items-center gap-1 mt-2">
                @if($trendUp)
                <svg class="w-3.5 h-3.5" style="color:#10B981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                <span style="font-size:12px; font-weight:500; color:#10B981;">{{ $trend }}</span>
                @else
                <svg class="w-3.5 h-3.5" style="color:#EF4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
                <span style="font-size:12px; font-weight:500; color:#EF4444;">{{ $trend }}</span>
                @endif
            </div>
            @endif
        </div>

        @if($icon)
        <div class="w-10 h-10 rounded flex items-center justify-center flex-shrink-0"
             :style="`background: {{ $color }}1A`">
            <div :style="`color: {{ $color }}; width: 20px; height: 20px`">
                {!! $icon !!}
            </div>
        </div>
        @endif
    </div>

    {{ $slot ?? '' }}
</div>
