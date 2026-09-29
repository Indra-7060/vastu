@php
    $currentSort = request('sort');
    $currentDir = strtolower((string) request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
    $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
    $params = array_merge(request()->except('page'), [
        'sort' => $column,
        'dir' => $nextDir,
    ]);
    $isActive = $currentSort === $column;
    $arrow = $isActive ? ($currentDir === 'asc' ? ' ▲' : ' ▼') : ' ⇅';
@endphp
<a href="{{ url()->current().'?'.http_build_query($params) }}" class="sort-link {{ $isActive ? 'active' : '' }}">
    {{ $label }}<span class="sort-arrows">{{ $arrow }}</span>
</a>
