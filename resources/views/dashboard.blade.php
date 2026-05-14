<x-app-layout>

    <div class="py-4">
        <x-flash-messages />
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Notificações --}}
            @if($notifications->isNotEmpty())
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Notificações ({{ $notifications->count() }})
                    </h3>

                    <ul class="divide-y divide-gray-100">
                        @foreach($notifications as $notification)

                        <li class="py-4 flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $notification['data']['message'] }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $notification['created_at'] }}
                                </p>
                            </div>

                            <a href="{{ $notification['data']['action_url'] }}?notification_id={{ $notification['id'] }}"
                                class="shrink-0 text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                Aceitar convite
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>