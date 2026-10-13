<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 md:pt-[20px]">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">Tổng người dùng</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $totalUsers }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">Đang hoạt động</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $activeUsers }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">Bị vô hiệu hóa</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ $inactiveUsers }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">Mới trong tháng</p>
                    <p class="text-3xl font-bold text-amber-600 mt-1">{{ $newUsersThisMonth }}</p>
                </div>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Người dùng đăng ký theo tháng</h3>
                <canvas id="regChart" height="100"></canvas>
            </div>
            <div>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-md text-xs font-semibold text-white uppercase hover:bg-indigo-500 transition">Quản lý người dùng →</a>
            </div>
        </div>
    </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        const rd = @json($monthlyRegistrations);
        new Chart(document.getElementById('regChart'), {
            type:'line',
            data:{ labels:rd.map(i=>`T${i.month}/${i.year}`), datasets:[{label:'Người dùng mới',data:rd.map(i=>i.total),borderColor:'#6366f1',backgroundColor:'rgba(99,102,241,0.1)',fill:true,tension:0.3,pointRadius:4}] },
            options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
        });
    </script>
    @endpush
</x-app-layout>
