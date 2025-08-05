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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
