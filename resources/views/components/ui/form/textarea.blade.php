@props(['label', 'placeholder', 'value', 'type', 'name', 'required' => false])

@php
    $hasError = $errors->has($name);
@endphp

<label {{ $attributes->merge(["class" => "control-wrap block text-sm"]) }}>
    <span class="text-gray-700">{{ $label }} @if($required) <sup class="text-red-500">*</sup> @endif</span>
    <textarea
        class="
            appearance-none p-2.5 rounded-sm border block w-full mt-1 text-sm form-input
            focus:border-purple-400 focus:outline-none focus:shadow-outline-purple
            {{ $hasError ? 'border-red-500' : 'border-gray-300' }}
        "
        placeholder="{{ $placeholder }}"
        name="{{ $name }}"
        @if($required)
            required="{{ $required }}"
        @endif
    >{{ $value ?? old($name) }}</textarea>
    @error($name)
        <span class="block mt-1.5 text-red-500">{{ $message }}</span>
    @enderror
</label>
