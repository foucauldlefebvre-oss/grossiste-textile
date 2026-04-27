<x-layouts.app title="Creer un compte">

    <div class="min-h-[60vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-lg">
            {{-- Logo + heading --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-bordeaux mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold">Creer votre compte</h1>
                <p class="text-sm text-gray-500 mt-1">Rejoignez Marquage Textile pour gerer vos devis et commandes</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-8">
                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="{ password: '' }">
                    @csrf
                    <x-honeypot id="register" />

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux"
                               placeholder="Jean Dupont">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux"
                               placeholder="vous@entreprise.fr">
                    </div>

                    {{-- B2B fields --}}
                    <div class="border-t pt-4 mt-2">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="text-sm font-medium text-gray-700">Informations entreprise</span>
                            <span class="text-xs text-gray-400">(optionnel)</span>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-700 mb-1">Entreprise</label>
                                <input type="text" name="company" id="company" value="{{ old('company') }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux"
                                       placeholder="Raison sociale">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="siret" class="block text-sm font-medium text-gray-700 mb-1">SIRET</label>
                                    <input type="text" name="siret" id="siret" value="{{ old('siret') }}"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux"
                                           placeholder="123 456 789 00001" maxlength="17">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telephone</label>
                                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux"
                                           placeholder="03 20 40 06 90">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span class="text-red-500">*</span></label>
                            <input type="password" name="password" id="password" required x-model="password"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                            {{-- Password strength meter --}}
                            <div class="mt-2" x-show="password.length > 0" x-cloak>
                                <div class="flex gap-1 mb-1">
                                    <div class="h-1 flex-1 rounded-full transition-colors" :class="password.length >= 1 ? (password.length >= 12 ? 'bg-green-500' : password.length >= 8 ? 'bg-yellow-500' : 'bg-red-500') : 'bg-gray-200'"></div>
                                    <div class="h-1 flex-1 rounded-full transition-colors" :class="password.length >= 8 ? (password.length >= 12 ? 'bg-green-500' : 'bg-yellow-500') : 'bg-gray-200'"></div>
                                    <div class="h-1 flex-1 rounded-full transition-colors" :class="password.length >= 12 ? 'bg-green-500' : 'bg-gray-200'"></div>
                                </div>
                                <p class="text-xs" :class="password.length >= 12 ? 'text-green-600' : password.length >= 8 ? 'text-yellow-600' : 'text-red-500'"
                                   x-text="password.length >= 12 ? 'Mot de passe fort' : password.length >= 8 ? 'Mot de passe correct' : 'Trop court (min. 8 caracteres)'"></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                    </div>

                    {{-- RGPD consent --}}
                    <div>
                        <label class="flex items-start gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="rgpd" value="1" {{ old('rgpd') ? 'checked' : '' }} required
                                   class="rounded border-gray-300 text-bordeaux focus:ring-bordeaux mt-0.5">
                            <span>J'accepte la <a href="{{ route('legal.privacy') }}" target="_blank" class="text-bordeaux hover:underline">politique de confidentialite</a> et les <a href="{{ route('legal.terms') }}" target="_blank" class="text-bordeaux hover:underline">mentions legales</a>. <span class="text-red-500">*</span></span>
                        </label>
                        @error('rgpd') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">
                        Creer mon compte
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t text-center">
                    <p class="text-sm text-gray-500">
                        Deja un compte ?
                        <a href="{{ route('login') }}" class="text-bordeaux hover:underline font-medium">Se connecter</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
