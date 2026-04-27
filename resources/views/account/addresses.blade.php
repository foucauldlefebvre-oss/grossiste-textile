<x-layouts.app title="Mes adresses">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('account._sidebar')

            <div class="flex-1">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold">Mes adresses</h1>
                </div>

                <livewire:address-manager />
            </div>
        </div>
    </div>

</x-layouts.app>
