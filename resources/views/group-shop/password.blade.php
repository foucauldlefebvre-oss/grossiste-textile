<x-layouts.app title="{{ $groupShop->name }}">

    <div class="min-h-[60vh] flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                @if($groupShop->logo)
                    <img src="{{ Storage::url($groupShop->logo) }}" alt="{{ $groupShop->name }}" class="h-16 mx-auto mb-4 object-contain">
                @endif

                <div class="w-14 h-14 mx-auto mb-4 bg-blue-50 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>

                <h1 class="text-xl font-bold mb-1">{{ $groupShop->name }}</h1>
                <p class="text-gray-500 text-sm mb-6">Cette boutique est protegee par un mot de passe.</p>

                <form action="{{ route('group-shop.authenticate', $groupShop) }}" method="POST">
                    @csrf
                    <input type="password" name="password" placeholder="Mot de passe" required autofocus
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center @error('password') border-red-300 @enderror">
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="mt-4 w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                        Acceder a la boutique
                    </button>
                </form>
            </div>
        </div>
    </div>

</x-layouts.app>
