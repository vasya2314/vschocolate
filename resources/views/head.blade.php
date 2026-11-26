<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        {{ config('app.name') }}
        @if(isset($title) && $title !== '')
            -
            {{ $title }}
        @endisset
    </title>

    {{-- Иконка: плитка шоколада с буквой V в цветах сайта (purple). SVG — для современных браузеров, ICO — запасной, PNG — для iOS --}}
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#9810fa">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
