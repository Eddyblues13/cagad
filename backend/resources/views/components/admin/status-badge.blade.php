@props(['post'])

<span {{ $attributes->class(['status-badge', 'is-'.Str::lower($post->statusLabel())]) }}>{{ $post->statusLabel() }}</span>
