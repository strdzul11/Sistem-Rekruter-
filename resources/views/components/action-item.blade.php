@props([
    'href'    => null,
    'onclick' => null,
    'danger'  => false,
    'method'  => null,   // 'DELETE', 'POST', dll
    'action'  => null,   // URL form action
    'confirm' => null,   // Pesan konfirmasi
    'icon'    => null,   // Class icon FontAwesome
])

@php
    $baseClass = 'flex items-center gap-2.5 w-full px-4 py-2 text-sm transition-colors duration-100 ';
    $colorClass = $danger
        ? 'text-red-600 hover:bg-red-50 hover:text-red-700'
        : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900';
@endphp

@if($method && $action)
    {{-- Form method (DELETE, dll) --}}
    <form method="POST" action="{{ $action }}" class="block"
          @if($confirm) onsubmit="return confirm('{{ $confirm }}')" @endif>
        @csrf
        @if(strtoupper($method) !== 'POST')
            @method($method)
        @endif
        <button type="submit" class="{{ $baseClass }}{{ $colorClass }}">
            @if($icon) <i class="{{ $icon }} w-4 text-center opacity-70"></i> @endif
            {{ $slot }}
        </button>
    </form>
@elseif($href)
    {{-- Link biasa --}}
    <a href="{{ $href }}"
       @if($onclick) onclick="{{ $onclick }}" @endif
       class="{{ $baseClass }}{{ $colorClass }}">
        @if($icon) <i class="{{ $icon }} w-4 text-center opacity-70"></i> @endif
        {{ $slot }}
    </a>
@else
    {{-- Tombol biasa --}}
    <button type="button"
            @if($onclick) onclick="{{ $onclick }}" @endif
            class="{{ $baseClass }}{{ $colorClass }}">
        @if($icon) <i class="{{ $icon }} w-4 text-center opacity-70"></i> @endif
        {{ $slot }}
    </button>
@endif
