<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    @include('head', ['title' => 'авторизация'])

    <body class="min-h-screen flex flex-col">
        <div class="flex items-center min-h-screen p-6 bg-gray-50">
            <div
                class="flex-1 h-full max-w-4xl mx-auto overflow-hidden bg-white rounded-lg shadow-xl"
            >
                <form method="POST" action="{{ route('auth.login') }}" class="flex flex-col overflow-y-auto md:flex-row">
                    @csrf
                    <div class="h-32 md:h-auto md:w-1/2 min-h-40">
                        <img
                            aria-hidden="true"
                            class="object-cover w-full h-full"
                            src="{{ asset('storage/static/images/login.jpg') }}"
                            alt="Office"
                        />
                    </div>
                    <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
                        <div class="w-full">
                            <x-ui.title.h-1 label="Авторизация" />
                            <x-ui.form.input label="Email" placeholder="Email" required="required" type="email" name="email" />
                            <x-ui.form.input label="Пароль" placeholder="Пароль" required="required" type="password" name="password" class="mt-4"/>

                            <x-ui.form.button label="Войти" type="submit" class="mt-4" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
