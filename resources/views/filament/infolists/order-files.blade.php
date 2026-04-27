@php
    $order = $getRecord();
    $dir = 'orders/' . $order->reference;
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $files = $disk->exists($dir) ? $disk->files($dir) : [];
@endphp

@if(empty($files))
    <p class="text-sm text-gray-400">Aucun fichier charge par le client.</p>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        @foreach($files as $file)
            @php
                $name = basename($file);
                $url = $disk->url($file);
                $size = round($disk->size($file) / 1024);
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
            @endphp
            <a href="{{ $url }}" target="_blank" class="block border rounded-lg overflow-hidden hover:shadow-md transition group">
                @if($isImage)
                    <div class="aspect-square bg-gray-50 flex items-center justify-center p-2">
                        <img src="{{ $url }}" alt="{{ $name }}" class="max-w-full max-h-full object-contain">
                    </div>
                @else
                    <div class="aspect-square bg-gray-50 flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-10 h-10 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        <span class="text-xs font-medium uppercase">{{ $ext }}</span>
                    </div>
                @endif
                <div class="px-2 py-1.5 bg-white border-t">
                    <p class="text-xs font-medium text-gray-700 truncate group-hover:text-primary-600">{{ $name }}</p>
                    <p class="text-[10px] text-gray-400">{{ $size }} Ko</p>
                </div>
            </a>
        @endforeach
    </div>
@endif
