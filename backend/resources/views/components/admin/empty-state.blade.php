@props(['icon', 'title', 'compact' => false])

<div {{ $attributes->class(['empty-state', 'is-compact' => $compact]) }}>
    <div class="empty-state-icon" aria-hidden="true"><i class="{{ $icon }}"></i></div>
    <h2 class="empty-state-title">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <p class="empty-state-text">{{ $slot }}</p>
    @endif
    {{ $actions ?? '' }}
</div>
