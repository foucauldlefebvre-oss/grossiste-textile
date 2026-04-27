<x-layouts.app title="Connexion">

    <div class="min-h-[60vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            {{-- Logo + heading --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-bordeaux mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold">Connectez-vous</h1>
                <p class="text-sm text-gray-500 mt-1">Accedez a votre espace client</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-8">
                @if(session('status'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2"
                         x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <x-honeypot id="login" />

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux"
                                   placeholder="vous@entreprise.fr">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            <input type="password" name="password" id="password" required
                                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-bordeaux focus:ring-bordeaux">
                            Se souvenir de moi
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm text-bordeaux hover:underline">Mot de passe oublie ?</a>
                    </div>

                    <button type="submit" class="w-full px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">
                        Se connecter
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t text-center">
                    <p class="text-sm text-gray-500">
                        Pas encore de compte ?
                        <a href="{{ route('register') }}" class="text-bordeaux hover:underline font-medium">Creer un compte gratuit</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
