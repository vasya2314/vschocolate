@php
    $title = "Клиенты";
@endphp

<x-layouts.main title="{{ $title }}">
    <x-ui.title.h-2 label="{{ $title }}" class="mt-6" />
    <div class="md:flex">
        <x-ui.form.button-a href="{{ route('client.create') }}" label="Создать клиента" class="py-3" />
        <x-ui.form.button-blue @click="toggleFilter" type="button" label="Открыть фильтр" class="py-3 mt-4 w-full md:ml-4 md:mt-0 md:w-max" />

        @if(!empty(request()->except('page')))
            <x-ui.form.button-a href="{{ route('client.index') }}" label="Сбросить фильтр" class="py-3 mt-4 w-full md:ml-4 md:mt-0 md:w-max" />
        @endif

    </div>
    @if($clients->isNotEmpty())
        <div class="w-full overflow-hidden rounded-lg shadow-xs mt-4">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                    <tr
                        class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50"
                    >
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Имя</th>
                        <th class="px-4 py-3">Телефон</th>
                        <th class="px-4 py-3">Платформа</th>
                        <th class="px-4 py-3">Комментарий</th>
                        <th class="px-4 py-3">Дата создания</th>
                        <th class="px-4 py-3">Действия</th>
                    </tr>
                    </thead>
                    <tbody
                        class="bg-white divide-y"
                    >
                    @foreach($clients as $client)
                        <tr class="{{ $client->deleted_at ? 'bg-gray-100 border-gray-300 text-gray-400' : 'bg-white border-gray-200 text-gray-700' }}">
                            <td class="px-4 py-3" style="position: sticky;left: 0; z-index: 10; background: inherit;">{{ $client->id }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium min-w-[100px]">{{ $client->name }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                {{ $client->phone_view }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $client->fromPlatform->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <p class="min-w-3xs">
                                    {{ $client->short_comment }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $client->created_at_view }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-4 text-sm">
                                    @if(is_null($client->deleted_at))
                                        <a
                                            href="{{ route('client.edit', $client->id) }}"
                                            class="text-blue-600 hover:text-blue-800 cursor-pointer"
                                        >
                                            <i class="bi bi-pencil inline-block text-xl"></i>
                                        </a>
                                        <form action="{{ route('client.delete', $client->id) }}" method="POST" onsubmit="return confirm('Вы действительно хотите удалить клиента?');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-red-600 hover:text-red-800 cursor-pointer">
                                                <i class="bi bi-trash3 inline-block text-xl"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('client.restore', $client->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-gray-500 hover:text-gray-700 cursor-pointer">
                                                <i class="bi bi-arrow-clockwise inline-block w-7 h-7 text-xl"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <x-common.paginate :data="$clients" />
        </div>
    @else
        <span class="block font-light text-red-500 mt-4">Не найдено ни одной записи</span>
    @endif
</x-layouts.main>

<x-layouts.filter action="{{ route('client.index') }}">
    <x-ui.form.select
        label="Площадка"
        :options="$fromPlatforms"
        name="from_platform_id"
        class="mb-4"
        multiple
        :value="request('from_platform_id')"
    />

    <x-ui.form.input
        label="Телефон"
        placeholder="Телефон"
        type="tel"
        name="phone"
        class="mb-4"
        :value="request('phone')"
    />
</x-layouts.filter>
