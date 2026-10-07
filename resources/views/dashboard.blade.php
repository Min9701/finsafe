<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard') }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 md:pt-[20px]">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-indigo-100 p-3">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Số dư</p>
                            <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-indigo-600' : 'text-red-600' }}">{{ number_format($balance, 0, ',', '.') }}đ</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-green-100 p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Tổng thu</p>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($totalIncome, 0, ',', '.') }}đ</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-red-100 p-3">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Tổng chi</p>
                            <p class="text-2xl font-bold text-red-600">{{ number_format($totalExpense, 0, ',', '.') }}đ</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Chi tiêu theo tháng</h3>
                    <canvas id="monthlyChart" height="200"></canvas>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Chi tiêu theo danh mục (tháng này)</h3>
                    @if($categoryExpenses->count() > 0)
                        <canvas id="categoryChart" height="200"></canvas>
                    @else
                        <p class="text-gray-500 text-center py-8">Chưa có dữ liệu chi tiêu trong tháng này.</p>
                    @endif
                </div>
            </div>
            @include('dashboard-recent-transactions')
        </div>
    </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        const md = @json($monthlyExpenses);
        new Chart(document.getElementById('monthlyChart'), { type:'bar', data:{ labels:md.map(i=>`T${i.month}/${i.year}`), datasets:[{data:md.map(i=>parseFloat(i.total)),backgroundColor:'rgba(99,102,241,0.7)',borderRadius:4}] }, options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}} });
        @if($categoryExpenses->count() > 0)
        const cd = @json($categoryExpenses);
        const cc = ['#6366f1','#ec4899','#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6','#14b8a6','#f97316','#06b6d4','#84cc16','#e11d48'];
        new Chart(document.getElementById('categoryChart'), { type:'doughnut', data:{ labels:cd.map(i=>i.category_name), datasets:[{data:cd.map(i=>parseFloat(i.total)),backgroundColor:cc.slice(0,cd.length)}] }, options:{responsive:true,plugins:{legend:{position:'bottom'}}} });
        @endif
    </script>
    @endpush
</x-app-layout>
