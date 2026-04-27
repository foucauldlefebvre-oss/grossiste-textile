<x-layouts.app title="Mon profil">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('account._sidebar')

            <div class="flex-1">
                <h1 class="text-2xl font-bold mb-6">Mon profil</h1>

                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2"
                         x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Profile info --}}
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="font-semibold mb-4">Informations personnelles</h2>

                    <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-4 max-w-lg">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                            <input type="text" name="name" id="name" value="{{ old('name', Auth::user()->name) }}" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', Auth::user()->email) }}" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="border-t pt-4 mt-2">
                            <p class="text-xs text-gray-400 mb-3">Informations entreprise (optionnel)</p>
                            <div class="space-y-4">
                                <div>
                                    <label for="company" class="block text-sm font-medium text-gray-700 mb-1">Entreprise</label>
                                    <input type="text" name="company" id="company" value="{{ old('company', Auth::user()->company) }}"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                                    @error('company') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="siret" class="block text-sm font-medium text-gray-700 mb-1">SIRET</label>
                                        <input type="text" name="siret" id="siret" value="{{ old('siret', Auth::user()->siret) }}"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux" maxlength="17">
                                        @error('siret') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telephone</label>
                                        <input type="tel" name="phone" id="phone" value="{{ old('phone', Auth::user()->phone) }}"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="px-6 py-2.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">
                            Mettre a jour
                        </button>
                    </form>
                </div>

                {{-- Password --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="font-semibold mb-4">Changer le mot de passe</h2>

                    <form method="POST" action="{{ route('account.profile.password') }}" class="space-y-4 max-w-lg">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                            <input type="password" name="current_password" id="current_password" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                            @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                            <input type="password" name="password" id="password" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le nouveau mot de passe</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                        </div>

                        <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white font-semibold rounded-lg hover:bg-gray-900 transition">
                            Changer le mot de passe
                        </button>
                    </form>
                </div>

                {{-- RGPD --}}
                <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                    <h2 class="font-semibold mb-4">Mes donnees personnelles (RGPD)</h2>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('account.export') }}" class="px-6 py-2.5 border border-bordeaux text-bordeaux font-medium rounded-lg hover:bg-bordeaux-50 transition text-center text-sm">
                            Exporter mes donnees (JSON)
                        </a>

                        <div x-data="{ confirmDelete: false }">
                            <button @click="confirmDelete = true" class="px-6 py-2.5 border border-red-500 text-red-500 font-medium rounded-lg hover:bg-red-50 transition text-sm">
                                Supprimer mon compte
                            </button>

                            <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="confirmDelete = false">
                                <div class="bg-white rounded-xl shadow-lg p-6 max-w-md w-full mx-4">
                                    <h3 class="text-lg font-bold text-red-600 mb-2">Supprimer mon compte</h3>
                                    <p class="text-sm text-gray-500 mb-4">Cette action est irreversible. Vos donnees personnelles seront supprimees. Vos commandes seront anonymisees pour des raisons comptables.</p>

                                    <form method="POST" action="{{ route('account.delete') }}" class="space-y-4">
                                        @csrf
                                        @method('DELETE')

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe pour confirmer</label>
                                            <input type="password" name="password" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="flex gap-3">
                                            <button type="submit" class="px-6 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                                                Confirmer la suppression
                                            </button>
                                            <button type="button" @click="confirmDelete = false" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                                Annuler
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-gray-400">
                        Pour en savoir plus, consultez notre <a href="{{ route('legal.privacy') }}" class="text-blue-500 hover:underline">politique de confidentialite</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
