@if(!empty($items))
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        @foreach($items as $item)
            <!-- Card -->
            <div
                class="flex items-center p-4 bg-white rounded-lg shadow-xs"
            >
                <div
                    class="p-3 mr-4 {{ $item['classes'] }} rounded-full"
                >
                    <i class="{{ $item['icon'] }} w-5 h-5 flex justify-center items-center text-lg"></i>
                </div>
                <div>
                    <p
                        class="mb-2 text-sm font-medium text-gray-600"
                    >
                        {{ $item['name'] }}
                    </p>
                    <p
                        class="text-lg font-semibold text-gray-700"
                    >
                        {{ $item['value'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
@endif
