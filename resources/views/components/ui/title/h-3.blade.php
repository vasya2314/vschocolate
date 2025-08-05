@props(['label'])

<h3
    {{ $attributes->merge(["class" => "text-lg font-semibold text-gray-700 mb-4"]) }}
>
    {{ $label }}
</h3>
