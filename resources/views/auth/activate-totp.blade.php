<x-guest-layout>
    <div class="mb-6 text-sm text-gray-600">
        <h1 class="mb-2 text-lg font-semibold text-gray-900">Thiết lập Google Authenticator</h1>
        <p>Quét mã QR bằng ứng dụng Google Authenticator, sau đó nhập mã 6 số đang hiển thị để kích hoạt tài khoản.</p>
    </div>

    <div class="mb-6 flex justify-center rounded-lg bg-white p-4">
        {!! $qrCode !!}
    </div>

    <div class="mb-6 rounded-lg bg-gray-50 p-4 text-sm text-gray-700">
        <p class="font-medium">Không quét được mã QR?</p>
        <p class="mt-1 break-all font-mono">{{ $secret }}</p>
    </div>

    <form method="POST" action="{{ route('totp.activation.store') }}" class="space-y-4">
        @csrf
        <div>
            <x-input-label for="code" value="Mã xác thực 6 số" />
            <x-text-input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="mt-1 block w-full" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>
        <x-primary-button class="w-full justify-center">Xác nhận và tiếp tục</x-primary-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-600 underline">Đăng xuất</button>
    </form>
</x-guest-layout>
