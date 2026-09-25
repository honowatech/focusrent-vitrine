<x-admin.layout title="Message de {{ $message->name }}">
    <p class="mb-4 text-sm"><a href="{{ route('admin.messages') }}" class="text-[#FC912C] hover:text-white">Retour aux messages</a></p>
    <article class="rounded-xl bg-[#1D142B] p-6">
        <h1 class="mb-2 text-3xl font-bold text-white">{{ $message->name }}</h1>
        <p class="mb-6 text-sm text-gray-400">{{ $message->created_at->timezone('Africa/Douala')->format('d/m/Y H:i') }} · {{ strtoupper($message->locale ?: 'fr') }}</p>
        <dl class="mb-6 grid gap-3 text-sm">
            <div>
                <dt class="text-gray-400">E-mail</dt>
                <dd>@if ($message->email)<a href="mailto:{{ $message->email }}" class="hover:text-white">{{ $message->email }}</a>@else <span class="text-gray-500">—</span>@endif</dd>
            </div>
            <div>
                <dt class="text-gray-400">Téléphone</dt>
                <dd><a href="tel:{{ $message->phone }}" class="hover:text-white">{{ $message->phone }}</a></dd>
            </div>
        </dl>
        <h2 class="mb-2 text-lg font-semibold text-white">Message</h2>
        <p class="whitespace-pre-wrap text-gray-200">{{ $message->message }}</p>
    </article>
</x-admin.layout>
