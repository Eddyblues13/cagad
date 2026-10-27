@props(['title', 'subtitle' => null, 'back' => null, 'backLabel' => 'Back'])

<div {{ $attributes->class('page-header') }}>
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="page-back">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{ $backLabel }}
            </a>
        @endif
        <h1 class="page-title">
            <span class="min-w-0">{{ $title }}</span>
            {{ $badge ?? '' }}
        </h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="page-actions">{{ $actions }}</div>
    @endisset
</div>
