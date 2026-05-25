<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('backend_dashboard') }}" class="brand-link hmg-brand-link">
        <img src="{{ asset('backend_assets/images/hmglogo.png') }}" alt="{{ config('cms.name') }}"
            class="hmg-brand-logo">
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                @if(auth()->user()?->isPlatformOwner())
                    @foreach(config('cms.backend_module') as $backend_module)
                        @if(!empty($backend_module['title']))
                            <li class="nav-header">{{ strtoupper($backend_module['title']) }}</li>
                        @endif

                        @foreach($backend_module['items'] as $backend_key => $backend_item)
                            <li class="nav-item">
                                <a href="{{ route($backend_item['route']) }}"
                                    class="nav-link @if($selectedMainMenu == $backend_key) active @endif">
                                    <i class="nav-icon {{ $backend_item['icon'] }}"></i>
                                    <p>{{ $backend_item['title'] }}</p>
                                </a>
                            </li>
                        @endforeach
                    @endforeach
                @endif

                @php
                    $currentTenant = auth()->user()?->tenant;
                    $moduleManager = app(\App\Core\Module\ModuleManager::class);
                    $tenantMenus = auth()->check()
                        ? $moduleManager->menuForTenant($currentTenant)
                        : collect();
                    $tenantContentMenus = $tenantMenus->where('section', 'content')->values();
                    $tenantSystemMenus = $tenantMenus->where('section', 'systems')->values();
                @endphp

                @if($tenantContentMenus->isNotEmpty())
                    <li class="nav-header">CONTENT</li>
                    @foreach($tenantContentMenus as $tenantMenu)
                        @if(!empty($tenantMenu['items']))
                            <li class="nav-item has-treeview @if($selectedMainMenu == $tenantMenu['slug']) menu-open @endif">
                                <a href="#!"
                                    class="nav-link @if($selectedMainMenu == $tenantMenu['slug']) active @endif">
                                    <i class="nav-icon {{ $tenantMenu['icon'] }}"></i>
                                    <p>
                                        {{ $tenantMenu['title'] }}
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @foreach($tenantMenu['items'] as $tenantSubKey => $tenantSubMenu)
                                        @if(empty($tenantSubMenu['route']) || \Illuminate\Support\Facades\Route::has($tenantSubMenu['route']))
                                            <li class="nav-item">
                                                <a href="{{ !empty($tenantSubMenu['route']) ? route($tenantSubMenu['route']) : '#!' }}"
                                                    class="nav-link @if(!empty($selectedSubMenu) && $selectedSubMenu == $tenantSubKey) active @endif">
                                                    <i class="nav-icon fas fa-angle-right"></i>
                                                    <p>{{ $tenantSubMenu['title'] }}</p>
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @elseif(!empty($tenantMenu['route']) && \Illuminate\Support\Facades\Route::has($tenantMenu['route']))
                            <li class="nav-item">
                                <a href="{{ route($tenantMenu['route']) }}"
                                    class="nav-link @if($selectedMainMenu == $tenantMenu['slug']) active @endif">
                                    <i class="nav-icon {{ $tenantMenu['icon'] }}"></i>
                                    <p>{{ $tenantMenu['title'] }}</p>
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif

                @if(auth()->check() && !auth()->user()?->isPlatformOwner())
                    <li class="nav-header">SYSTEMS</li>
                    <li class="nav-item">
                        <a href="{{ route('backend_file_manager') }}"
                            class="nav-link @if($selectedMainMenu == 'file_manager') active @endif">
                            <i class="nav-icon fas fa-file-archive"></i>
                            <p>Files</p>
                        </a>
                    </li>

                    @foreach($tenantSystemMenus as $systemMenu)
                        @php
                            $systemKey = $systemMenu['slug'];
                            $visibleSystemItems = collect($systemMenu['items'])
                                ->filter(fn ($item) => !empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']));
                        @endphp

                        @if($visibleSystemItems->isNotEmpty())
                            <li class="nav-item has-treeview @if($selectedMainMenu == $systemKey) menu-open @endif">
                                <a href="#!"
                                    class="nav-link @if($selectedMainMenu == $systemKey) active @endif">
                                    <i class="nav-icon {{ $systemMenu['icon'] }}"></i>
                                    <p>
                                        {{ $systemMenu['title'] }}
                                        <i class="fas fa-angle-left right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @foreach($visibleSystemItems as $systemSubKey => $systemSubMenu)
                                        <li class="nav-item">
                                            <a href="{{ route($systemSubMenu['route']) }}"
                                                class="nav-link @if(!empty($selectedSubMenu) && $selectedSubMenu == $systemSubKey) active @endif">
                                                <i class="nav-icon fas fa-angle-right"></i>
                                                <p>{{ $systemSubMenu['title'] }}</p>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @elseif(!empty($systemMenu['route']) && \Illuminate\Support\Facades\Route::has($systemMenu['route']))
                            <li class="nav-item">
                                <a href="{{ route($systemMenu['route']) }}"
                                    class="nav-link @if($selectedMainMenu == $systemKey) active @endif">
                                    <i class="nav-icon {{ $systemMenu['icon'] }}"></i>
                                    <p>{{ $systemMenu['title'] }}</p>
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            </ul>
        </nav>
    </div>
</aside>
