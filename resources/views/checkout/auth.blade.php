<x-layouts.app title="Commander">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-2xl font-bold text-center mb-8">Passer commande</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Bloc 1 : Nouveau client --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="text-lg font-bold mb-1">Nouveau client</h2>
                <p class="text-sm text-gray-500 mb-5">Creez votre compte pour finaliser la commande</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <x-honeypot id="checkout-register" />

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Societe</label>
                            <input type="text" name="company" value="{{ old('company') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telephone</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>

                        <div class="flex items-start gap-2">
                            <input type="checkbox" name="rgpd" id="rgpd-checkout" required class="mt-1 rounded text-bordeaux focus:ring-bordeaux">
                            <label for="rgpd-checkout" class="text-xs text-gray-500">J'accepte les <a href="{{ route('legal.cgv') }}" class="text-bordeaux hover:underline" target="_blank">CGV</a> et la <a href="{{ route('legal.privacy') }}" class="text-bordeaux hover:underline" target="_blank">politique de confidentialite</a> <span class="text-red-500">*</span></label>
                        </div>

                        <button type="submit" class="w-full py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-700 transition">
                            Creer mon compte et commander
                        </button>
                    </div>
                </form>
            </div>

            {{-- Bloc 2 : Deja client --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="text-lg font-bold mb-1">Deja client</h2>
                <p class="text-sm text-gray-500 mb-5">Connectez-vous pour retrouver vos informations</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <x-honeypot id="checkout-login" />

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="remember" class="rounded text-bordeaux focus:ring-bordeaux">
                                Se souvenir de moi
                            </label>
                            <a href="{{ route('password.request') }}" class="text-xs text-bordeaux hover:underline">Mot de passe oublie ?</a>
                        </div>

                        <button type="submit" class="w-full py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-700 transition">
                            Me connecter et commander
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</x-layouts.app>
