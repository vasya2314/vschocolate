@php(
    $title = "Настройки"
)

<x-layouts.main title="{{ $title }}">
    <div>
        <x-ui.title.h-2 label="{{ $title }}" class="mt-6" />
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <div class="w-full">
                <div class="px-4 py-3 bg-white rounded-lg shadow-md h-full flex flex-col">
                    <x-ui.title.h-3 label="Список платформ"/>

                    @if($fromPlatforms->isNotEmpty())
                        <ul class="space-y-2 max-h-[250px] overflow-y-auto pr-2 mb-4 h-full">
                            @foreach($fromPlatforms as $fromPlatform)
                                <li class="p-3 rounded border text-sm flex justify-between items-start gap-4
                                    {{ $fromPlatform->deleted_at ? 'bg-gray-100 border-gray-300 text-gray-400' : 'bg-white border-gray-200 text-gray-800' }}">
                                    <div>
                                        <span class="flex">
                                            <span class="block font-medium mr-1">ID:</span>
                                            <span class="block">{{ $fromPlatform->id }}</span>
                                        </span>
                                        <span class="flex flex-col md:flex-row">
                                            <span class="block font-medium mr-1">Название:</span>
                                            <span class="block mt-1 md:mt-0">{{ $fromPlatform->name }}</span>
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        @if(is_null($fromPlatform->deleted_at))
                                            <a href="{{ route('fromPlatform.edit', $fromPlatform->id) }}" class="text-blue-600 hover:text-blue-800 cursor-pointer">
                                                <i class="bi bi-pencil inline-block w-7 h-7 text-xl"></i>
                                            </a>
                                            <form action="{{ route('fromPlatform.delete', $fromPlatform->id) }}" method="POST" onsubmit="return confirm('Вы действительно хотите удалить платформу?');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-red-600 hover:text-red-800 cursor-pointer">
                                                    <i class="bi bi-trash3 inline-block w-7 h-7 text-xl"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('fromPlatform.restore', $fromPlatform->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-gray-500 hover:text-gray-700 cursor-pointer">
                                                    <i class="bi bi-arrow-clockwise inline-block w-7 h-7 text-xl"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="block font-light text-red-500 mb-4">Ни одной записи не найдено</span>
                    @endif

                    <form action="{{ route('fromPlatform.store') }}" method="POST" class="flex flex-col justify-end">
                        @csrf
                        <x-ui.form.input label="Название платформы" placeholder="Название платформы" required="required" type="text" name="name"/>
                        <x-ui.form.button label="Создать" type="submit" class="mt-4" />
                    </form>
                </div>
            </div>

            <div class="w-full">
                <div class="px-4 py-3 bg-white rounded-lg shadow-md h-full flex flex-col">
                    <x-ui.title.h-3 label="Список товаров"/>

                    @if($products->isNotEmpty())
                        <ul class="space-y-2 max-h-[250px] overflow-y-auto pr-2 mb-4 h-full">
                            @foreach($products as $product)
                                <li class="p-3 rounded border text-sm flex justify-between items-start gap-4
                                    {{ $product->deleted_at ? 'bg-gray-100 border-gray-300 text-gray-400' : 'bg-white border-gray-200 text-gray-800' }}">
                                    <div>
                                        <span class="flex">
                                            <span class="block font-medium mr-1">ID:</span>
                                            <span class="block">{{ $product->id }}</span>
                                        </span>
                                        <span class="flex flex-col md:flex-row">
                                            <span class="block font-medium mr-1">Название:</span>
                                            <span class="block mt-1 md:mt-0">{{ $product->name }}</span>
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        @if(is_null($product->deleted_at))
                                            <a href="{{ route('product.edit', $product->id) }}" class="text-blue-600 hover:text-blue-800 cursor-pointer">
                                                <i class="bi bi-pencil inline-block w-7 h-7 text-xl"></i>
                                            </a>
                                            <form action="{{ route('product.delete', $product->id) }}" method="POST" onsubmit="return confirm('Вы действительно хотите удалить товар?');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-red-600 hover:text-red-800 cursor-pointer">
                                                    <i class="bi bi-trash3 inline-block w-7 h-7 text-xl"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('product.restore', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-gray-500 hover:text-gray-700 cursor-pointer">
                                                    <i class="bi bi-arrow-clockwise inline-block w-7 h-7 text-xl"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="block font-light text-red-500 mb-4">Ни одной записи не найдено</span>
                    @endif

                    <form action="{{ route('product.store') }}" method="POST" class="flex flex-col justify-end">
                        @csrf
                        <x-ui.form.input label="Название товара" placeholder="Название товара" required="required" type="text" name="name"/>
                        <x-ui.form.button label="Создать" type="submit" class="mt-4" />
                    </form>
                </div>
            </div>

            <div class="w-full">
                <div class="px-4 py-3 bg-white rounded-lg shadow-md h-full flex flex-col">
                    <x-ui.title.h-3 label="Уведомления в TG"/>
                    <form action="{{ route('settings.saveTgSettings') }}" method="POST" class="flex flex-col justify-end">
                        @csrf
                        <x-ui.form.input label="Чат ID" placeholder="Чат ID" type="text" name="chat_id" value="{{ $chatId }}"/>
                        <x-ui.form.input label="Токен" placeholder="Токен" type="text" name="token" class="mt-4" value="{{ $token }}"/>
                        <x-ui.form.select label="Включить уведомления" placeholder="Включить уведомления" :options="$tgStatusValues"  value="{{ $tgStatus }}" name="tg_status" class="mt-4"/>
                        <x-ui.form.button label="Сохранить" type="submit" class="mt-4" />
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-layouts.main>
