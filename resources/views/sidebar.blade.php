@php
    $isSupeAdmin = false;
    $currentUrl = \Illuminate\Support\Facades\Request::url();
@endphp

@auth
    @php
        $isSupeAdmin = \Illuminate\Support\Facades\Auth::user()->isSuperAdmin();
    @endphp
@endauth

<!-- Backdrop -->
<div
    x-show="isSideMenuOpen"
    x-transition:enter="transition ease-in-out duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in-out duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-10 flex items-end bg-[rgba(0,0,0,0.5)] bg-opacity-50 sm:items-center sm:justify-center"
></div>

<!-- Desktop sidebar -->
<aside
    class="z-20 hidden w-64 overflow-y-auto bg-white md:block flex-shrink-0"
>
    <div class="py-4 text-gray-500">
        <a
            class="ml-6 mb-6 block text-lg font-bold text-gray-800"
            href="{{ route('dashboard.index') }}"
        >
            {{ config('app.name') }}
        </a>
        @if(!empty($menu))
            <ul>
                @foreach($menu as $item)
                    <li class="relative px-6 py-3">
                        @if($currentUrl == $item['href'])
                            <span
                                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                                aria-hidden="true"
                            ></span>
                        @endif
                        <a
                            class="
                                {{ $currentUrl == $item['href'] ? 'text-gray-800' : '' }}
                                inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800
                            "
                            href="{{ $item['href'] }}"
                        >
                            <i class="{{ $item['icon'] }} text-lg"></i>
                            <span class="ml-4">{{ $item['name'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        @if(!empty($adminMenu) && $isSupeAdmin)
            <ul class="border-t border-gray-500">
                @foreach($adminMenu as $item)
                    <li class="relative px-6 py-3">
                        @if($currentUrl == $item['href'])
                            <span
                                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                                aria-hidden="true"
                            ></span>
                        @endif
                        <a
                            class="
                                {{ $currentUrl == $item['href'] ? 'text-gray-800' : '' }}
                                inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800
                            "
                            href="{{ $item['href'] }}"
                        >
                            <i class="{{ $item['icon'] }} text-lg"></i>
                            <span class="ml-4">{{ $item['name'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</aside>
<aside
    class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white md:hidden"
    x-show="isSideMenuOpen"
    x-transition:enter="transition ease-in-out duration-150"
    x-transition:enter-start="opacity-0 transform -translate-x-20"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in-out duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0 transform -translate-x-20"
    @click.away="closeSideMenu"
    @keydown.escape="closeSideMenu"
>
    <div class="py-4 text-gray-500">
        <a
            class="ml-6 mb-6 block text-lg font-bold text-gray-800"
            href="{{ route('dashboard.index') }}"
        >
            {{ config('app.name') }}
        </a>
        @if(!empty($menu))
            <ul>
                @foreach($menu as $item)
                    <li class="relative px-6 py-3">
                        @if($currentUrl == $item['href'])
                            <span
                                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                                aria-hidden="true"
                            ></span>
                        @endif
                        <a
                            class="
                                {{ $currentUrl == $item['href'] ? 'text-gray-800' : '' }}
                                inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800
                            "
                            href="{{ $item['href'] }}"
                        >
                            <i class="{{ $item['icon'] }} text-lg"></i>
                            <span class="ml-4">{{ $item['name'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        @if(!empty($adminMenu) && $isSupeAdmin)
            <ul class="border-t border-gray-500">
                @foreach($adminMenu as $item)
                    <li class="relative px-6 py-3">
                        @if($currentUrl == $item['href'])
                            <span
                                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                                aria-hidden="true"
                            ></span>
                        @endif
                        <a
                            class="
                                {{ $currentUrl == $item['href'] ? 'text-gray-800' : '' }}
                                inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800
                            "
                            href="{{ $item['href'] }}"
                        >
                            <i class="{{ $item['icon'] }} text-lg"></i>
                            <span class="ml-4">{{ $item['name'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</aside>
