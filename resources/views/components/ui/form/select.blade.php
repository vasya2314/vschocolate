@props(['label', 'name', 'value' => false, 'multiple' => false, 'nullable' => false, 'options' => [], 'required' => false])

@php
    $hasError = $errors->has($name);

    if(!is_array($value))
    {
        $arr = [];
        $arr[] = $value;

        $value = $arr;
    }
@endphp

<div {{ $attributes->merge(["class" => "control-wrap block text-sm w-full"]) }}>
    <label class="block w-full">
        <span class="text-gray-700">{{ $label }} @if($required) <sup class="text-red-500">*</sup> @endif</span>
    </label>
    <select
        name="{{$name}}@if($multiple){{'[]'}}@endif"
        class="appearance-none p-2.5 rounded-sm border block w-full mt-1 text-sm form-select
        focus:border-purple-400 focus:outline-none focus:shadow-outline-purple
            {{ $hasError ? 'border-red-500' : 'border-gray-300' }}
        "
        @if($required)
            required="{{ $required }}"
        @endif
        @if($multiple)
            multiple
        @endif
    >
        @if($nullable)
            <option value="" @if(!$value) {{ 'selected' }} @endif>Не выбрано</option>
        @endif
        @foreach($options as $key => $val)
            <option value="{{ $key }}" @if((in_array($key, $value) || $val == old($name))) {{ 'selected' }} @endif>{{ $val }}</option>
        @endforeach
    </select>
    @error($name)
        <span class="block mt-1.5 text-red-500">{{ $message }}</span>
    @enderror
</div>
