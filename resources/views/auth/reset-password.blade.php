<x-layouts.app title="Reinitialiser le mot de passe">

    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white rounded-xl shadow-sm p-8">
            <h1 class="text-2xl font-bold text-center mb-6">Nouveau mot de passe</h1>

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', request('email')) }}" required autofocus
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                    <input type="password" name="password" id="password" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                    <p class="text-xs text-gray-400 mt-1">Minimum 8 caracteres</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                </div>

                <button type="submit" class="w-full px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">
                    Reinitialiser le mot de passe
                </button>
            </form>
        </div>
    </div>

</x-layouts.app>
