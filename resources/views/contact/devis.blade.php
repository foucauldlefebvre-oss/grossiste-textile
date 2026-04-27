<x-layouts.app title="Demander un devis">

    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-bordeaux">Accueil</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Demander un devis</span>
            </nav>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Header --}}
        <div class="text-center mb-10">
            <div class="w-16 h-16 mx-auto mb-4 bg-bordeaux-50 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $sujetLabel ?? 'Nous contacter' }}</h1>
            <p class="text-gray-500 max-w-lg mx-auto">Decrivez votre projet et joignez vos fichiers (logos, maquettes...). Nous vous repondons sous 24h.</p>
        </div>

        {{-- Confirmation --}}
        @if(session('devis-sent'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center mb-8">
                <div class="w-14 h-14 mx-auto mb-3 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-green-800 mb-1">Demande envoyee !</h2>
                <p class="text-sm text-green-600">Nous avons bien recu votre demande de devis. Vous recevrez une reponse sous 24h.</p>
            </div>
        @endif

        {{-- Formulaire --}}
        <form action="{{ route('demande-devis.send') }}" method="POST" enctype="multipart/form-data"
              class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
            @csrf
            @if($sujet ?? null)
                <input type="hidden" name="sujet" value="{{ $sujetLabel }}">
            @endif
            {{-- Honeypot anti-bot --}}
            <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true" tabindex="-1">
                <label for="website_url">Ne pas remplir</label>
                <input type="text" name="website_url" id="website_url" value="" autocomplete="off" tabindex="-1">
            </div>
            <input type="hidden" name="_timestamp" value="{{ time() }}">

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Nom --}}
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                    <input type="text" name="nom" id="nom" value="{{ old('nom', auth()->user()?->name) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', auth()->user()?->email) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                </div>

                {{-- Telephone --}}
                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Telephone</label>
                    <input type="tel" name="telephone" id="telephone" value="{{ old('telephone', auth()->user()?->phone) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                </div>

                {{-- Societe --}}
                <div>
                    <label for="societe" class="block text-sm font-medium text-gray-700 mb-1">Societe</label>
                    <input type="text" name="societe" id="societe" value="{{ old('societe', auth()->user()?->company) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                </div>
            </div>

            {{-- Quantite --}}
            <div>
                <label for="quantite" class="block text-sm font-medium text-gray-700 mb-1">Quantite estimee</label>
                <select name="quantite" id="quantite"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                    <option value="">-- Selectionnez --</option>
                    <option value="10-49" @selected(old('quantite') === '10-49')>10 a 49 pieces</option>
                    <option value="50-99" @selected(old('quantite') === '50-99')>50 a 99 pieces</option>
                    <option value="100-499" @selected(old('quantite') === '100-499')>100 a 499 pieces</option>
                    <option value="500-999" @selected(old('quantite') === '500-999')>500 a 999 pieces</option>
                    <option value="1000+" @selected(old('quantite') === '1000+')>1 000 pieces et plus</option>
                </select>
            </div>

            {{-- Date de livraison souhaitee --}}
            <div x-data="{ dateVal: '{{ old('date_livraison') }}', minDate: '{{ now()->addDays(5)->format('Y-m-d') }}', get tooEarly() { return this.dateVal && this.dateVal < this.minDate; } }">
                <label for="date_livraison" class="block text-sm font-medium text-gray-700 mb-1">Date de livraison souhaitee</label>
                <input type="date" name="date_livraison" id="date_livraison" x-model="dateVal"
                       class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux"
                       :class="tooEarly ? 'border-red-400' : 'border-gray-300'">
                <p x-show="tooEarly" x-transition class="mt-1 text-xs text-red-600">Impossible : delai minimum de 5 jours ouvres requis.</p>
            </div>

            {{-- Message --}}
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Votre projet <span class="text-red-500">*</span></label>
                <textarea name="message" id="message" rows="5" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux"
                          placeholder="Decrivez votre projet : type de textile, technique souhaitee, couleurs, delai...">{{ old('message') }}</textarea>
            </div>

            {{-- Upload fichiers --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fichiers joints</label>
                <p class="text-xs text-gray-500 mb-3">Logos, maquettes, photos de reference... (PNG, JPG, PDF, AI, EPS — 10 Mo max par fichier, 5 fichiers max)</p>

                <div x-data="{ files: [] }" class="space-y-3">
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-bordeaux hover:bg-bordeaux-50/30 transition">
                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span class="text-sm text-gray-500 font-medium">Cliquez ou glissez vos fichiers</span>
                        <input type="file" name="fichiers[]" multiple accept=".png,.jpg,.jpeg,.pdf,.ai,.eps,.psd,.svg"
                               class="hidden"
                               @change="files = Array.from($event.target.files)">
                    </label>

                    {{-- Liste fichiers selectionnes --}}
                    <template x-if="files.length > 0">
                        <div class="space-y-1.5">
                            <template x-for="(file, i) in files" :key="i">
                                <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg text-sm">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <span class="flex-1 truncate text-gray-700" x-text="file.name"></span>
                                    <span class="text-xs text-gray-400" x-text="(file.size / 1024 / 1024).toFixed(1) + ' Mo'"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full px-6 py-3.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-center">
                Envoyer ma demande de devis
            </button>

            <p class="text-xs text-gray-400 text-center">
                En soumettant ce formulaire, vous acceptez d'etre contacte par notre equipe commerciale.
            </p>
        </form>
    </div>

</x-layouts.app>
