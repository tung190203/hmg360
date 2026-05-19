@php
    $listProjectHeader = App\Models\Project::orderBy('is_pinned', 'desc')->orderByRaw('CASE WHEN pin_order IS NULL THEN 999999 ELSE pin_order END ASC')->orderBy('updated_at', 'desc')->whereNull('parent_id')->where('status', 'approved')->take(5)->get();
    $countAllProject = App\Models\Project::where('is_invest', 0)->where('status', 'approved')->count();
@endphp
<header class="header">
    <div class="header__wrapper">
        <div class="container">
            <div class="header__inner">
                <a class="header__logo" href="{{ route('home_page') }}">
                    <img src="{{ \App\Models\Setting::getSettingByKey('logo') }}" alt="" />
                </a>
                <div class="header__elements">
                    <div class="header__text">{{ \App\Models\Setting::getSettingByKey('site_name') }}</div>
                </div>
                <button class="btn-toggle text-white d-xl-none js-navbar-toggle ms-1"></button>
            </div>
        </div>
        <nav class="navigation">
            <div class="container">
                <div class="navigation__inner">
                    <section class="navbar js-navbar">
                        <div class="navbar__backdrop js-navbar-toggle"></div>
                        <div class="navbar__wrapper">
                            <div class="navbar__header">
                                <a class="navbar__logo" href="{{ route('home_page') }}">
                                    <img src="{{ \App\Models\Setting::getSettingByKey('logo') }}" alt="" />
                                </a>
                                <button class="btn-toggle js-navbar-toggle ms-auto text-white"></button>
                            </div>
                            <div class="navbar__body">
                                <ul class="menu menu-root">
                                    @foreach($share['main_menu'] as $item)
                                        <li
                                            class="menu-item @if($item['name'] == __('app.investment_projects')) menu-item-group @endif">
                                            <a class="menu-link 
                                                    @if(empty($setting['menu_active']) && $item['name'] == __('app.home')) active
                                                    @elseif(($setting['menu_active'] ?? '') == \Str::slug($item['name'])) active
                                                    @endif" href="{{ $item['href'] }}">
                                                {{ $item['name'] }}

                                                {{-- Hiển thị badge cho menu nhóm --}}
                                                @if($item['name'] == __('app.investment_projects'))
                                                    <span class="badge bg-danger ms-2">{{ $countAllProject ?? 0 }}</span>
                                                @endif
                                            </a>

                                            {{-- Menu nhóm dự án --}}
                                            @if($item['name'] == __('app.investment_projects'))
                                                <span class="menu-toggle"></span>
                                                <ul class="menu menu-sub custom-menu-header">
                                                    @foreach($listProjectHeader as $project)
                                                        <li class="menu-item">
                                                            <a class="menu-link"
                                                                href="{{ route('project_detail', ['slug' => $project->slug, 'ref' => __('app.investment_projects')]) }}">
                                                                {{ $project->name }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                    <li class="menu-item">
                                                    @php
                                                        $locale = app()->getLocale() === 'vi' ? 'vn' : app()->getLocale();
                                                    @endphp
                                                        <a class="menu-link text-center"
                                                            href="{{ url($locale . '/' . __('app.projects_link')) }}">{{ __('app.view_more') }}</a>
                                                    </li>
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>

                            </div>
                        </div>
                    </section>
                    <form class="search" method="GET" action="{{ route('search') }}">
                        <div class="input-group">
                            <input class="form-control" name="keyword" type="text" placeholder="{{ __('app.search') }}"
                                size="1" />
                            <button class="input-group-text"><i class="far fa-search"></i></button>
                        </div>
                    </form>
                    <button class="h-btn d-none d-xl-flex ms-1 js-search-btn" type="button"><i
                            class="fal fa-fw fa-lg fa-search"></i></button>
                    <div class="h-dropdown">
                        <div class="h-dropdown__toggle">
                            <button class="h-btn ms-1" type="button"><i class="fal fa-fw fa-lg fa-user"></i></button>
                        </div>
                        <div class="h-dropdown__menu">
                            @if(Auth::guard('guest')->check())
                                <!-- Nếu đã login -->
                                <a class="h-dropdown__item"
                                    href="{{ route('account') }}">{{ __('app.personal_information') }}</a>
                                <a class="h-dropdown__item"
                                    href="{{ route('account') }}#project-interest">{{ __('app.interested_projects') }}</a>
                                <a class="h-dropdown__item" href="{{ route('guest_logout') }}">{{ __('app.logout') }}</a>
                            @else
                                <!-- Nếu chưa login -->
                                <a class="h-dropdown__item" href="#!" data-bs-toggle="modal"
                                    data-bs-target="#md-sign-in">{{ __('app.login') }}</a>
                            @endif
                        </div>
                    </div>
                    <div class="h-dropdown ms-2">
                        <div class="h-dropdown__toggle">
                            @php
                                $currentLocale = App::getLocale();
                            @endphp
                            @if($currentLocale === 'en')
                                <img src="{{ asset('images/gb.svg') }}" alt="{{ __('app.english') }}" />
                            @else
                                <img src="{{ asset('images/vn.svg') }}" alt="{{ __('app.vietnamese') }}" />
                            @endif
                            <i class="fal fa-fw fa-angle-down ms-1"></i>
                        </div>
                        @php
                            $availableLocales = config('app.available_locales');
                            $currentPath = Request::path();

                            foreach ($availableLocales as $lang) {
                                if ($currentPath === $lang || str_starts_with($currentPath, $lang . '/')) {
                                    $currentPath = ltrim(substr($currentPath, strlen($lang)), '/');
                                    break;
                                }
                            }
                            $queries = Request::except(['lang', 'page']);
                            $queryString = http_build_query($queries);
                            $querySuffix = $queryString ? '?' . $queryString : '';
                            $basePath = $currentPath ? '/' . $currentPath : '';
                        @endphp

                        <div class="h-dropdown__menu">
                            <a class="h-dropdown__item" 
                               href="{{ url('vn' . $basePath . $querySuffix) }}">
                                <img src="{{ asset('images/vn.svg') }}" alt="" />
                                <span>{{ __('app.vietnamese') }}</span>
                            </a>

                            <a class="h-dropdown__item" 
                               href="{{ url('en' . $basePath . $querySuffix) }}">
                                <img src="{{ asset('images/gb.svg') }}" alt="" />
                                <span>{{ __('app.english') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>