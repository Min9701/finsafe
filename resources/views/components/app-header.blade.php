<header class="fixed inset-x-0 top-0 z-40 w-full border-b border-stone-200 bg-white/95 backdrop-blur">
    <div class="flex h-[50px] w-full items-center justify-between px-5 sm:px-8 lg:px-10">
        {{-- Logo + Navigation --}}
        <div class="flex min-w-0 items-center gap-4 sm:gap-8">
            <a
                href="{{ Auth::user()->role === 'ADMIN' ? route('admin.dashboard') : route('dashboard') }}"
                class="flex shrink-0 items-center gap-2.5 text-slate-900"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-white">
                    <x-application-logo class="h-5 w-5 fill-current" />
                </span>

                <span class="text-lg font-bold tracking-tight hidden md:inline-block">
                    FinSafe
                </span>
            </a>

            {{-- Navigation luôn hiển thị --}}
            <nav class="flex min-w-0 items-center gap-1 overflow-x-auto">
                @if (Auth::user()->role === 'ADMIN')
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="{{ request()->routeIs('admin.dashboard')
                            ? 'bg-stone-300 text-slate-900'
                            : 'text-stone-500 hover:bg-stone-50 hover:text-slate-800' }}
                            shrink-0 rounded-lg px-3 py-2 text-sm font-semibold transition"
                    >
                        Dashboard
                    </a>

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*')
                            ? 'bg-stone-300 text-slate-900'
                            : 'text-stone-500 hover:bg-stone-50 hover:text-slate-800' }}
                            shrink-0 rounded-lg px-3 py-2 text-sm font-semibold transition"
                    >
                        Người dùng
                    </a>
                @else
                    <a
                        href="{{ route('dashboard') }}"
                        class="{{ request()->routeIs('dashboard')
                            ? 'bg-stone-300 text-slate-900'
                            : 'text-stone-500 hover:bg-stone-50 hover:text-slate-800' }}
                            shrink-0 rounded-lg px-3 py-2 text-sm font-semibold transition"
                    >
                        Tổng quan
                    </a>

                    <a
                        href="{{ route('transactions.index') }}"
                        class="{{ request()->routeIs('transactions.*')
                            ? 'bg-stone-300 text-slate-900'
                            : 'text-stone-500 hover:bg-stone-50 hover:text-slate-800' }}
                            shrink-0 rounded-lg px-3 py-2 text-sm font-semibold transition"
                    >
                        Giao dịch
                    </a>
                    <a
                        href="{{ route('budgets.index') }}"
                        class="{{ request()->routeIs('budgets.*')
                            ? 'bg-stone-300 text-slate-900'
                            : 'text-stone-500 hover:bg-stone-50 hover:text-slate-800' }}
                            shrink-0 rounded-lg px-3 py-2 text-sm font-semibold transition"
                    >
                        Ngân sách
                    </a>
                @endif
            </nav>
        </div>

        {{-- User --}}
        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            @if (Auth::user()->role !== 'ADMIN')
                <a
                    href="{{ route('transactions.create') }}"
                    class="hidden rounded-lg bg-slate-800 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 sm:inline-flex"
                >
                    + Thêm giao dịch
                </a>
            @endif

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button
                        class="flex items-center gap-2 rounded-lg p-1.5 text-left transition focus:outline-none"
                    >
                        <span class="flex items-center justify-center text-xs font-bold text-slate-700">
                            {{ Auth::user()->name }}
                        </span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-stone-200 text-xs font-bold text-slate-700">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                    </button>
                </x-slot>

                <x-slot name="content">
                    @if (Auth::user()->role !== 'ADMIN')
                        <x-dropdown-link :href="route('profile.edit')">
                            Hồ sơ cá nhân
                        </x-dropdown-link>
                    @endif

                    <form
                        method="POST"
                        action="{{ Auth::user()->role === 'ADMIN' ? route('admin.logout') : route('logout') }}"
                    >
                        @csrf

                        <x-dropdown-link
                            :href="Auth::user()->role === 'ADMIN' ? route('admin.logout') : route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                        >
                            Đăng xuất
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>