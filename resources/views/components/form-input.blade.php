@props([
    'name',
    'label'       => '',
    'type'        => 'text',
    'placeholder' => '',
    'required'    => false,
    'hint'        => null,
    'value'       => null,
])

<div>
    @if($label)
    <label for="{{ $name }}" class="ss-label">
        {{ $label }}
        @if($required)
        <span style="color:#EF4444;"> *</span>
        @endif
    </label>
    @endif

    <input
        id="{{ $name }}"
        type="{{ $type }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        value="{{ $value ?? old($name) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'ss-input' . ($errors->has($name) ? ' error' : '')]) }}
    >

    @if($hint && !$errors->has($name))
    <p style="margin-top:6px; font-size:12px; color:#64748D;">{{ $hint }}</p>
    @endif

    @error($name)
    <p class="ss-error-msg">{{ $message }}</p>
    @enderror
</div>
