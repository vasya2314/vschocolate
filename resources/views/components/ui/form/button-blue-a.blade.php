@props(['label', 'href'])

<a
    {{ $attributes->merge(["class" => "block text-center cursor-pointer block px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none"]) }}
    href="{{ $href }}"
>
    {{ $label }}
</a>
