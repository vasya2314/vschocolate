@props([
    'data',
])

<div class="grid px-4 py-3 text-xs font-semibold tracking-wide text-gray-500 uppercase border-t dark:border-gray-700 bg-gray-50 sm:grid-cols-9 dark:text-gray-400 dark:bg-gray-800">
    <span class="flex items-center col-span-3">
        Показано {{ $data->firstItem() }}-{{ $data->lastItem() }} из {{ $data->total() }}
    </span>
    <span class="col-span-2"></span>
    <!-- Пагинация -->
    <span class="flex col-span-4 mt-2 sm:mt-auto sm:justify-end">
        <nav aria-label="Table navigation">
            <ul class="inline-flex items-center">
                <!-- Кнопка Previous -->
                <li>
                    <button
                        class="cursor-pointer px-3 py-1 rounded-md rounded-l-lg focus:outline-none focus:shadow-outline-purple"
                        aria-label="Previous"
                        @if (!$data->onFirstPage())
                            onclick="window.location='{{ $data->previousPageUrl() }}'"
                        @endif
                    >
                        <svg class="w-4 h-4 fill-current" aria-hidden="true" viewBox="0 0 20 20">
                            <path
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                                fill-rule="evenodd"
                            ></path>
                        </svg>
                    </button>
                </li>
                @foreach ($data->getUrlRange(1, $data->lastPage()) as $page => $url)
                    <li>
                        <button
                            class="cursor-pointer px-3 py-1 {{ $data->currentPage() == $page ? 'text-white bg-purple-600 border border-purple-600' : '' }} rounded-md focus:outline-none focus:shadow-outline-purple"
                            onclick="window.location='{{ $url }}'"
                        >
                            {{ $page }}
                        </button>
                    </li>
                @endforeach
                <li>
                    <button
                        class="cursor-pointer px-3 py-1 rounded-md rounded-r-lg focus:outline-none focus:shadow-outline-purple"
                        aria-label="Next"
                        @if ($data->hasMorePages())
                            onclick="window.location='{{ $data->nextPageUrl() }}'"
                        @endif
                    >
                        <svg class="w-4 h-4 fill-current" aria-hidden="true" viewBox="0 0 20 20">
                            <path
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"
                                fill-rule="evenodd"
                            ></path>
                        </svg>
                    </button>
                </li>
            </ul>
        </nav>
    </span>
</div>
