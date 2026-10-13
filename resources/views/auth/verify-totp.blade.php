<x-guest-layout>
    <div class="mb-6 text-sm text-gray-600">
        <h1 class="mb-2 text-lg font-semibold text-gray-900">Xác thực Google Authenticator</h1>
        <p>Vui lòng nhập mã gồm 6 chữ số từ ứng dụng Google Authenticator trên thiết bị của bạn để hoàn tất đăng nhập.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('totp.challenge.store') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="code" value="Mã xác thực 6 số" />
            <x-text-input
                id="code"
                name="code"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                class="mt-1 block w-full text-center tracking-widest text-lg font-mono"
                required
                autofocus
                placeholder="000000"
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">
            Xác minh
        </x-primary-button>
    </form>

    <form method="POST" action="{{ auth()->user()?->isAdmin() ? route('admin.logout') : route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900">
            Đăng xuất
        </button>
    </form>
</x-guest-layout>
