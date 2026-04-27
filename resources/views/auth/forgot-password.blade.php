<x-layouts.app title="Mot de passe oublie">

    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white rounded-xl shadow-sm p-8">
            <h1 class="text-2xl font-bold text-center mb-2">Mot de passe oublie ?</h1>
            <p class="text-sm text-gray-500 text-center mb-6">Entrez votre adresse email et nous vous enverrons un lien pour reinitialiser votre mot de passe.</p>

            @if(session('status'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
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

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                </div>

                <button type="submit" class="w-full px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">
                    Envoyer le lien
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                <a href="{{ route('login') }}" class="text-bordeaux hover:underline font-medium">Retour a la connexion</a>
            </p>
        </div>
    </div>

</x-layouts.app>
