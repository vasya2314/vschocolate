<!DOCTYPE html>
<html x-data="data()" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('head', compact('title'))
    <body>
        <div
            class="flex h-screen bg-gray-50"
            :class="{ 'overflow-hidden': isSideMenuOpen }"
        >
            @include('sidebar')
            <div class="flex flex-col flex-1 w-full">
                @include('header')
                <main class="h-full overflow-y-auto">
                    <div class="container px-6 mx-auto grid">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
