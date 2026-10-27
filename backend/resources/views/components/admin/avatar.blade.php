@props(['admin', 'size' => null])

{{-- Initials on one of five brand colours, picked from the id so each admin keeps theirs. --}}
<span {{ $attributes->class(['avatar', 'avatar-'.$size => $size, 'tone-'.($admin->id % 5)]) }} aria-hidden="true">{{ $admin->initials() }}</span>
