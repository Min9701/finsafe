<div>
    <x-input-label for="type" value="Loại giao dịch" />
    <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="">-- Chọn loại --</option>
        <option value="income" {{ old('type', $transaction->type ?? '') === 'income' ? 'selected' : '' }}>Thu nhập</option>
        <option value="expense" {{ old('type', $transaction->type ?? '') === 'expense' ? 'selected' : '' }}>Chi tiêu</option>
    </select>
    <x-input-error :messages="$errors->get('type')" class="mt-2" />
</div>

<div>
    <x-input-label for="category_id" value="Danh mục" />
    <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="">-- Chọn danh mục --</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" data-type="{{ $category->type }}" {{ old('category_id', $transaction->category_id ?? '') == $category->id ? 'selected' : '' }}>
                {{ $category->name }} ({{ $category->type === 'income' ? 'Thu' : 'Chi' }})
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
</div>

<div>
    <x-input-label for="amount" value="Số tiền (VNĐ)" />
    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount', $transaction->amount ?? '')" required />
    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
</div>

<div>
    <x-input-label for="transaction_date" value="Ngày giao dịch" />
    <x-text-input id="transaction_date" name="transaction_date" type="date" class="mt-1 block w-full" :value="old('transaction_date', isset($transaction) ? $transaction->transaction_date->format('Y-m-d') : date('Y-m-d'))" required />
    <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
</div>

<div>
    <x-input-label for="description" value="Mô tả" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Nhập mô tả...">{{ old('description', $transaction->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

@push('scripts')
<script>
    document.getElementById('type').addEventListener('change', function() {
        const type = this.value;
        const sel = document.getElementById('category_id');
        Array.from(sel.options).forEach(o => {
            if (o.value === '') return;
            o.hidden = o.dataset.type !== type;
        });
        if (sel.selectedOptions[0]?.hidden) sel.value = '';
    });
    document.getElementById('type').dispatchEvent(new Event('change'));
</script>
@endpush
