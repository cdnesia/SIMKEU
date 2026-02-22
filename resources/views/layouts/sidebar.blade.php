@php
    $menus = [
        [
            'title' => 'Dashboard',
            'icon' => 'bx bx-home-smile',
            'route' => 'dashboard',
        ],
        [
            'title' => 'Transaksi',
            'icon' => 'bx bx-money',
            'children' => [
                ['title' => 'Data Tagihan', 'route' => 'tagihan.index', 'icon' => 'bx bx-radio-circle'],
                ['title' => 'Data Pembayaran', 'route' => 'pembayaran.index', 'icon' => 'bx bx-radio-circle'],
                ['title' => 'Jadwal Pembayaran', 'route' => 'jadwal-pembayaran.index', 'icon' => 'bx bx-radio-circle'],
                [
                    'title' => 'Perpanjangan Per Mahasiswa',
                    'route' => 'perpanjangan-per-mahasiswa.index',
                    'icon' => 'bx bx-radio-circle',
                ],
            ],
        ],
        [
            'title' => 'Master Data',
            'icon' => 'bx bx-sitemap',
            'children' => [
                ['title' => 'BIPOT', 'route' => 'bipot.index', 'icon' => 'bx bx-radio-circle'],
                [
                    'title' => 'BIPOT Per Angkatan',
                    'route' => 'bipot-per-angkatan.index',
                    'icon' => 'bx bx-radio-circle',
                ],
                ['title' => 'Sync Data', 'route' => 'master.sync.index', 'icon' => 'bx bx-radio-circle'],
            ],
        ],
        [
            'title' => 'Manajemen',
            'icon' => 'bx bx-cog',
            'children' => [
                ['title' => 'Pengguna', 'route' => 'users.index', 'icon' => 'bx bx-radio-circle'],
                ['title' => 'Roles', 'route' => 'roles.index', 'icon' => 'bx bx-radio-circle'],
                ['title' => 'Permissions', 'route' => 'permissions.index', 'icon' => 'bx bx-radio-circle'],
            ],
        ],
        [
            'title' => 'Laporan',
            'icon' => 'bx bx-file',
            'children' => [
                ['title' => 'Mahasiswa', 'route' => 'laporan.mahasiswa', 'icon' => 'bx bx-radio-circle'],
                ['title' => 'Keuangan', 'route' => 'laporan.keuangan', 'icon' => 'bx bx-radio-circle'],
            ],
        ],
    ];
@endphp

<ul class="metismenu" id="menu">
    @foreach ($menus as $menu)
        @php
            $hasChildren = isset($menu['children']);
            $allowedChildren = $hasChildren
                ? collect($menu['children'])->filter(fn($child) => auth()->user()->can($child['route']))
                : collect();

            $parentActive = false;
            if (isset($menu['route']) && auth()->user()->can($menu['route'])) {
                $parts = explode('.', $menu['route']);
                array_pop($parts);
                $prefix = implode('.', $parts) . '.*';
                $parentActive = Route::is($prefix);
            } elseif ($hasChildren && $allowedChildren->isNotEmpty()) {
                $childPrefixes = $allowedChildren->pluck('route')->map(function ($r) {
                    $parts = explode('.', $r);
                    array_pop($parts);
                    return implode('.', $parts) . '.*';
                });
                foreach ($childPrefixes as $prefix) {
                    if (Route::is($prefix)) {
                        $parentActive = true;
                        break;
                    }
                }
            }
        @endphp

        @if (isset($menu['route']) && auth()->user()->can($menu['route']))
            <li class="{{ $parentActive ? 'mm-active' : '' }}">
                <a href="{{ route($menu['route']) }}">
                    <div class="parent-icon"><i class="{{ $menu['icon'] }}"></i></div>
                    <div class="menu-title">{{ $menu['title'] }}</div>
                </a>
            </li>
        @elseif($hasChildren && $allowedChildren->isNotEmpty())
            <li class="{{ $parentActive ? 'mm-active' : '' }}">
                <a href="javascript:void(0)" class="has-arrow">
                    <div class="parent-icon"><i class="{{ $menu['icon'] }}"></i></div>
                    <div class="menu-title">{{ $menu['title'] }}</div>
                </a>
                <ul>
                    @foreach ($allowedChildren as $child)
                        @php
                            $parts = explode('.', $child['route']);
                            array_pop($parts);
                            $prefix = implode('.', $parts) . '.*';
                            $childActive = Route::is($prefix);
                        @endphp
                        <li class="{{ $childActive ? 'mm-active' : '' }}">
                            <a href="{{ route($child['route']) }}">
                                <i class="{{ $child['icon'] }}"></i>{{ $child['title'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endif
    @endforeach
</ul>
