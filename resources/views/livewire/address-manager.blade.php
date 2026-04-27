<div>
    @if($showForm)
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="font-semibold mb-4">{{ $editingId ? 'Modifier l\'adresse' : 'Nouvelle adresse' }}</h2>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Libelle (ex: Bureau, Maison)</label>
                    <input type="text" wire:model="label" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Optionnel">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prenom *</label>
                        <input type="text" wire:model="first_name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" wire:model="last_name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Societe</label>
                    <input type="text" wire:model="company" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse *</label>
                    <input type="text" wire:model="address_line_1" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('address_line_1') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complement</label>
                    <input type="text" wire:model="address_line_2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code postal *</label>
                        <input type="text" wire:model="postal_code" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('postal_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ville *</label>
                        <input type="text" wire:model="city" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pays *</label>
                        <select wire:model="country" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="France">France</option>
                            <option value="Belgique">Belgique</option>
                            <option value="Suisse">Suisse</option>
                            <option value="Luxembourg">Luxembourg</option>
                            <option value="Allemagne">Allemagne</option>
                            <option value="Espagne">Espagne</option>
                            <option value="Italie">Italie</option>
                            <option value="Pays-Bas">Pays-Bas</option>
                            <option value="Portugal">Portugal</option>
                            <option value="Royaume-Uni">Royaume-Uni</option>
                            <option value="Autriche">Autriche</option>
                            <option value="Irlande">Irlande</option>
                            <option value="Danemark">Danemark</option>
                            <option value="Suede">Suede</option>
                            <option value="Finlande">Finlande</option>
                            <option value="Pologne">Pologne</option>
                            <option value="Republique Tcheque">Republique Tcheque</option>
                            <option value="Grece">Grece</option>
                            <option value="Roumanie">Roumanie</option>
                            <option value="Hongrie">Hongrie</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telephone</label>
                    <input type="tel" wire:model="phone" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="06 12 34 56 78">
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" wire:model="is_default" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Adresse par defaut
                </label>

                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                        {{ $editingId ? 'Mettre a jour' : 'Ajouter' }}
                    </button>
                    <button type="button" wire:click="cancel" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    @else
        <button wire:click="openForm" class="mb-6 px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition text-sm">
            + Ajouter une adresse
        </button>
    @endif

    @if($addresses->isEmpty() && !$showForm)
        <div class="bg-white rounded-xl shadow-sm p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-gray-500 mb-1">Aucune adresse enregistree.</p>
            <p class="text-sm text-gray-400">Ajoutez une adresse pour faciliter vos commandes.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($addresses as $address)
                <div class="bg-white rounded-xl shadow-sm p-5 relative {{ $address->is_default ? 'ring-2 ring-blue-500 ring-offset-1' : '' }}">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2">
                            @if($address->label)
                                <span class="font-semibold text-sm text-gray-900">{{ $address->label }}</span>
                            @endif
                            @if($address->is_default)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Par defaut
                                </span>
                            @endif
                        </div>
                    </div>
                    <p class="font-medium text-gray-900">{{ $address->first_name }} {{ $address->last_name }}</p>
                    @if($address->company)
                        <p class="text-sm text-gray-500">{{ $address->company }}</p>
                    @endif
                    <p class="text-sm text-gray-500 mt-1">{{ $address->address_line_1 }}</p>
                    @if($address->address_line_2)
                        <p class="text-sm text-gray-500">{{ $address->address_line_2 }}</p>
                    @endif
                    <p class="text-sm text-gray-500">{{ $address->postal_code }} {{ $address->city }}</p>
                    <p class="text-sm text-gray-500">{{ $address->country }}</p>
                    @if($address->phone)
                        <p class="text-sm text-gray-400 mt-1">
                            <svg class="w-3.5 h-3.5 inline -mt-0.5 mr-0.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $address->phone }}
                        </p>
                    @endif
                    <div class="flex gap-3 mt-3 pt-3 border-t">
                        <button wire:click="openForm({{ $address->id }})" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Modifier
                        </button>
                        <button wire:click="delete({{ $address->id }})" wire:confirm="Supprimer cette adresse ?" class="inline-flex items-center gap-1 text-sm text-red-500 hover:text-red-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Supprimer
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
