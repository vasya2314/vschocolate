@props(['label'])

<h2
    {{ $attributes->merge(["class" => "mb-4 text-2xl font-semibold text-gray-700"]) }}
>
    {{ $label }}
</h2>
