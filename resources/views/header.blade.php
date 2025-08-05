<header class="z-10 py-4 bg-white shadow-md">
    <div
        class="container flex items-center justify-between h-full px-6 mx-auto text-purple-600"
    >
        <!-- Mobile hamburger -->
        <button
            class="p-1 mr-5 -ml-1 rounded-md md:hidden focus:outline-none focus:shadow-outline-purple"
            @click="toggleSideMenu"
            aria-label="Menu"
        >
            <svg
                class="w-6 h-6"
                aria-hidden="true"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path
                    fill-rule="evenodd"
                    d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                    clip-rule="evenodd"
                ></path>
            </svg>
        </button>
        <ul class="flex items-center flex-shrink-0 space-x-6 ml-auto">
            <!-- Profile menu -->
            <li class="relative">
                <button
                    class="align-middle rounded-full focus:shadow-outline-purple focus:outline-none"
                    @click="toggleProfileMenu"
                    @keydown.escape="closeProfileMenu"
                    aria-label="Account"
                    aria-haspopup="true"
                >
                    <div class="w-8 h-8 bg-purple-600 rounded-[50%] text-white flex items-center justify-center cursor-pointer">
                        @auth
                            <span>{{ strtoupper(mb_substr(Auth::user()->email, 0, 1)) }}</span>
                        @else
                            <span>-</span>
                        @endauth
                    </div>
                </button>
                <template x-if="isProfileMenuOpen">
                    <ul
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click.away="closeProfileMenu"
                        @keydown.escape="closeProfileMenu"
                        class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md"
                        aria-label="submenu"
                    >
                        <li class="flex">
                            <a
                                class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800"
                                href="#"
                            >
                                <i class="bi bi-person mr-3 text-lg"></i>
                                <span>Профиль</span>
                            </a>
                        </li>
                        <li class="flex">
                            <a
                                class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800"
                                href="{{ route('logout') }}"
                            >
                                <i class="bi bi-door-closed mr-3 text-lg"></i>
                                <span>Выйти</span>
                            </a>
                        </li>
                    </ul>
                </template>
            </li>
        </ul>
    </div>
</header>

<x-alert />
