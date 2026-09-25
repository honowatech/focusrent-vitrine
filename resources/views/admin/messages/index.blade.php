<x-admin.layout title="Messages">
    <h1 class="mb-6 text-3xl font-bold text-white">Messages de contact</h1>
    <div class="overflow-x-auto rounded-xl bg-[#1D142B]">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400">
                    <th class="px-4 py-3 font-semibold" scope="col">Reçu</th>
                    <th class="px-4 py-3 font-semibold" scope="col">Nom</th>
                    <th class="px-4 py-3 font-semibold" scope="col">E-mail</th>
                    <th class="px-4 py-3 font-semibold" scope="col">Message</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr class="border-b border-gray-800 {{ $message->isUnread() ? 'text-white' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $message->created_at->timezone('Africa/Douala')->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.messages.show', $message) }}" class="font-semibold hover:text-[#FC912C]">{{ $message->name }}</a>
                            @if ($message->isUnread())
                                <span class="ml-2 text-xs text-[#FC912C]">Nouveau</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $message->email ?? '—' }}</td>
                        <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($message->message, 80) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-gray-400" colspan="4">Aucun message pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($messages->hasPages())
        <div class="mt-6 flex gap-4 text-sm">
            @if ($messages->onFirstPage())
                <span class="text-gray-500">Précédent</span>
            @else
                <a href="{{ $messages->previousPageUrl() }}" class="hover:text-white">Précédent</a>
            @endif
            <span>Page {{ $messages->currentPage() }} / {{ $messages->lastPage() }}</span>
            @if ($messages->hasMorePages())
                <a href="{{ $messages->nextPageUrl() }}" class="hover:text-white">Suivant</a>
            @else
                <span class="text-gray-500">Suivant</span>
            @endif
        </div>
    @endif
</x-admin.layout>
