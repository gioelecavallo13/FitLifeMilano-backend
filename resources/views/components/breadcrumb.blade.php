@props(['items' => []])

@if(count($items) > 0)
<nav aria-label="breadcrumb" class="mb-3 small">
    <ol class="breadcrumb breadcrumb-admin mb-0 flex-wrap align-items-center">
        @foreach($items as $item)
            <li class="breadcrumb-item d-inline-flex align-items-center {{ $loop->last ? 'text-secondary active' : '' }}">
                @if(!empty($item['url']) && !$loop->last)
                    <a href="{{ $item['url'] }}" class="text-white text-decoration-none link-anagrafica">{{ strtoupper($item['label']) }}</a>
                @else
                    <span class="{{ $loop->last ? 'text-secondary' : 'text-white' }}">{{ strtoupper($item['label']) }}</span>
                @endif
                @if(!$loop->last)<span class="text-secondary px-1"> / </span>@endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
