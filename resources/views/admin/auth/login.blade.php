<x-guest-layout>
    <div class="min-h-screen bg-slate-950 flex items-center justify-center px-4">
        <div class="w-full max-w-md">

            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-500 text-xl font-bold text-white">
                    F
                </div>

                <h1 class="text-2xl font-bold text-white">
                    FinSafe Admin
                </h1>

                <p class="mt-2 text-sm text-slate-400">
                    Administration Portal
                </p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900 p-8 shadow-xl">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-white">
                        Admin Sign In
                    </h2>

                    <p class="mt-1 text-sm text-slate-400">
                        Đăng nhập bằng tài khoản quản trị viên.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-slate-300">
                            Email
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="block w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            placeholder="admin@gmail.com"
                        >

                        @error('email')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-300">
                            Password
                        </label>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="block w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-white outline-none transition placeholder:text-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            placeholder="••••••••"
                        >

                        @error('password')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-400">
                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-slate-700 bg-slate-800 text-indigo-500"
                        >

                        Remember me
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                    >
                        Sign in to Admin
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">
                FinSafe Administration · Authorized personnel only
            </p>
        </div>
    </div>
</x-guest-layout>