@props([
    'name',
    'title'       => '',
    'maxWidth'    => '520px',
])

<div
    x-data="{ open: false }"
    x-on:open-modal.window="$event.detail === '{{ $name }}' ? open = true : null"
    x-on:close-modal.window="$event.detail === '{{ $name }}' ? open = false : null"
    x-on:keydown.escape.window="open = false"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:40;"
    ></div>

    {{-- Panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        style="position:fixed; inset:0; display:flex; align-items:center; justify-content:center; padding:24px; z-index:50;"
    >
        <div :style="{ maxWidth: '{{ $maxWidth }}' }"
             style="width:100%; background:#FFFFFF; border:1px solid #D4DEE9; border-radius:5px; box-shadow:0px 20px 60px rgba(0,0,0,0.15); overflow:hidden;">

            {{-- Header --}}
            @if($title)
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
                <h4 style="font-size:16px; font-weight:500; color:#061B31;">{{ $title }}</h4>
                <button @click="open = false"
                    style="background:none; border:none; cursor:pointer; color:#64748D; padding:4px; border-radius:4px; display:flex; align-items:center; justify-content:center; transition:background-color 100ms ease;"
                    onmouseover="this.style.backgroundColor='#F8FAFC'; this.style.color='#061B31';"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#64748D';">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @endif

            {{-- Content --}}
            <div class="px-6 py-5">
                {{ $slot }}
            </div>

            {{-- Footer (optional slot) --}}
            @isset($footer)
            <div class="px-6 py-4 flex items-center justify-end gap-3" style="border-top:1px solid #E5EDF5; background:#F8FAFC;">
                {{ $footer }}
            </div>
            @endisset
        </div>
    </div>
</div>
