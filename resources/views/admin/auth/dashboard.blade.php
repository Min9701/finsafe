<x-guest-layout>
    <div class="min-h-screen bg-slate-100 p-8">
        <div class="mx-auto max-w-7xl">

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-indigo-600">
                        FinSafe Administration
                    </p>

                    <h1 class="mt-1 text-3xl font-bold text-slate-900">
                        Admin Dashboard
                    </h1>

                    <p class="mt-2 text-slate-600">
                        Xin chào, {{ auth()->user()->name }}
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                    >
                        Logout
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-guest-layout>