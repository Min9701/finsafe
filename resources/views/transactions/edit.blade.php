<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sửa giao dịch</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('transactions.update', $transaction) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('transactions.form')
                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('transactions.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Hủy</a>
                        <x-primary-button>Cập nhật</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
