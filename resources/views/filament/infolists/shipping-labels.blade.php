@php
    $order = $getRecord();
    $labels = \App\Models\OrderDocument::where('order_id', $order->id)
        ->where('type', 'shipping_label')
        ->orderBy('created_at')
        ->get();
@endphp

@if($labels->isEmpty())
    <p class="text-sm text-gray-400">Aucune etiquette uploadee.</p>
@else
    <div class="flex flex-wrap gap-2">
        @foreach($labels as $label)
            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($label->path) }}" target="_blank"
               class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <div>
                    <p class="text-xs font-medium text-blue-700">{{ $label->label }}</p>
                    <p class="text-[10px] text-blue-400">{{ $label->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </a>
        @endforeach
    </div>
    <p class="text-xs text-gray-400 mt-1">{{ $labels->count() }} colis</p>
@endif
