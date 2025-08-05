@props([
    'label',
    'placeholder' => '',
    'value' => old($name ?? ''),
    'type' => 'text',
    'name',
    'disabled' => false,
    'required' => false,
    'isAmount' => false,
])

@php
    $hasError = $errors->has($name);
    $inputClasses = implode(' ', [
        'appearance-none p-2.5 rounded-sm border block w-full mt-1 text-sm form-input',
        'focus:border-purple-400 focus:outline-none focus:shadow-outline-purple',
        $hasError ? 'border-red-500' : 'border-gray-300',
        $disabled ? 'text-gray-400 border-gray-200' : '',
    ]);
@endphp

<label {{ $attributes->merge(['class' => 'control-wrap block text-sm']) }}>
    <span class="text-gray-700">
        {{ $label }}@if($required) <sup class="text-red-500">*</sup> @endif
    </span>

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        value="{{ $value }}"
        class="{{ $inputClasses }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($type === 'tel')
            x-data
            x-init="Inputmask('+7 (999) 999-99-99').mask($el)"
        @endif
        @if($isAmount)
            x-data
            x-init="Inputmask({
                alias: 'numeric',
                groupSeparator: ' ',
                autoGroup: true,
                digits: 0,
                digitsOptional: false,
                placeholder: '0',
                rightAlign: false
            }).mask($el)"
        @endif
    />

    @error($name)
        <span class="block mt-1.5 text-red-500">{{ $message }}</span>
    @enderror
</label>
