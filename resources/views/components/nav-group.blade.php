@props(['name', 'icon', 'id', 'items'])

@php
 $isOpen = collect($items)->contains(fn ($item) => request()->routeIs(...explode(',', $item[1])));
@endphp

<li class="nav-item nav-submenu">
 <button class="nav-link nav-submenu__toggle {{ $isOpen ? 'active' : '' }}"
 type="button"
 data-bs-toggle="collapse"
 data-bs-target="#{{ $id }}"
 aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
 aria-controls="{{ $id }}">
 <i class="material-symbols-rounded opacity-6">{{ $icon }}</i>
 <span class="nav-link-text ms-1">{{ $name }}</span>
 <i class="material-symbols-rounded nav-submenu__chevron" aria-hidden="true">expand_more</i>
 </button>
 <div class="collapse {{ $isOpen ? 'show' : '' }}" id="{{ $id }}">
 <ul class="nav flex-column nav-submenu__items">
 @foreach ($items as [$route, $patterns, $itemIcon, $label])
 @php
 $active = request()->routeIs(...explode(',', $patterns));
 @endphp
 <li class="nav-item">
 <a class="nav-link {{ $active ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route($route) }}">
 <i class="material-symbols-rounded opacity-6">{{ $itemIcon }}</i>
 <span class="nav-link-text ms-1">{{ $label }}</span>
 </a>
 </li>
 @endforeach
 </ul>
 </div>
</li>
