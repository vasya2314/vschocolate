@php(
    $title = "Профиль"
)

<x-layouts.main title="{{ $title }}">
    <div>
        <x-ui.title.h-2 label="{{ $title }}" class="mt-6" />
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 mb-8">
            <div class="w-full">
                <div class="px-4 py-3 bg-white rounded-lg shadow-md h-full flex flex-col">
                    <x-ui.title.h-3 label="Личные данные"/>
                    <form action="{{ route('profile.update') }}" method="POST" class="flex flex-col">
                        @csrf
                        @method('patch')
                        <x-ui.form.input label="Имя" placeholder="Имя" type="text" name="firstname" class="mb-4" value="{{ old('firstname', $user->firstname) }}" />
                        <x-ui.form.input label="Фамилия" placeholder="Фамилия" type="text" name="lastname" class="mb-4" value="{{ old('lastname', $user->lastname) }}" />
                        <x-ui.form.input label="Email" placeholder="Email" required="required" type="email" name="email" value="{{ old('email', $user->email) }}" />
                        <x-ui.form.button label="Сохранить" type="submit" class="mt-4" />
                    </form>
                </div>
            </div>

            <div class="w-full">
                <div class="px-4 py-3 bg-white rounded-lg shadow-md h-full flex flex-col">
                    <x-ui.title.h-3 label="Смена пароля"/>
                    <form action="{{ route('profile.updatePassword') }}" method="POST" class="flex flex-col">
                        @csrf
                        @method('put')
                        <x-ui.form.input label="Текущий пароль" placeholder="Текущий пароль" required="required" type="password" name="current_password" class="mb-4" value="" />
                        <x-ui.form.input label="Новый пароль" placeholder="Не менее 8 символов" required="required" type="password" name="password" class="mb-4" value="" />
                        <x-ui.form.input label="Повторите новый пароль" placeholder="Повторите новый пароль" required="required" type="password" name="password_confirmation" value="" />
                        <x-ui.form.button label="Изменить пароль" type="submit" class="mt-4" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.main>
