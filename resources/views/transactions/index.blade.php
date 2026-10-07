<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Quản lý giao dịch</h2>
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md text-xs font-semibold text-white uppercase hover:bg-indigo-500 transition">+ Thêm giao dịch</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 md:pt-[20px]">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
            @endif
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('transactions.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="search" value="Tìm kiếm" />
                            <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" :value="request('search')" placeholder="Mô tả..." />
                        </div>
                        <div>
                            <x-input-label for="type" value="Loại" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Tất cả</option>
                                <option value="income" {{ request('type')==='income'?'selected':'' }}>Thu nhập</option>
                                <option value="expense" {{ request('type')==='expense'?'selected':'' }}>Chi tiêu</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="category_id" value="Danh mục" />
                            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Tất cả</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="date_from" value="Từ ngày" />
                            <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="request('date_from')" />
                        </div>
                        <div>
                            <x-input-label for="date_to" value="Đến ngày" />
                            <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="request('date_to')" />
                        </div>
                        <div>
                            <x-input-label for="amount_min" value="Số tiền từ" />
                            <x-text-input id="amount_min" name="amount_min" type="number" class="mt-1 block w-full" :value="request('amount_min')" />
                        </div>
                        <div>
                            <x-input-label for="amount_max" value="Số tiền đến" />
                            <x-text-input id="amount_max" name="amount_max" type="number" class="mt-1 block w-full" :value="request('amount_max')" />
                        </div>
                        <div class="flex items-end gap-2">
                            <x-primary-button>Lọc</x-primary-button>
                            <a href="{{ route('transactions.index') }}" class="px-4 py-2 bg-gray-200 rounded-md text-xs font-semibold text-gray-700 uppercase hover:bg-gray-300 transition leading-[20px]">Xóa lọc</a>
                        </div>
                    </div>
                </form>
            </div>
            @include('transactions.table')
        </div>
    </div>
</x-app-layout>
