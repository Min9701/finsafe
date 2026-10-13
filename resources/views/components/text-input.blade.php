@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-stone-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-stone-400 focus:border-slate-500 focus:ring-slate-500 disabled:bg-stone-100']) }}>
