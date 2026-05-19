@extends('frontend.index')
@php
    $countAllProject = App\Models\Project::count();
    $countAllIndustrial = App\Models\ProjectIndustries::count();
@endphp
@section('content')
    <div class="page__content">
        <!-- main content-->
        <section class="banner-home" style="position: relative;">
            @if(file_exists(public_path('videos/intro.mp4')))
                <div id="video-loader-container" style="display: none;">
                    <video id="loader-video" autoplay muted playsinline preload="auto">
                        <source src="/videos/intro.mp4" type="video/mp4">
                        Trình duyệt của bạn không hỗ trợ video.
                    </video>
                    <button id="skip-video-btn" class="btn-skip-video">
                        Bỏ qua video <i class="fas fa-forward ms-2"></i>
                    </button>
                </div>
            @endif
            <div class="w-full">
                <div id="map">
                    <!-- Railway Legend -->
                    <div id="railway-legend" style="display:none;">
                        <!-- Icon hiển thị khi thu gọn -->
                        <button id="railway-legend-icon" class="railway-legend__icon-btn" title="Mở chú thích">
                            <i class="fas fa-train"></i>
                        </button>
                        
                        <!-- Panel hiển thị khi mở rộng -->
                        <div id="railway-legend-panel">
                            <div class="railway-legend__header">
                                <i class="fas fa-train me-2"></i>
                                <span>Chú thích tuyến ĐSĐT</span>
                                <button id="railway-legend-toggle" title="Thu gọn"><i class="fas fa-times"></i></button>
                            </div>
                            <div id="railway-legend__body">
                                <div class="railway-legend__grid" id="railway-legend-items">
                                    <!-- Items sẽ được render bởi JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="pj-search" id="pjSearchFull">
            <div class="container py-3" style="position: absolute; top:50%;left:50%;transform: translate(-50%,-100%);">
                <!-- FORM: TÌM KIẾM DỰ ÁN -->
                <div class="pj-search__body custom_body tab-content active" id="projectTabContent">
                    <div class="pj-search__top">
                        <div class="pj-search__col">
                            <div class="input-group">
                                <input class="form-control" type="text" id="searchInput"
                                    placeholder="{{ __('app.enter_project_name') }}">
                                <div class="input-group-text"><i class="fal fa-lg fa-search"></i></div>
                            </div>
                        </div>
                        <div class="pj-search__col">
                            <button class="pj-search__btn" id="applyBtn" type="button">{{ __('app.search') }}</button>
                        </div>
                    </div>
                    <div class="pj-search__bottom">
                        <div class="pj-search__col custom-select" style="position: relative;">
                            <div class="input-group">
                                <input class="form-control" type="text" id="districtFilter"
                                    placeholder="{{ __('app.locations') }}" autocomplete="off">
                                <div class="input-group-text cursor-pointer" id="openDropdown">
                                    <i class="fal fa-lg fa-map-marker-alt cursor-pointer"></i>
                                </div>
                            </div>
                            <div id="districtDropdown" class="mt-1 bg-white border border-gray-300 rounded shadow"
                                style="position: absolute; z-index: 999;">
                                <!-- Nội dung dropdown -->
                            </div>
                        </div>
                        <div class="pj-search__col">
                            <select class="form-select" id="typeFilter">
                                <option value="all">{{ __('app.project_types') }}</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pj-search__col">
                            <select class="form-select" id="industryFilter">
                                <option value="all">{{ __('app.industry_field') }}</option>
                                @foreach ($industries as $industry)
                                    <option value="{{ $industry['id'] }}">{{ $industry['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pj-search__col">
                            <div class="range-input">
                                <div class="range-input__content">
                                    <div class="range-input__label">{{ __('app.investment_scale') }}</div>
                                    <div class="range-input__price">0</div>
                                </div>
                                <input class="range-input__input" id="priceRange" type="range" value="0"
                                    min="0" max="{{ $maxPrice ?? 10000 }}" step="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORM: SP KHU CÔNG NGHIỆP -->
                <div class="pj-search__body custom_body tab-content orange-theme" id="industrialTabContent"
                    style="display: none">
                    <div class="pj-search__top">
                        <div class="pj-search__col position-relative">
                            <div class="input-group">
                                <input class="form-control" type="text" id="searchInputSp"
                                    placeholder="{{ __('app.search_keyword') }}" autocomplete="off">
                                <div class="input-group-text"><i class="fal fa-lg fa-search"></i></div>
                            </div>

                            <!-- POPUP GỢI Ý - Đã thêm z-index và xử lý truncate -->
                            <div id="suggestionPopupSp" class="suggestion-orange-popup shadow-lg" style="z-index: 10000;">
                                <div class="suggestion-header">
                                    <span>{{ __('app.suggested_projects') }}</span>
                                    <!-- Spinner để báo hiệu đang tìm kiếm -->
                                    <div class="spinner-border spinner-border-sm text-warning d-none"
                                        id="suggestionLoaderSp"></div>
                                </div>
                                <div class="suggestion-content">
                                    <ul class="list-unstyled mb-0" id="suggestionListSp">

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="pj-search__col">
                            <button class="pj-search__btn orange-btn" id="applyBtnSp"
                                type="button">{{ __('app.search') }}</button>
                        </div>
                    </div>
                    <div class="pj-search__bottom">
                        <div class="pj-search__col custom-select" style="position: relative;">
                            <div class="input-group">
                                <input class="form-control" type="text" id="districtFilterSp"
                                    placeholder="{{ __('app.locations') }}" autocomplete="off">
                                <div class="input-group-text cursor-pointer" id="openDropdownSp">
                                    <i class="fal fa-lg fa-map-marker-alt cursor-pointer"></i>
                                </div>
                            </div>
                            <div id="districtDropdownSp" class="mt-1 bg-white border border-gray-300 rounded shadow"
                                style="position: absolute; z-index: 999;">
                                <!-- Nội dung dropdown -->
                            </div>
                        </div>
                        <div class="pj-search__col">
                            <select class="form-select" id="project_id">
                                <option value="all">{{ __('app.choice_project') }}</option>
                                @foreach ($list_projects as $project)
                                    <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pj-search__col">
                            <select class="form-select" id="product_type">
                                <option value="all">{{ __('app.product_type') }}</option>
                                @foreach ($product_types as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pj-search__col">
                            <div class="range-input">
                                <div class="range-input__content">
                                    <div class="range-input__label text-white">{{ __('app.rental_price') }}</div>
                                    <div class="range-input__price1 text-white">0</div>
                                </div>
                                <input class="white-range" id="priceRangeSp" type="range" value="0"
                                    min="0" max="{{ $maxPriceSp ?? 10000 }}" step="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs dưới form -->
                <div class="custom_tabs">
                    <button class="custom-btn active text-uppercase" id="projectTab"
                        onclick="showTab('project')">{{ __('app.search_project') }}</button>
                    <button class="custom-btn text-uppercase" id="industrialTab"
                        onclick="showTab('industrial')">{{ __('app.industrial_products') }}</button>
                </div>
            </div>
        </div>
        <div class="pj-search" id="pjSearchMini">
            <div class="container py-3" style="position: absolute; top:50%;left:50%;transform: translate(-50%,-100%);">
                <div class="pj-search__body custom_body tab-content active" id="projectTabContentMini"
                    style="border-bottom-left-radius:8px !important">
                    <div class="pj-search__top">
                        <div class="pj-search__col">
                            <div class="input-group">
                                <input class="form-control" type="text" id="searchInput"
                                    placeholder="{{ __('app.enter_project_name') }}">
                                <div class="input-group-text"><i class="fal fa-lg fa-search"></i></div>
                            </div>
                        </div>
                        <div class="pj-search__col">
                            <button class="pj-search__btn" id="applyBtn" type="button">{{ __('app.search') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section class="section" id="investment-section">
            <div class="container">
                <h2 class="section__title mb-3 text-uppercase">{{ __('app.investment_portfolio') }}</h2>

                <div class="project-nav-wrapper mb-60">
                    <!-- "Tất cả" cố định -->
                    <div class="project-nav-fixed">
                        <a class="{{ request('industry') ? '' : 'active' }}"
                            href="{{ route('home_page') }}#investment-section">
                            {{ __('app.all') }}
                        </a>
                    </div>

                    <!-- Các ngành scroll ngang -->
                    <div class="project-nav-scroll swiper-container">
                        <ul class="project-nav__list_custom swiper-wrapper">
                            @foreach ($industries as $industry)
                                <li class="swiper-slide" data-tippy-content="{{ $industry['name'] }}">
                                    <a class="{{ request('industry') == $industry['id'] ? 'active' : '' }}"
                                        href="{{ route('home_page', ['industry' => $industry['id']]) }}#investment-section">
                                        {{ $industry['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div id="project-slider-wrapper" style="position: relative; min-height: 200px;">
                    @include('frontend.home.partials.project_slider')
                </div>
            </div>
        </section>
        <section class="section section--bg-pattern" style="padding: 23px 0">
            <div class="container">
                <div class="features-slider">
                    <div class="features-slider__container swiper-container">
                        <div class="swiper-wrapper">
                            @php
                                $locale = app()->getLocale();
                            @endphp

                            @foreach ($setting['features'] as $item)
                                <div class="swiper-slide">
                                    <div class="counter">
                                        <div class="counter__item">
                                            <div class="counter__icon">
                                                <img src="{{ $item['icon'] ?? '' }}" alt="" />
                                            </div>
                                            <div class="counter__number">
                                                {{ $item['title'][$locale] ?? ($item['title']['vi'] ?? '0') }}
                                            </div>
                                            <div class="counter__title">
                                                {{ $item['content'][$locale] ?? ($item['content']['vi'] ?? '') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section">
            {{-- <img class="texture-1" src="./images/texture-1.png" alt="" />
            <img class="texture-2" src="./images/texture-2.png" alt="" /> --}}
            <div class="container">
                <h2 class="section__title text-uppercase">{{ __('app.news') }}</h2>
                <div class="news-slider">
                    <div class="news-slider__nav">
                        <div class="news-slider__prev"><i class="fal fa-fw fa-lg fa-angle-left"></i></div>
                        <div class="news-slider__next"><i class="fal fa-fw fa-lg fa-angle-right"></i></div>
                    </div>
                    <div class="news-slider__container swiper-container">
                        <div class="swiper-wrapper">
                            @foreach ($posts as $item)
                                <div class="swiper-slide">
                                    <div class="news">
                                        <a class="news__frame"
                                            href="{{ route('post_detail', ['id' => $item['id'], 'slug' => $item['slug'], 'ref' => 'app.news']) }}">
                                            <img src="{{ $item['image'] }}" alt="" />
                                        </a>
                                        <div class="news__body">
                                            <div class="news__info">
                                                <div class="news__time">
                                                    <i class="fal fa-clock me-2"></i>
                                                    <span>{{ \Carbon\Carbon::parse($item['published_at'])->format('d/m/Y') }}</span>
                                                </div>
                                                <a class="news__like" href="javascript:void(0)"
                                                    data-id="{{ $item['id'] }}" data-type="App\Models\Post">
                                                    <i
                                                        class="fas fa-fw fa-heart {{ $item['is_interested'] ? 'text-danger' : '' }}"></i>
                                                </a>
                                            </div>
                                            <h3 class="news__title custom-desc">
                                                <a href="{{ route('post_detail', ['id' => $item['id'], 'slug' => $item['slug'], 'ref' => 'app.news']) }}"
                                                    data-tippy-content="{{ $item['name'] }}">
                                                    {{ $item['name'] }}
                                                </a>
                                            </h3>
                                            <div class="news__desc">{{ $item['description'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @php
                    $locale = app()->getLocale() === 'vi' ? 'vn' : app()->getLocale();
                @endphp
                <nav class="d-flex justify-content-center mt-40 mt-lg-60"><a class="button"
                        href="{{ url($locale . '/' . __('app.news_link')) }}"
                        style="text-transform: capitalize;">{{ __('app.view_more') }}</a>
                </nav>
            </div>
        </section>
        <section class="section section--medium-blue">
            <div class="container">
                <h2 class="section__title text-white text-uppercase">{{ __('app.link') }}</h2>
                @if (!empty($setting['banners']))
                    <div class="partners-slider">
                        <div class="partners-slider__container swiper-container">
                            <div class="swiper-wrapper">
                                @foreach ($setting['banners'] as $banner)
                                    <div class="swiper-slide">
                                        <div class="partners__item">
                                            <a href="{{ $banner['link'] ?? '#' }}" target="_blank">
                                                <img src="{{ $banner['image'] ?? '' }}" alt="" />
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <div class="modal fade" id="filterResultModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('app.filter_results') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="{{ __('app.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <ul id="resultList" class="list-group"></ul>
                        <nav>
                            <ul id="pagination" class="pagination justify-content-center mt-3"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div id="homePopup"
            style="display:none; position:fixed; inset:0;
               background:rgba(0,0,0,0.6); z-index:9999;
               justify-content:center; align-items:center;">

            <div id="popupBox"
                style="position:relative; width:80%; max-width:900px;
                 background:#fff; border-radius:12px; overflow:hidden;
                 box-shadow:0 4px 20px rgba(0,0,0,0.3);">

                <div id="popupBody" style="position:relative; width:100%; aspect-ratio:16/9; overflow:hidden;">

                    <a id="popupLink" href="#" target="_blank"
                        style="display:block; width:100%; height:100%;
                     background-position:center; background-repeat:no-repeat;
                     background-size:cover;">
                    </a>

                    <button id="closePopup"
                        style="position:absolute; top:10px; right:10px;
                     border:none; background:rgba(0,0,0,0.5);
                     color:#fff; font-size:24px; font-weight:bold;
                     cursor:pointer; border-radius:50%; width:36px; height:36px;
                     line-height:32px; text-align:center;">
                        ×
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('bottom')
    <style>
        /* pj-search wrapper phủ lên bản đồ nhưng phải trong suốt với sự kiện chuột
           để không chặn click/drag bản đồ ở vùng trống xung quanh form */
        #pjSearchFull,
        #pjSearchMini {
            pointer-events: none;
        }
        /* Chỉ cho phép tương tác trên phần nội dung thực của form */
        #pjSearchFull .pj-search__body,
        #pjSearchFull .custom_tabs,
        #pjSearchMini .pj-search__body {
            pointer-events: auto;
        }

        /* Xóa ô vuông xanh (focus outline) khi click/focus vào ranh giới */
        .leaflet-interactive:focus {
            outline: none !important;
        }

        /* Style lại tooltip cho tinh tế hơn */
        .boundary-tooltip {
            background: rgba(255, 255, 255, 0.9);
            border: none !important;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            color: #333;
            font-weight: 600;
            padding: 4px 8px;
            font-size: 12px;
        }

        .railway-tooltip {
            background: rgba(33, 33, 33, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            color: #fff;
            font-weight: 500;
            padding: 6px 12px;
            font-size: 13px;
        }

        .railway-tooltip:before {
            border: none !important;
        }

        .boundary-tooltip:before {
            border: none !important;
        }

        .suggestion-orange-popup {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            z-index: 1000;
            margin-top: 8px;
            border-radius: 8px;
            display: none;
            overflow: hidden;
        }

        .suggestion-orange-popup.active {
            display: block;
            animation: fadeInDown 0.2s ease-out;
        }

        .suggestion-header {
            background: #fff3e0;
            padding: 8px 15px;
            font-size: 12px;
            font-weight: bold;
            color: #e65100;
            text-transform: uppercase;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ffe0b2;
        }

        .suggestion-content {
            max-height: 280px;
            overflow-y: auto;
        }

        .suggestion-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            text-decoration: none;
            border-bottom: 1px solid #f5f5f5;
            transition: background 0.2s;
        }

        .suggestion-item .icon-box {
            width: 36px;
            height: 36px;
            background: #fff3e0;
            color: #ff9800;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .suggestion-item .info .name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            line-height: 1.2;
        }

        .suggestion-item .info .sub {
            font-size: 12px;
            color: #777;
        }

        .suggestion-footer {
            padding: 10px;
            text-align: center;
            background: #fafafa;
        }

        .suggestion-footer a {
            font-size: 13px;
            text-decoration: none;
            font-weight: 500;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Video Loader Styles */
        #video-loader-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 20000;
            background: #000;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: auto;
        }
        #loader-video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .btn-skip-video {
            position: absolute;
            bottom: 30px;
            right: 30px;
            padding: 12px 24px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            z-index: 2001;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        @media (max-width: 768px) {
            .btn-skip-video {
                bottom: 20px;
                right: 20px;
                padding: 10px 20px;
                font-size: 12px;
            }
        }
        .btn-skip-video:hover {
            background: rgba(0, 0, 0, 0.8);
            border-color: #fff;
            transform: translateY(-2px);
        }
        .video-fade-out {
            opacity: 0;
            transition: opacity 0.8s ease-out;
            pointer-events: none;
        }

        /* ── RAILWAY LEGEND ─────────────────────────────────────── */
        #railway-legend {
            position: absolute;
            bottom: 24px;
            left: 12px;
            z-index: 1500;
            pointer-events: none; /* Let clicks pass through empty spaces */
        }
        
        .railway-legend__icon-btn {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #1a6fc4;
            color: #fff;
            border: 2px solid #fff;
            box-shadow: 0 4px 15px rgba(26, 111, 196, 0.4);
            font-size: 18px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            
            /* Animation */
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            transform-origin: center;
            opacity: 0;
            transform: scale(0.5);
            visibility: hidden;
            pointer-events: none;
            z-index: 2;
            display: flex; /* So the flex rules stay even when hidden */
        }
        .railway-legend__icon-btn:hover {
            background: #1d7ddc;
        }

        #railway-legend.legend-collapsed .railway-legend__icon-btn {
            opacity: 1;
            transform: scale(1);
            visibility: visible;
            pointer-events: auto;
        }

        #railway-legend-panel {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(26, 111, 196, 0.2);
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            min-width: 220px;
            max-width: 280px;
            overflow: hidden;
            pointer-events: auto;
            
            /* Animation */
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            transform-origin: bottom left;
            opacity: 1;
            transform: scale(1);
            visibility: visible;
            position: relative;
            z-index: 1;
        }

        #railway-legend.legend-collapsed #railway-legend-panel {
            opacity: 0;
            transform: scale(0.6) translateY(20px);
            visibility: hidden;
            pointer-events: none;
        }

        .railway-legend__header {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            background: #1a6fc4;
            border-bottom: 1px solid #1459a0;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.3px;
            user-select: none;
        }
        .railway-legend__header span {
            flex: 1;
        }
        #railway-legend-toggle {
            background: none;
            border: none;
            color: rgba(255,255,255,0.8);
            cursor: pointer;
            padding: 0 2px;
            font-size: 15px;
            transition: color 0.2s;
            line-height: 1;
        }
        #railway-legend-toggle:hover { color: #fff; }
        
        #railway-legend__body {
            max-height: 250px;
            overflow-y: auto;
        }
        
        /* Custom scrollbar for legend body */
        #railway-legend__body::-webkit-scrollbar {
            width: 5px;
        }
        #railway-legend__body::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
        }
        #railway-legend__body::-webkit-scrollbar-thumb {
            background: rgba(26, 111, 196, 0.4);
            border-radius: 10px;
        }
        #railway-legend__body::-webkit-scrollbar-thumb:hover {
            background: rgba(26, 111, 196, 0.6);
        }

        .railway-legend__grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 12px 14px;
        }
        .railway-legend__item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: #333;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.35;
        }
        .railway-legend__swatch {
            flex-shrink: 0;
            width: 24px;
            height: 6px;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            margin-top: 5px;
        }
        /* Mobile: 1 cột, góc dưới trái, nhỏ hơn */
        @media (max-width: 768px) {
            #railway-legend {
                top: 100px;
                bottom: auto;
                left: 10px;
            }
            .railway-legend__icon-btn {
                bottom: auto;
                top: 0;
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            #railway-legend-panel {
                transform-origin: top left;
                min-width: 180px;
                max-width: 240px;
                border-radius: 8px;
            }
            #railway-legend.legend-collapsed #railway-legend-panel {
                transform: scale(0.6) translateY(-20px);
            }
            .railway-legend__header {
                font-size: 12px;
                padding: 8px 10px;
            }
            #railway-legend__body {
                max-height: 200px;
            }
            .railway-legend__grid {
                gap: 10px;
                padding: 10px;
            }
            .railway-legend__item {
                font-size: 11.5px;
            }
        }
        /* RAILWAY STATION ICON */
        .railway-station-icon {
            background: #8b1d41;
            border: 2px solid #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            transition: all 0.3s ease;
        }
        .railway-station-icon:hover {
            transform: scale(1.2);
            background: #a3224d;
            z-index: 1000 !important;
        }
        .railway-station-icon i {
            font-size: 11px;
        }
        
        .station-popup .leaflet-popup-content-wrapper {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 8px;
            padding: 5px;
        }
        .station-popup__title {
            color: #8b1d41;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .station-popup__line {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            font-style: italic;
        }
        .station-popup__desc {
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        /* Làm nổi bật nút Layer Control của Leaflet */
        .leaflet-control-layers {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        
        .leaflet-control-layers-expanded {
            background: #fff !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15) !important;
            padding: 12px 18px !important;
            border: 1px solid rgba(26, 111, 196, 0.2) !important;
            margin: 10px !important;
            transform-origin: top right;
        }
        
        /* Ngăn lòi panel ngoài ý muốn */
        .leaflet-control-layers:not(.leaflet-control-layers-expanded) .leaflet-control-layers-list {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            max-height: 0 !important;
            pointer-events: none !important;
        }
        
        .leaflet-control-layers-expanded .leaflet-control-layers-list {
            display: block;
            animation: fadeInLayerList 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        
        @keyframes fadeInLayerList {
            0% {
                opacity: 0;
                transform: translateY(-8px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .leaflet-control-layers-toggle {
            width: 52px !important;
            height: 52px !important;
            border-radius: 50% !important;
            background-color: #1a6fc4 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 576 512'%3E%3Cpath fill='%23ffffff' d='M264.5 5.2c14.9-6.9 32.1-6.9 47 0l218.6 101c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 149.8C37.4 145.8 32 137.3 32 128s5.4-17.9 13.9-21.8L264.5 5.2zM476.9 209.6l53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 277.8C37.4 273.8 32 265.3 32 256s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0l152-70.2zM224.1 411.2c23.4 10.8 50.4 10.8 73.8 0l152-70.2 53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 411.8C37.4 407.8 32 399.3 32 390s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2z'/%3E%3C/svg%3E") !important;
            background-size: 26px 26px !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            box-shadow: 0 6px 20px rgba(26, 111, 196, 0.55) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease !important;
            animation: layer-bounce-idle 3s ease-in-out 1.5s infinite !important;
            transform-origin: center bottom;
        }

        .leaflet-control-layers-toggle:hover {
            background-color: #1459a0 !important;
            transform: scale(1.1) !important;
            box-shadow: 0 8px 28px rgba(26, 111, 196, 0.7) !important;
        }

        .leaflet-control-layers-toggle:active {
            transform: scale(0.93) !important;
            transition: transform 0.1s ease !important;
        }

        @keyframes layer-bounce-idle {
            0%   { transform: translateY(0) scale(1); }
            10%  { transform: translateY(-8px) scale(1.05); }
            20%  { transform: translateY(0px) scale(1); }
            30%  { transform: translateY(-4px) scale(1.02); }
            40%  { transform: translateY(0px) scale(1); }
            100% { transform: translateY(0) scale(1); }
        }
        
        .leaflet-control-layers-base label,
        .leaflet-control-layers-overlays label {
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            padding: 4px 0;
            display: flex;
            align-items: center;
        }
        
        .leaflet-control-layers-separator {
            border-top: 1px dashed #ccc !important;
            margin: 10px 0 !important;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    <script src="https://unpkg.com/@maplibre/maplibre-gl-leaflet@0.0.21/leaflet-maplibre-gl.js"></script>
    <script src="/js/industry-styles.js"></script>
    <script src="/js/boundaries.js"></script>
    <script src="/js/railways.js"></script>
    <script src="/js/railway_stations.js"></script>
    <script src="/js/railway_depots.js"></script>

    <script>
        $(document).ready(function() {
            const videoContainer = $('#video-loader-container');
            const loaderVideo = document.getElementById('loader-video');
            const skipBtn = $('#skip-video-btn');
            
            if (!loaderVideo) return;

            // Lấy ngày hiện tại YYYY-MM-DD
            const today = new Date().toISOString().split('T')[0];
            const hiddenDate = localStorage.getItem('hide_video_date');

            if (hiddenDate === today) {
                videoContainer.remove();
            } else {
                videoContainer.show();
                
                // Ngăn chặn bản đồ tranh chấp sự kiện click/tua của video
                if (typeof L !== 'undefined' && L.DomEvent) {
                    L.DomEvent.disableClickPropagation(videoContainer[0]);
                    L.DomEvent.disableScrollPropagation(videoContainer[0]);
                }

                // Tự động đóng khi video kết thúc
                loaderVideo.onended = function() {
                    closeVideoLoader();
                };
                
                // Đảm bảo video chạy nếu autoplay bị chặn
                loaderVideo.play().catch(error => {
                    console.log("Autoplay was prevented:", error);
                });
            }

            skipBtn.on('click', function() {
                loaderVideo.pause(); // Tạm dừng để hiện popup
                
                Swal.fire({
                    title: 'Bỏ qua video?',
                    text: "Bạn có muốn ẩn video này trong ngày hôm nay không?",
                    showCancelButton: true,
                    confirmButtonColor: '#1a6fc4',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy',
                    reverseButtons: true,
                    backdrop: `rgba(0,0,0,0.4)`
                }).then((result) => {
                    if (result.isConfirmed) {
                        localStorage.setItem('hide_video_date', today);
                        closeVideoLoader();
                    } else {
                        closeVideoLoader();
                    }
                });
            });

            function closeVideoLoader() {
                videoContainer.addClass('video-fade-out');
                setTimeout(() => {
                    videoContainer.remove();
                }, 800);
            }
        });

        // Tile layers
        // Vẫn sử dụng MapTiler làm mặc định nhưng được tối ưu hóa quá trình tải tile
        const defaults = L.tileLayer('https://api.maptiler.com/maps/outdoor-v2/{z}/{x}/{y}.png?key={{ config('services.maptiler.key') }}', {
            maxNativeZoom: 19,
            maxZoom: 21,
            updateWhenIdle: true,       // Chỉ fetch tiles sau khi người dùng ngừng kéo bản đồ
            updateWhenZooming: false,   // Không fetch liên tục trong quá trình đang zoom
            keepBuffer: 3               // Cache nhẹ thêm các tile xung quanh để khi quay lại không cần fetch lại
        });
        
        const streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxNativeZoom: 19,
            maxZoom: 21
        });
        
        const satellite = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxNativeZoom: 18,
                maxZoom: 21
            });
        const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxNativeZoom: 17,
            maxZoom: 21
        });

        // MapLibre GL 3D Layer
        const map3d = L.maplibreGL({
            style: 'https://api.maptiler.com/maps/streets-v2/style.json?key={{ config('services.maptiler.key') }}',
            updateInterval: 0 // Eliminate 30fps throttle to fix 3D panning jitter
        });

        // Add 3D building extrusion when MapLibre map is ready
        map3d.on('styleload', function() {
            const glMap = map3d.getMaplibreMap();
            if (glMap && !glMap.getLayer('3d-buildings')) {
                const layers = glMap.getStyle().layers;
                let labelLayerId;
                for (let i = 0; i < layers.length; i++) {
                    if (layers[i].type === 'symbol' && layers[i].layout['text-field']) {
                        labelLayerId = layers[i].id;
                        break;
                    }
                }

                glMap.addLayer({
                        'id': '3d-buildings',
                        'source': 'openmaptiles',
                        'source-layer': 'building',
                        'type': 'fill-extrusion',
                        'minzoom': 15,
                        'paint': {
                            'fill-extrusion-color': '#aaa',
                            'fill-extrusion-height': [
                                'interpolate', ['linear'],
                                ['zoom'],
                                15, 0,
                                15.05, ['get', 'render_height']
                            ],
                            'fill-extrusion-base': [
                                'interpolate', ['linear'],
                                ['zoom'],
                                15, 0,
                                15.05, ['get', 'render_min_height']
                            ],
                            'fill-extrusion-opacity': 0.6
                        }
                    },
                    labelLayerId
                );
            }

            // ── TRAFFIC SIMULATION ──────────────────────────────────────────
            (function initTrafficSimulation(glMap) {
                const VEHICLE_COUNT = 18;
                const MIN_ZOOM = 15;
                const PALETTE = [
                    '#ffffff', '#e0e0e0', '#ffd700', '#ff5555',
                    '#88ddff', '#99ee66', '#ffaa00', '#cc88ff'
                ];

                // ── Nguồn GeoJSON + layer circle (luôn hiển thị, ko cần icon) ─
                glMap.addSource('veh-src', {
                    type: 'geojson',
                    data: {
                        type: 'FeatureCollection',
                        features: []
                    }
                });

                // Vòng ngoài (thân xe)
                glMap.addLayer({
                    id: 'veh-body',
                    type: 'circle',
                    source: 'veh-src',
                    minzoom: MIN_ZOOM,
                    paint: {
                        'circle-radius': 5,
                        'circle-color': ['get', 'col'],
                        'circle-stroke-color': 'rgba(0,0,0,0.6)',
                        'circle-stroke-width': 1.2,
                        'circle-opacity': 0.92
                    }
                });

                // Điểm mũi xe (hướng di chuyển) – offset nhỏ theo bearing
                glMap.addLayer({
                    id: 'veh-head',
                    type: 'circle',
                    source: 'veh-src',
                    minzoom: MIN_ZOOM,
                    paint: {
                        'circle-radius': 2,
                        'circle-color': '#ffe700',
                        'circle-translate': ['literal', [4, 0]], // offset nhỏ
                        'circle-translate-anchor': 'map',
                        'circle-opacity': 0.95
                    }
                });

                // ── Sinh đường theo lưới thực tế từ view hiện tại ───────────
                function makeProceduralRoads() {
                    const c = glMap.getCenter();
                    const lat = c.lat,
                        lng = c.lng;
                    const R = 0.004; // ~400m
                    const out = [];

                    // Đường ngang
                    for (let i = -5; i <= 5; i++) {
                        const y = lat + i * R / 4;
                        const pts = [];
                        for (let j = -6; j <= 6; j++) {
                            pts.push([lng + j * R / 5, y]);
                        }
                        out.push(pts);
                    }
                    // Đường dọc
                    for (let i = -4; i <= 4; i++) {
                        const x = lng + i * R / 4;
                        const pts = [];
                        for (let j = -6; j <= 6; j++) {
                            pts.push([x, lat + j * R / 5]);
                        }
                        out.push(pts);
                    }
                    // Đường chéo nhẹ
                    out.push([
                        [lng - R, lat - R * 0.4],
                        [lng - R / 2, lat - R * 0.2],
                        [lng, lat],
                        [lng + R / 2, lat + R * 0.2],
                        [lng + R, lat + R * 0.4]
                    ]);
                    out.push([
                        [lng - R, lat + R * 0.35],
                        [lng, lat],
                        [lng + R, lat - R * 0.35]
                    ]);
                    return out;
                }

                // ── Cố gắng lấy đường từ vector tiles (nếu đã load) ─────────
                function tryGetTileRoads() {
                    let feats = [];
                    try {
                        // openmaptiles source, transportation source-layer
                        feats = glMap.querySourceFeatures('openmaptiles', {
                            sourceLayer: 'transportation'
                        });
                    } catch (e) {
                        /* source chưa sẵn sàng */ }

                    if (!feats.length) {
                        // fallback: tất cả line type features
                        try {
                            feats = glMap.queryRenderedFeatures();
                        } catch (e) {}
                    }

                    const seen = new Set();
                    const roads = [];
                    for (const f of feats) {
                        if (!f.geometry) continue;
                        const lines = f.geometry.type === 'LineString' ?
                            [f.geometry.coordinates] :
                            f.geometry.type === 'MultiLineString' ?
                            f.geometry.coordinates : [];
                        for (const line of lines) {
                            if (line.length < 2) continue;
                            const key = line[0][0].toFixed(4) + line[0][1].toFixed(4);
                            if (seen.has(key)) continue;
                            seen.add(key);
                            roads.push(line);
                            if (roads.length >= 60) break;
                        }
                        if (roads.length >= 60) break;
                    }
                    return roads;
                }

                // ── State ────────────────────────────────────────────────────
                let roads = [];
                let fleet = [];

                function calcBrg(a, b) {
                    const toR = Math.PI / 180,
                        toD = 180 / Math.PI;
                    const dL = (b[0] - a[0]) * toR;
                    const p1 = a[1] * toR,
                        p2 = b[1] * toR;
                    const y = Math.sin(dL) * Math.cos(p2);
                    const x = Math.cos(p1) * Math.sin(p2) - Math.sin(p1) * Math.cos(p2) * Math.cos(dL);
                    return (Math.atan2(y, x) * toD + 360) % 360;
                }

                function segM(a, b) {
                    const R = 6371000,
                        r = Math.PI / 180;
                    const dLat = (b[1] - a[1]) * r,
                        dLon = (b[0] - a[0]) * r;
                    const s = Math.sin(dLat / 2) ** 2 + Math.cos(a[1] * r) * Math.cos(b[1] * r) * Math.sin(
                        dLon / 2) ** 2;
                    return 2 * R * Math.asin(Math.sqrt(Math.min(1, s)));
                }

                function spawn() {
                    if (!roads.length) return null;
                    const road = roads[Math.floor(Math.random() * roads.length)];
                    if (!road || road.length < 2) return null;
                    return {
                        road,
                        seg: Math.floor(Math.random() * (road.length - 1)),
                        t: Math.random(),
                        spd: 6 + Math.random() * 12, // m/s  (≈20-65 km/h)
                        col: PALETTE[Math.floor(Math.random() * PALETTE.length)]
                    };
                }

                function initFleet() {
                    fleet = [];
                    for (let i = 0; i < VEHICLE_COUNT; i++) {
                        const v = spawn();
                        if (v) fleet.push(v);
                    }
                }

                // ── Animation loop ───────────────────────────────────────────
                let rafId = null;
                let lastTs = 0;

                function tick(ts) {
                    const dt = Math.min(ts - lastTs, 80);
                    lastTs = ts;

                    const feats = fleet.map(v => {
                        const a = v.road[v.seg],
                            b = v.road[v.seg + 1];
                        if (!a || !b) {
                            v.seg = 0;
                            v.t = 0;
                            return null;
                        }

                        const m = segM(a, b);
                        const step = m > 0 ? (v.spd * dt * 0.001) / m : 0.04;
                        v.t += step;
                        while (v.t >= 1) {
                            v.t -= 1;
                            v.seg = (v.seg + 1) % (v.road.length - 1);
                        }

                        const A = v.road[v.seg],
                            B = v.road[v.seg + 1] || A;
                        const lng = A[0] + (B[0] - A[0]) * v.t;
                        const lat = A[1] + (B[1] - A[1]) * v.t;

                        return {
                            type: 'Feature',
                            geometry: {
                                type: 'Point',
                                coordinates: [lng, lat]
                            },
                            properties: {
                                col: v.col,
                                brg: calcBrg(A, B)
                            }
                        };
                    }).filter(Boolean);

                    const src = glMap.getSource('veh-src');
                    if (src) src.setData({
                        type: 'FeatureCollection',
                        features: feats
                    });
                    glMap.triggerRepaint();
                    rafId = requestAnimationFrame(tick);
                }

                // ── Start / Stop ─────────────────────────────────────────────
                function start() {
                    if (rafId) return;

                    // Lấy đường: ưu tiên tile data, fallback procedural ngay lập tức
                    const tileRoads = tryGetTileRoads();
                    roads = tileRoads.length >= 5 ? tileRoads : makeProceduralRoads();

                    if (!fleet.length || fleet.every(v => !v.road)) initFleet();

                    lastTs = performance.now();
                    rafId = requestAnimationFrame(tick);

                    // Upgrade sang tile roads sau 2s (nếu chưa có)
                    if (tileRoads.length < 5) {
                        setTimeout(() => {
                            const tr = tryGetTileRoads();
                            if (tr.length >= 5) {
                                roads = tr;
                                fleet = []; // respawn trên đường thực
                                initFleet();
                            }
                        }, 2000);
                    }
                }

                function stop() {
                    if (rafId) {
                        cancelAnimationFrame(rafId);
                        rafId = null;
                    }
                    const src = glMap.getSource('veh-src');
                    if (src) src.setData({
                        type: 'FeatureCollection',
                        features: []
                    });
                    fleet = [];
                    roads = [];
                }

                // ── Lifecycle ────────────────────────────────────────────────
                function check() {
                    if (map.hasLayer(map3d) && map.getZoom() >= MIN_ZOOM) {
                        start();
                    } else {
                        stop();
                    }
                }

                // Bắt đầu ngay khi style/tiles sẵn sàng
                glMap.once('idle', () => setTimeout(check, 100));

                map.on('zoomend', () => setTimeout(check, 200));
                map.on('baselayerchange', () => setTimeout(check, 700));
                map.on('moveend', () => {
                    if (!rafId) return;
                    // Refresh đường khi pan
                    const tr = tryGetTileRoads();
                    if (tr.length >= 5) {
                        roads = tr;
                        // Re-spawn xe trên đường mới
                        fleet = fleet.map(() => spawn()).filter(Boolean);
                        while (fleet.length < VEHICLE_COUNT) {
                            const v = spawn();
                            if (v) fleet.push(v);
                        }
                    } else {
                        // Tạo lại procedural nếu pan xa
                        roads = makeProceduralRoads();
                        fleet = [];
                        initFleet();
                    }
                });

                // Bổ sung xe định kỳ nếu fleet thiếu
                setInterval(() => {
                    if (!rafId || !roads.length) return;
                    while (fleet.length < VEHICLE_COUNT) {
                        const v = spawn();
                        if (v) fleet.push(v);
                    }
                }, 2500);

            })(glMap);
            // ── END TRAFFIC SIMULATION ──────────────────────────────────────
        });

        const boundaryOverlayGroup = L.layerGroup();
        const railwayOverlayGroup = L.layerGroup();
        const stationOverlayGroup = L.layerGroup();
        const depotOverlayGroup = L.layerGroup();
        let districtDisplayNames = {}; // Map of VI Name -> Localized Name

        const baseLayers = {
            "{{ __('app.default_map') }}": defaults,
            "{{ __('app.traffic_map') }}": streets,
            "{{ __('app.satellite_map') }}": satellite,
            "{{ __('app.topo_map') }}": topo,
            "{{ __('app.map_3d') }}": map3d
        };

        // ── RAILWAY FOCUS STATE ──────────────────────────────────────────
        let activeRailwayName = null;
        const railwayPolylines = {}; // Phụ trợ để tìm polyline nhanh từ legend

        function updateRailwayStyles() {
            railwayOverlayGroup.eachLayer(layer => {
                if (!(layer instanceof L.Polyline)) return;
                
                const name = layer.options.railwayName;
                
                // Cấu hình mặc định
                let weight = 4;
                let opacity = 0.8;
                
                if (activeRailwayName) {
                    if (name === activeRailwayName) {
                        weight = 8; // Tuyến đang chọn thì đậm lên
                        opacity = 1;
                        layer.bringToFront();
                    } else {
                        weight = 3;
                        opacity = 0.35; // Làm mờ các tuyến khác
                    }
                }
                
                layer.setStyle({ weight, opacity });
            });
        }

        // ── RAILWAY LEGEND ────────────────────────────────────────────────
        function buildRailwayLegend() {
            const container = document.getElementById('railway-legend-items');
            if (!container) return;
            container.innerHTML = '';
            Object.entries(railways).forEach(([name, data]) => {
                const item = document.createElement('div');
                item.className = 'railway-legend__item';
                item.style.cursor = 'pointer';
                item.innerHTML = `
                    <span class="railway-legend__swatch" style="background:${data.color};"></span>
                    <span>${name}</span>
                `;

                // Click vào tên trong legend để focus
                item.onclick = (e) => {
                    e.stopPropagation();
                    activeRailwayName = name;
                    updateRailwayStyles();
                    renderRailwayStations(name, data);
                    renderRailwayDepots(name);
                    
                    const targetPoly = railwayPolylines[name];
                    if (targetPoly) {
                        map.fitBounds(targetPoly.getBounds(), { padding: [50, 50] });
                    }
                };

                container.appendChild(item);
            });
        }

        function showRailwayLegend() {
            const legend = document.getElementById('railway-legend');
            if (legend) {
                buildRailwayLegend();
                legend.classList.remove('legend-collapsed');
                legend.style.display = 'block';
            }
        }

        function hideRailwayLegend() {
            const legend = document.getElementById('railway-legend');
            if (legend) legend.style.display = 'none';
        }

        // Toggle thu gọn/mở rộng body legend
        document.addEventListener('click', function(e) {
            if (e.target.closest('#railway-legend-toggle')) {
                const legend = document.getElementById('railway-legend');
                if (legend) legend.classList.add('legend-collapsed');
            }
            if (e.target.closest('#railway-legend-icon')) {
                const legend = document.getElementById('railway-legend');
                if (legend) legend.classList.remove('legend-collapsed');
            }
        });

        // ── END RAILWAY LEGEND ───────────────────────────────────────────────

        function renderRailwayLayers() {
            railwayOverlayGroup.clearLayers();
            Object.entries(railways).forEach(([name, data]) => {
                const poly = L.polyline(data.coords, {
                    color: data.color,
                    weight: 4,
                    opacity: 0.8,
                    lineJoin: 'round',
                    interactive: true,
                    railwayName: name
                });
                
                railwayPolylines[name] = poly; // Lưu lại để legend có thể truy cập
                
                poly.bindTooltip(name, {
                    sticky: true,
                    direction: 'top',
                    className: 'railway-tooltip'
                });

                poly.on('click', function(e) {
                    L.DomEvent.stopPropagation(e);
                    
                    // Nếu đã đang focus chính tuyến này, có thể toggle off hoặc giữ nguyên
                    // Ở đây mặc định là chọn tuyến này
                    activeRailwayName = name;
                    updateRailwayStyles();
                    
                    renderRailwayStations(name, data);
                    renderRailwayDepots(name);
                    
                    if (map.getZoom() < 13) {
                        map.fitBounds(this.getBounds(), { padding: [50, 50] });
                    }
                });

                railwayOverlayGroup.addLayer(poly);
            });
        }

        function renderRailwayDepots(selectedName = null) {
            depotOverlayGroup.clearLayers();
            if (!selectedName || typeof railwayDepots === 'undefined') return;

            const filteredDepots = railwayDepots.filter(d => {
                return selectedName.includes(d.line) || d.line.includes(selectedName);
            });

            filteredDepots.forEach(d => {
                const poly = L.polygon(d.coords, {
                    color: '#8b1d41',
                    weight: 2,
                    dashArray: '5, 5',
                    fillColor: '#8b1d41',
                    fillOpacity: 0.2,
                    interactive: true
                });

                poly.bindTooltip(d.name, {
                    sticky: true,
                    direction: 'top',
                    className: 'boundary-tooltip'
                });

                poly.bindPopup(`
                    <div class="station-popup">
                        <div class="station-popup__title">
                            <i class="fas fa-warehouse"></i> ${d.name}
                        </div>
                        <div class="station-popup__line">${d.line}</div>
                        <div class="station-popup__desc">${d.desc}</div>
                    </div>
                `);

                depotOverlayGroup.addLayer(poly);
            });

            if (!map.hasLayer(depotOverlayGroup)) {
                depotOverlayGroup.addTo(map);
            }
        }

        function renderBoundaryLayers() {
            if (typeof boundaries === 'undefined') return;
            boundaryOverlayGroup.clearLayers();
            
            Object.entries(boundaries).forEach(([nameVi, coords]) => {
                const displayName = districtDisplayNames[nameVi] || nameVi;
                const poly = L.polygon(coords, {
                    color: '#1a6fc4',
                    weight: 1.5,
                    dashArray: '4, 4',
                    fillColor: '#4a9ede',
                    fillOpacity: 0.08,
                    interactive: true
                });
                poly.bindTooltip(displayName, {
                    sticky: true,
                    direction: 'top',
                    className: 'boundary-tooltip'
                });
                poly.on('mouseover', function() {
                    this.setStyle({ fillOpacity: 0.25, weight: 2.5 });
                });
                poly.on('mouseout', function() {
                    this.setStyle({ fillOpacity: 0.08, weight: 1.5 });
                });
                boundaryOverlayGroup.addLayer(poly);
            });
        }
        
        // Initial render with static data
        function renderRailwayStations(selectedName = null, selectedData = null) {
            stationOverlayGroup.clearLayers();
            
            if (!selectedName || !selectedData) return;
            
            // Helper to snap a point to the nearest line coordinate
            function snapToLine(lat, lng, lineData) {
                if (!lineData || !lineData.coords) return [lat, lng];
                
                // Flatten coordinates to get a simple array of [lat, lng]
                let coords = lineData.coords;
                if (Array.isArray(coords[0]) && Array.isArray(coords[0][0])) {
                    coords = coords.flat(1);
                }

                let nearest = [lat, lng];
                let minD = Infinity;
                
                for (let i = 0; i < coords.length; i++) {
                    const p = coords[i];
                    const d = Math.pow(p[0] - lat, 2) + Math.pow(p[1] - lng, 2);
                    if (d < minD) {
                        minD = d;
                        nearest = p;
                    }
                }
                return nearest;
            }

            const targetLine = selectedData;

            // Note: railwayStations variable is now loaded from /js/railway_stations.js
            if (typeof railwayStations === 'undefined') {
                console.error("railwayStations data not loaded");
                return;
            }

            // Filter stations that match the clicked line name
            // Logic: if selectedName is "ĐSĐT số 3.1", it matches "ĐSĐT số 3"
            const filteredStations = railwayStations.filter(st => {
                return selectedName.includes(st.line) || st.line.includes(selectedName);
            });

            filteredStations.forEach(st => {
                // Snap the coordinates to the nearest point on the line
                const snapped = snapToLine(st.lat, st.lng, targetLine);
                
                const icon = L.divIcon({
                    className: 'railway-station-icon',
                    html: '<i class="fas fa-train"></i>',
                    iconSize: [26, 26],
                    iconAnchor: [13, 13],
                    popupAnchor: [0, -13]
                });

                const marker = L.marker(snapped, { icon: icon });
                
                const popupContent = `
                    <div class="station-popup">
                        <div class="station-popup__title">
                            <i class="fas fa-train"></i> ${st.name}
                        </div>
                        <div class="station-popup__line">${st.line}</div>
                        <div class="station-popup__desc">${st.desc}</div>
                    </div>
                `;
                
                marker.bindPopup(popupContent, {
                    className: 'station-popup',
                    maxWidth: 220
                });
                
                stationOverlayGroup.addLayer(marker);
            });
            
            if (!map.hasLayer(stationOverlayGroup)) {
                stationOverlayGroup.addTo(map);
            }
        }

        renderBoundaryLayers();
        renderRailwayLayers();
        // renderRailwayStations(); // Hidden by default, shown on click

        const overlayLayers = {
            "{{ __('app.boundary_map') }}": boundaryOverlayGroup,
            "{{ __('app.railway_map') }}": railwayOverlayGroup
        };

        const defaultCenter = [21.0285, 105.8542];
        const defaultZoom = 12;

        // Toạ độ giới hạn vùng Hà Nội (tương đối chính xác)
        const bounds = L.latLngBounds(
            [20.4, 104.9],
            [21.7, 106.4]
        );

        // Tạo bản đồ và giới hạn vùng
        const map = L.map('map', {
            center: defaultCenter,
            zoom: defaultZoom,
            layers: [defaults],
            maxBounds: bounds, // Giới hạn không cho pan ra khỏi vùng này
            maxBoundsViscosity: 1.0, // 1.0 = không bao giờ cho kéo ra ngoài
            minZoom: 10, // Không cho zoom out thấp hơn mức này
            maxZoom: 21, // Cho phép zoom in sâu hơn (đến mức 21)
            attributionControl: false,
            keyboard: false // Disable default keyboard panning to allow custom 3D rotation
        });

        // Click ra ngoài bản đồ để bỏ chọn (reset focus)
        map.on('click', function() {
            activeRailwayName = null;
            updateRailwayStyles();
            stationOverlayGroup.clearLayers(); // Ẩn các nhà ga khi bỏ chọn tuyến
            depotOverlayGroup.clearLayers();   // Ẩn các depot khi bỏ chọn tuyến
            if (window._currentProjectBoundaryPolygon) {
                map.removeLayer(window._currentProjectBoundaryPolygon);
                window._currentProjectBoundaryPolygon = null;
            }
            if (window.layerControl && window.layerControl.forceCollapse) {
                window.layerControl.forceCollapse();
            }
        });

        // Ngăn chặn sự kiện cuộn/nhấp chuột truyền từ legend xuống bản đồ
        if (typeof L !== 'undefined') {
            const legendEl = document.getElementById('railway-legend');
            if (legendEl) {
                L.DomEvent.disableClickPropagation(legendEl);
                L.DomEvent.disableScrollPropagation(legendEl);
            }
        }

        // Auto-tilt logic for Map3D - Optimized
        let tilingTicking = false;

        function updateTilt() {
            if (map.hasLayer(map3d)) {
                const z = map.getZoom();
                const glMap = map3d.getMaplibreMap();
                if (glMap) {
                    // Start tilting at zoom 15, max tilt (60 degrees) at zoom 19+
                    let pitch = 0;
                    if (z >= 15) {
                        pitch = Math.min(60, (z - 15) * 15);
                    }
                    // Only update if pitch actually changed to save performance
                    if (glMap.getPitch() !== pitch) {
                        glMap.setPitch(pitch);
                    }
                }
            }
            tilingTicking = false;
        }

        // Only update tilt on zoom to prevent lag during movement (panning)
        map.on('zoomend', function() {
            if (!tilingTicking) {
                requestAnimationFrame(updateTilt);
                tilingTicking = true;
            }
        });

        // 3D Rotation and Tilt Controls via Keyboard & Smooth Ease
        function rotate3D(bearingDelta, pitchDelta) {
            if (!map.hasLayer(map3d)) return;
            const glMap = map3d.getMaplibreMap();
            if (!glMap) return;

            const currentBearing = glMap.getBearing();
            const currentPitch = glMap.getPitch();

            glMap.easeTo({
                bearing: currentBearing + bearingDelta,
                pitch: Math.min(85, Math.max(0, currentPitch + pitchDelta)),
                duration: 200,
                easing: (t) => t
            });
        }

        const redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });


        // Thêm nút reset bản đồ
        const resetControl = L.control({
            position: window.innerWidth <= 1024 ? 'topright' : 'bottomright'
        });
        const currentLocation = L.control({
            position: window.innerWidth <= 1024 ? 'topright' : 'bottomright'
        });
        const fullScreenControl = L.control({
            position: window.innerWidth <= 1024 ? 'topright' : 'bottomright'
        });

        window.addEventListener('resize', () => {
            const newPosition = window.innerWidth <= 1024 ? 'topright' : 'bottomright';
            resetControl.setPosition(newPosition);
            currentLocation.setPosition(newPosition);
            fullScreenControl.setPosition(newPosition);

            resetControl.remove();
            currentLocation.remove();
            fullScreenControl.remove();

            if (newPosition === 'topright') {
                // Muốn C -> B -> A trên mobile (hiển thị dưới cùng lên)
                fullScreenControl.addTo(map);
                currentLocation.addTo(map);
                resetControl.addTo(map);
            } else {
                // Muốn A -> B -> C trên web
                resetControl.addTo(map);
                currentLocation.addTo(map);
                fullScreenControl.addTo(map);
            }
        });

        resetControl.onAdd = function(map) {
            const btn = L.DomUtil.create('button', 'leaflet-bar leaflet-control leaflet-control-custom');
            btn.innerHTML = '<i class="fas fa-redo-alt"></i>';
            btn.title = '{{ __('app.reset_map') }}';

            btn.style.backgroundColor = 'white';
            btn.style.width = '48px';
            btn.style.height = '48px';
            btn.style.cursor = 'pointer';
            btn.style.fontSize = '18px';
            btn.style.lineHeight = '30px';
            btn.style.textAlign = 'center';
            btn.style.margin = '10px'; // Thêm khoảng cách khỏi mép dưới/phải

            L.DomEvent.disableClickPropagation(btn);

            btn.onclick = function() {
                resetMap();
            };

            return btn;
        };

        resetControl.addTo(map);

        currentLocation.onAdd = function(map) {
            const btn = L.DomUtil.create('button', 'leaflet-bar leaflet-control leaflet-control-custom');
            btn.innerHTML = '<i class="fas fa-crosshairs"></i>';
            btn.title = '{{ __('app.current_location') }}';

            btn.style.backgroundColor = 'white';
            btn.style.width = '48px';
            btn.style.height = '48px';
            btn.style.margin = '0px';
            btn.style.cursor = 'pointer';
            btn.style.fontSize = '18px';
            btn.style.lineHeight = '30px';
            btn.style.textAlign = 'center';
            btn.style.margin = '10px';
            btn.style.marginBottom = '0';

            L.DomEvent.disableClickPropagation(btn);

            btn.onclick = function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const latLng = [position.coords.latitude, position.coords.longitude];
                        map.setView(latLng, 16);

                        if (map._currentLocationMarker) {
                            map.removeLayer(map._currentLocationMarker);
                        }
                        map._currentLocationMarker = L.marker(latLng, {
                                icon: redIcon
                            }).addTo(map)
                            .bindPopup("{{ __('app.current_location') }}")
                            .openPopup();

                    }, function() {
                        alert('{{ __('app.unable_to_get_current_location') }}');
                    });
                } else {
                    alert('{{ __('app.browser_not_support_geolocation') }}');
                }
            };

            return btn;
        };

        currentLocation.addTo(map);

        fullScreenControl.onAdd = function(map) {
            const btn = L.DomUtil.create('button', 'leaflet-bar leaflet-control leaflet-control-custom');
            btn.innerHTML = '<i class="fas fa-expand"></i>';
            btn.title = '{{ __('app.full_screen') }}';

            btn.style.backgroundColor = 'white';
            btn.style.width = '48px';
            btn.style.height = '48px';
            btn.style.margin = '0px';
            btn.style.cursor = 'pointer';
            btn.style.fontSize = '18px';
            btn.style.lineHeight = '30px';
            btn.style.textAlign = 'center';
            btn.style.margin = '10px';
            btn.style.marginBottom = '0';

            L.DomEvent.disableClickPropagation(btn);

            btn.onclick = function() {
                const mapElement = document.getElementById('map');

                if (!document.fullscreenElement) {
                    if (mapElement.requestFullscreen) {
                        mapElement.requestFullscreen();
                    } else if (mapElement.mozRequestFullScreen) {
                        /* Firefox */
                        mapElement.mozRequestFullScreen();
                    } else if (mapElement.webkitRequestFullscreen) {
                        /* Chrome, Safari & Opera */
                        mapElement.webkitRequestFullscreen();
                    } else if (mapElement.msRequestFullscreen) {
                        /* IE/Edge */
                        mapElement.msRequestFullscreen();
                    }
                    btn.innerHTML = '<i class="fas fa-compress"></i>';
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    }
                    btn.innerHTML = '<i class="fas fa-expand"></i>';
                }
            };

            // Lắng nghe sự kiện thoát full screen (ví dụ: nhấn ESC) để cập nhật lại icon
            document.addEventListener('fullscreenchange', exitHandler);
            document.addEventListener('webkitfullscreenchange', exitHandler);
            document.addEventListener('mozfullscreenchange', exitHandler);
            document.addEventListener('MSFullscreenChange', exitHandler);

            function exitHandler() {
                if (!document.fullscreenElement && !document.webkitIsFullScreen && !document.mozFullScreen && !document
                    .msFullscreenElement) {
                    btn.innerHTML = '<i class="fas fa-expand"></i>';
                }
            }

            return btn;
        };

        fullScreenControl.addTo(map);

        function resetMap() {
            resetProjectTab();
            resetIndustrialTab();
            if (map._currentLocationMarker) {
                map.removeLayer(map._currentLocationMarker);
                map._currentLocationMarker = null;
            }
            map.setView(defaultCenter, defaultZoom);
            isMapTriggered = true;
            applyFiltersWithBounds();
        }

        // Sửa Prototype của Leaflet để ngăn sự kiện di chuột tự động mở bằng JS
        if (!window._patchedLeafletLayers) {
            const proto = L.Control.Layers.prototype;
            const originalEx = proto._expand;
            const originalCol = proto._collapse;
            
            proto._expand = function() {
                if (this._clickToOpen) originalEx.call(this);
            };
            proto._collapse = function() {
                if (this._clickToClose) originalCol.call(this);
            };
            window._patchedLeafletLayers = true;
        }

        // Khởi tạo bình thường (không dùng collapsed: false để tránh phá DOM Leaflet)
        window.layerControl = L.control.layers(baseLayers, overlayLayers).addTo(map);
        
        // Reset trạng thái flag an toàn
        window.layerControl._clickToOpen = false;
        window.layerControl._clickToClose = false;
        
        // Public method an toàn để map onClick bên ngoài có thể gọi force form collapse
        window.layerControl.forceCollapse = function() {
            window.layerControl._clickToClose = true;
            window.layerControl._collapse();
            window.layerControl._clickToClose = false;
        };
        
        // Lắng nghe sự kiện click thay vì hover
        (function setupLayerControlClick() {
            const lcContainer = window.layerControl.getContainer();
            const toggleBtn = lcContainer.querySelector('.leaflet-control-layers-toggle');
            
            if(toggleBtn) {
                L.DomEvent.on(toggleBtn, 'click', function(e) {
                    L.DomEvent.stopPropagation(e);
                    L.DomEvent.preventDefault(e);
                    
                    if (L.DomUtil.hasClass(lcContainer, 'leaflet-control-layers-expanded')) {
                        window.layerControl.forceCollapse();
                    } else {
                        window.layerControl._clickToOpen = true;
                        window.layerControl._expand();
                        window.layerControl._clickToOpen = false;
                    }
                });
            }
            
            L.DomEvent.on(lcContainer, 'click', function(e){
                L.DomEvent.stopPropagation(e);
            });
        })();
        
        // railwayOverlayGroup.addTo(map); 
        // stationOverlayGroup.addTo(map); // Optional: if you want stations to persist toggle separately
        map.on('overlayadd', function(e) {
            if (e.name === "{{ __('app.railway_map') }}") {
                showRailwayLegend();
            }
        });
        map.on('overlayremove', function(e) {
            if (e.name === "{{ __('app.railway_map') }}") {
                hideRailwayLegend();
            }
        });

        let markersLayer = L.markerClusterGroup();
        let allDistricts = [];
        let allDistrictsLoaded = false;
        let boundaryPolygon = null;
        let currentDistrict = null;
        let isMapTriggered = false;

        function removeDiacritics(str) {
            return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        }

        function getTypeName(typeNumber) {
            const types = {
                1: "{{ __('app.public_private_partnership') }}",
                2: "{{ __('app.off_budget_capital') }}",
                3: "{{ __('app.public_investment') }}",
            };
            return types[typeNumber] || "{{ __('app.unknown') }}";
        }

        // Lấy ngôn ngữ hiện tại
        const currentLang = (window.APP_LANG || document.documentElement.lang || navigator.language || 'vi').toLowerCase();
        const locale = (currentLang.startsWith('vi') || currentLang.startsWith('vn')) ?
            'vi-VN' :
            'en-US';

        // Format số chung (area, length, ...)
        const numberFormatter = new Intl.NumberFormat(locale, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });

        function fmtNumber(value) {
            if (value === null || value === undefined || value === '') return '';
            const n = Number(value);
            if (!isFinite(n)) return '';
            return numberFormatter.format(n);
        }

        // Format riêng cho giá trị tiền (tỷ đồng)
        function fmtPrice(value) {
            if (value === null || value === undefined || value === '') return '{{ __('app.no_price') }}';
            const n = Number(value);
            if (!isFinite(n)) return '{{ __('app.no_price') }}';
            return numberFormatter.format(n);
        }

        function createDropIcon(bgColor = "#2a84d0", imgUrl = "") {
            return L.divIcon({
                className: "custom-drop-marker",
                html: `
                    <svg width="32" height="46" viewBox="0 0 32 46" xmlns="http://www.w3.org/2000/svg">
                        <!-- Hình giọt nước -->
                        <path d="M16 0C7.2 0 0 7.5 0 16.7C0 28.3 16 46 16 46C16 46 32 28.3 32 16.7C32 7.5 24.8 0 16 0Z" fill="${bgColor}" />
                        <!-- Vòng trắng bên trong -->
                        <circle cx="16" cy="17" r="10" fill="white" />
                        <!-- Ảnh chèn vào -->
                        <image href="${imgUrl}" x="9" y="10" height="14" width="14" preserveAspectRatio="xMidYMid meet"/>
                    </svg>
                `,
                iconAnchor: [16, 46],
                popupAnchor: [0, -46]
            });
        }

        function createMarker(loc) {
            const style = industryStyles[loc.industry_number];

            let marker;
            if (style) {
                // Nếu is_invest = 0 → đổi sang màu đỏ
                const markerColor = (loc.is_invest === 1) ? "#d9534f" : style.color;

                marker = L.marker([loc.lat, loc.lng], {
                    icon: createDropIcon(markerColor, style.icon)
                });
            } else {
                // fallback mặc định
                marker = L.marker([loc.lat, loc.lng]);
            }

            const detailUrl = loc.link;
            const tourUrl = loc.link_vrtour;
            let tourButtonHtml = '';
            if (tourUrl) {
                if (tourUrl !== 'null' && tourUrl.trim() !== '') {
                    tourButtonHtml =
                        `<a href="${tourUrl}" target="_blank" class="btn btn-sm btn-secondary text-white">{{ __('app.virtual_tour') }}</a>`;
                }
            }

            const districtText = Array.isArray(loc.districts) ?
                loc.districts.join(", ") :
                loc.district || "{{ __('app.unknown') }}";

            const priceText = fmtPrice(loc.price);

            const imageUrl = `${window.location.origin}${loc.detail_image}`;

            // Xử lý diện tích/chiều dài
            let areaHtml = '';
            if (loc.area !== null && loc.area !== undefined && loc.area !== '') {
                const areaText = fmtNumber(loc.area);
                if (loc.unit === 'ha') {
                    areaHtml = `{{ __('app.area') }}: ${areaText} ha`;
                } else if (loc.unit === 'km') {
                    areaHtml = `{{ __('app.length') }}: ${areaText} km`;
                }
            }

            const popupContent = `
        <div class='info-box' style="max-width:250px;">
            <img src="${imageUrl}" alt="${loc.name}" style="width:100%; height:120px; object-fit:cover; border-radius:6px; margin-bottom:8px;">
            <strong>${loc.name}</strong><br>
            {{ __('app.investment_form') }}: ${getTypeName(loc.type_number)}<br>
            {{ __('app.zone') }}: ${districtText}<br>
            {{ __('app.investment_scale') }}: ${priceText} {{ __('app.billion_vnd') }}<br>
            ${areaHtml}
            <div style="margin-top:10px; display:flex; gap:8px; justify-content:flex-end;">
                ${tourButtonHtml}
                <a href="${detailUrl}" target="_blank" class="btn btn-sm btn-primary text-white">{{ __('app.information') }}</a>
            </div>
        </div>
    `;

            marker.bindPopup(popupContent);

            if (loc.boundary) {
                marker.on('click', function() {
                    if (window._currentProjectBoundaryPolygon) {
                        map.removeLayer(window._currentProjectBoundaryPolygon);
                        window._currentProjectBoundaryPolygon = null;
                    }
                    try {
                        const coords = JSON.parse(loc.boundary);
                        if (coords && coords.length > 0) {
                            window._currentProjectBoundaryPolygon = L.polygon(coords, {
                                color: '#e65100',
                                weight: 2,
                                dashArray: '5, 5',
                                fillColor: '#ffa726',
                                fillOpacity: 0.2,
                                interactive: true
                            }).addTo(map);
                            
                            if (window._currentProjectBoundaryPolygon) {
                                window._currentProjectBoundaryPolygon.bindTooltip(loc.name, {
                                    sticky: true,
                                    direction: 'top',
                                    className: 'boundary-tooltip'
                                });
                            }
                        }
                    } catch (e) {
                        console.error("Lỗi vẽ toạ độ boundary dự án:", e);
                    }
                });
            }

            return marker;
        }

        function loadMarkers(data, triggeredBySearch = false) {
            markersLayer.clearLayers();

            const filtered = data.filter(loc => loc.lat && loc.lng && Array.isArray(loc.districts) && loc.districts.length >
                0);
            const markers = filtered.map(loc => createMarker(loc));

            if (markers.length === 0) return;

            markersLayer.addLayers(markers);
            map.addLayer(markersLayer);
            if (!triggeredBySearch) return;

            if (markers.length === 1) {
                const latLng = markers[0].getLatLng();
                map.flyTo(latLng, 16); // hoặc 15 tuỳ layout
            } else {
                const group = new L.featureGroup(markers);
                const bounds = group.getBounds();

                if (!map.getBounds().contains(bounds)) {
                    map.fitBounds(bounds, {
                        padding: [50, 50],
                        maxZoom: 16
                    });
                } else {
                    // Nếu đã nằm trong màn hình, chỉ pan nhẹ đến giữa
                    map.panTo(bounds.getCenter());
                }
            }
        }

        function drawDistrictBoundary(districtName) {
            if (boundaryPolygon) {
                map.removeLayer(boundaryPolygon);
                boundaryPolygon = null;
            }

            if (districtName === "all") return;
            
            // `districtName` currently could be the translated name or the VI name.
            // When selecting from dropdown, we'll try to pass the VI name as data-value.
            // If it's passed directly as translated name, let's map it back to VI name.
            let viName = districtName;
            
            // Reverse lookup if the name isn't found in boundaries
            if (!boundaries[viName]) {
                const foundEntry = Object.entries(districtDisplayNames).find(([vi, loc]) => loc === viName);
                if (foundEntry) {
                    viName = foundEntry[0];
                }
            }

            if (!boundaries[viName]) return;

            boundaryPolygon = L.polygon(boundaries[viName], {
                color: "blue",
                weight: 2,
                dashArray: "5, 5",
                fill: false
            }).addTo(map);

            map.flyToBounds(boundaryPolygon.getBounds(), {
                duration: 0.5,
                easeLinearity: 0.5
            });
        }

        function applyFiltersWithBounds() {
            const triggeredByMap = isMapTriggered;
            isMapTriggered = false;

            const bounds = map.getBounds();
            const activeTab = $('#projectTabContent').css('display') === 'none' ? 'industrial' : 'project';
            const selectedDistrict = activeTab === 'industrial' ? $('#districtFilterSp').val() : $('#districtFilter').val();
            const searchTerm = activeTab === 'industrial' ? $('#searchInputSp').val() : $('#searchInput').val();
            const priceRange = activeTab === 'industrial' ? $('#priceRangeSp').val() : $('#priceRange').val();
            const projectId = $('#project_id').val();
            const productType = $('#product_type').val();
            const selectedType = $('#typeFilter').val();
            const industryFilter = $('#industryFilter').val();

            if (activeTab === 'industrial') {
                const hasFilters = [
                    selectedDistrict,
                    searchTerm,
                    projectId !== "all" ? true : null,
                    parseInt(priceRange) > 0 ? true : null,
                    productType !== "all" ? true : null
                ].some(val => val && val !== "");

                if (!hasFilters && !triggeredByMap) {
                    return;
                }
            }

            const params = {
                minLat: bounds.getSouth(),
                maxLat: bounds.getNorth(),
                minLng: bounds.getWest(),
                maxLng: bounds.getEast(),
                tab: activeTab,
                ...(activeTab === 'industrial' ? {
                    district: selectedDistrict,
                    search: searchTerm,
                    project_id: projectId,
                    price: priceRange,
                    product_type: productType
                } : {
                    type: selectedType,
                    district: selectedDistrict,
                    search: searchTerm,
                    price: priceRange,
                    industry: industryFilter
                })
            };
            let lang = document.documentElement.lang || 'vn';
            if (lang === 'vi') lang = 'vn';

            $.ajax({
                url: `/${lang}/map/bounds`,
                method: 'GET',
                data: params,
                success: function(data) {
                    loadMarkers(data, !triggeredByMap);
                    const allIndustrial = [];
                    data.forEach(project => {
                        (project.industrial || []).forEach(item => {
                            allIndustrial.push({
                                ...item,
                                project_name: project.name,
                                link: item.link || `/chi-tiet/${item.id}`
                            });
                        });
                    });

                    window.industrialResults = allIndustrial;
                    renderList(1);

                    if (activeTab === "industrial" && !triggeredByMap) {
                        $('#filterResultModal').modal('show');
                    }

                    if (selectedDistrict && selectedDistrict !== "all") {
                        if (selectedDistrict !== currentDistrict) {
                            drawDistrictBoundary(selectedDistrict);
                            currentDistrict = selectedDistrict;
                        }
                    } else if (boundaryPolygon) {
                        map.removeLayer(boundaryPolygon);
                        boundaryPolygon = null;
                        currentDistrict = null;
                    }
                },
                error: function(err) {
                    console.error("{{ __('app.error_loading_data') }}:", err);
                }
            });
        }

        function renderList(page = 1) {
            const itemsPerPage = 4;
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const allItems = window.industrialResults || [];
            const items = allItems.slice(start, end);
            const $resultList = $("#resultList");

            if (allItems.length === 0) {
                resultList.innerHTML = `
                            <li class="list-group-item text-muted justify-content-center">
                                {{ __('app.no_matching_results') }}
                            </li>
                        `;
                $('#pagination').empty();
                return;
            }

            const labels = {
                projects: '{{ __('app.projects') }}',
                code: '{{ __('app.code') }}',
                area: '{{ __('app.area') }}',
                projectTypes: '{{ __('app.project_types') }}',
                unknown: '{{ __('app.unknown') }}',
                location: '{{ __('app.location') }}',
                nameProject: '{{ __('app.name_project') }}',
                lot: '{{ __('app.lot') }}',
            };

            resultList.innerHTML = items.map(item => `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${item.intended_use ?? labels.unknown}</strong><br>
                        <small>${labels.projects}: ${item.project_name}</small> - ${labels.code}: ${item.code.replace(/^cmss_/, '')}<br>
                        <small>${labels.nameProject}: ${labels.lot} ${item.code.replace(/^cmss_/, '') ?? labels.unknown} - ${labels.area}: ${item.acreage ?? labels.unknown} ${item.unit} - ${labels.projectTypes}: ${item.product_type_name ?? labels.unknown}</small>
                    </div>
                    <a href="${item.link}" target="_blank" class="btn custom-btn btn-sm">${labels.location}</a>
                </li>
            `).join('');

            renderPagination(page);
        }


        function renderPagination(currentPage) {
            const itemsPerPage = 5;
            const totalItems = (window.industrialResults || []).length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const $pagination = $("#pagination");
            pagination.innerHTML = "";

            if (totalPages <= 1) return;

            function createPageBtn(label, pageNum, active = false) {
                const $li = $("<li></li>").addClass(`page-item ${active ? "active" : ""}`).html(
                    `<button class="page-link" ${active ? "disabled" : ""}>${label}</button>`);
                if (!active && pageNum !== null) {
                    $li.on("click", () => renderList(pageNum));
                }
                $("#pagination").append($li);
            }

            // Previous
            const prevPage = currentPage === 1 ? totalPages : currentPage - 1;
            createPageBtn("«", prevPage);

            if (totalPages >= 5) {
                // Looping pagination
                const loopPages = [];
                for (let i = -2; i <= 2; i++) {
                    let page = ((currentPage - 1 + i + totalPages) % totalPages) + 1;
                    if (!loopPages.includes(page)) loopPages.push(page); // tránh trùng
                }
                loopPages.forEach(p => {
                    createPageBtn(p, p, p === currentPage);
                });
            } else {
                // Hiển thị tất cả nếu < 5
                for (let i = 1; i <= totalPages; i++) {
                    createPageBtn(i, i, i === currentPage);
                }
            }

            // Next
            const nextPage = currentPage === totalPages ? 1 : currentPage + 1;
            createPageBtn("»", nextPage);
        }

        function loadAllDistricts() {
            let lang = document.documentElement.lang || 'vn';
            if (lang === 'vi') lang = 'vn';
            $.ajax({
                url: `/${lang}/api/districts`,
                method: 'GET',
                success: function(res) {
                    // Extract objects for the search dropdown containing both display and VI names
                    allDistricts = res.map(d => ({ name: d.name, name_vi: d.name_vi })).sort((a,b) => a.name.localeCompare(b.name));
                    
                    // Merge boundaries from DB and store display names
                    res.forEach(d => {
                        // Store the translation mapping
                        if (d.name_vi) {
                            districtDisplayNames[d.name_vi] = d.name;
                        }
                        
                        if (d.boundary && d.name_vi) {
                            boundaries[d.name_vi] = d.boundary;
                        }
                    });

                    // Refresh the map overlay with localized names and updated boundaries
                    renderBoundaryLayers();

                    allDistrictsLoaded = true;
                },
                error: function(err) {
                    console.error("{{ __('app.error_loading_districts') }}:", err);
                }
            });
        }
        // PRICE RANGE
        $('#priceRange').on("input", function() {
            $('#priceValue').text(parseInt($(this).val()).toLocaleString('vi-VN'));
        });

        let priceTimeout = null;
        $('#priceRange').on("change", function() {
            clearTimeout(priceTimeout);
            priceTimeout = setTimeout(applyFiltersWithBounds, 500);
        });

        $('#priceRangeSp').on("input", function() {
            $('#priceValueSp').text(parseInt($(this).val()).toLocaleString('vi-VN'));
        });
        let priceSpTimeout = null;
        $('#priceRangeSp').on("change", function() {
            clearTimeout(priceSpTimeout);
            priceSpTimeout = setTimeout(applyFiltersWithBounds, 500);
        });

        $('#typeFilter, #districtFilter, #industryFilter').on("change", applyFiltersWithBounds);
        $('#project_id, #districtFilterSp, #product_type').on("change", applyFiltersWithBounds);
        $('#applyBtn,#applyBtnSp').on("click", applyFiltersWithBounds);

        // --- DROPDOWN QUẬN ---
        function renderDistrictDropdown(filtered = []) {
            const activeTab = $('#projectTabContent').css('display') === 'none' ? 'industrial' : 'project';
            const dropdown = activeTab === 'industrial' ? $('#districtDropdownSp') : $('#districtDropdown');
            dropdown.empty();

            if (!allDistrictsLoaded) {
                dropdown.append('<div class="px-3 py-2 text-gray-400 italic">{{ __('app.loading') }}</div>');
                return;
            }

            if (filtered.length === 0) {
                dropdown.append('<div class="px-3 py-2 text-gray-500">{{ __('app.no_matching_results') }}</div>');
                return;
            }

            filtered.forEach(d => {
                dropdown.append(`<div class="px-3 py-2 hover-options" data-value="${d.name_vi}" data-display="${d.name}">${d.name}</div>`);
            });
            dropdown.show();
        }

        $('#districtFilter').on('input', function() {
            const keyword = removeDiacritics($(this).val());
            const filtered = allDistricts.filter(d => removeDiacritics(d.name).includes(keyword));
            $('.custom_tabs').addClass('position-custom');
            renderDistrictDropdown(filtered);
        });

        $(document).on('click', '#districtDropdown div.hover-options', function() {
            const val = $(this).data('value'); // name_vi
            const display = $(this).data('display'); // translated name
            $('#districtFilter').val(display); // Show translated name in input
            $('#districtFilter').attr('data-real-value', val); // However mapping will still pass original value safely
            $('#districtDropdown').hide();
            $('.custom_tabs').removeClass('position-custom');
            
            // We pass the display name to applyFiltersWithBounds, or let it read from input
            // But MapController filter logic currently assumes localized name since it uses getDistricts API.
            // Wait, does MapController filtering use 'district' => translated name or VI name?
            applyFiltersWithBounds();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.pj-search__col').length) {
                $('#districtDropdown').hide();
                $('.custom_tabs').removeClass('position-custom');
            }
        });

        $('#districtFilterSp').on('input', function() {
            const keyword = removeDiacritics($(this).val());
            const filtered = allDistricts.filter(d => removeDiacritics(d.name).includes(keyword));
            $('.custom_tabs').addClass('position-custom');
            renderDistrictDropdown(filtered);
        });

        $(document).on('click', '#districtDropdownSp div.hover-options', function() {
            const val = $(this).data('value');
            const display = $(this).data('display');
            $('#districtFilterSp').val(display);
            $('#districtFilterSp').attr('data-real-value', val);
            $('#districtDropdownSp').hide();
            $('.custom_tabs').removeClass('position-custom');
            applyFiltersWithBounds();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.pj-search__col').length) {
                $('#districtDropdownSp').hide();
                $('.custom_tabs').removeClass('position-custom');
            }
        });


        // MAP MOVE
        let mapMoveTimeout = null;
        map.on('moveend zoomend', function() {
            isMapTriggered = true;
            clearTimeout(mapMoveTimeout);
            // Tăng thời gian chờ (debounce) lên 800ms để giảm tải số lần render/fetch marker khi vuốt thả bản đồ
            mapMoveTimeout = setTimeout(applyFiltersWithBounds, 800);
        });

        map.whenReady(function() {
            loadAllDistricts(); // tải districts ngay khi map load
            applyFiltersWithBounds(); // tải marker ngay từ đầu

            $('#openDropdown').on('click', function() {
                const dropdown = $('#districtDropdown');
                const customTabs = $('.custom_tabs');

                if (dropdown.is(':visible')) {
                    dropdown.hide();
                    customTabs.removeClass('position-custom');
                } else {
                    renderDistrictDropdown(allDistricts);
                    customTabs.addClass('position-custom');
                }
            });
            $('#openDropdownSp').on('click', function() {
                const dropdown = $('#districtDropdownSp');
                const customTabs = $('.custom_tabs');

                if (dropdown.is(':visible')) {
                    dropdown.hide();
                    customTabs.removeClass('position-custom');
                } else {
                    renderDistrictDropdown(allDistricts);
                    customTabs.addClass('position-custom');
                }
            });
        });
        // Reset các tab
        function resetProjectTab() {
            $('#searchInput').val('');
            $('#districtFilter').val('');
            $('#typeFilter').val('all');
            $('#industryFilter').val('all');
            $('#priceRange').val(0);
            $('#districtDropdown').hide();
        }

        function resetIndustrialTab() {
            $('#searchInputSp').val('');
            $('#districtFilterSp').val('');
            $('#project_id').val('all');
            $('#product_type').val('all');
            $('#priceRangeSp').val(0);
            $('#districtDropdownSp').hide();
        }

        function showTab(tab) {
            // Nút
            $('#projectTab').removeClass('active');
            $('#industrialTab').removeClass('active');

            // Nội dung
            $('#projectTabContent').hide();
            $('#industrialTabContent').hide();

            if (tab === 'project') {
                resetMap();
                $('#projectTab').addClass('active');
                $('#projectTabContent').show();
            } else {
                resetMap();
                $('#industrialTab').addClass('active');
                $('#industrialTabContent').show();
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            const $miniBox = $("#pjSearchMini"); // cụm bé
            const $fullBox = $("#pjSearchFull"); // cụm to
            const $inputMini = $miniBox.find("input[type=text]"); // input trong cụm bé

            let ignoreNextDocClick = false;

            // Trạng thái ban đầu
            $fullBox.hide().addClass("fade-slide");
            $miniBox.show();
            $miniBox.addClass("opacity-minibox");
            if ($(window).width() <= 768) {
                $('#projectTabContentMini').css('maxWidth', '100%');
            } else {
                $('#projectTabContentMini').css('maxWidth', '70%');
            }

            // Khi focus vào input bé → hiện cụm to
            $inputMini.on("focus", function() {
                $miniBox.hide();
                $fullBox.show();

                requestAnimationFrame(() => {
                    $fullBox.addClass("show");
                });

                ignoreNextDocClick = true;
            });

            // Click ngoài → đóng cụm to, hiện lại cụm bé
            $(document).on("click", function(e) {
                if (ignoreNextDocClick) {
                    ignoreNextDocClick = false;
                    return;
                }

                if (
                    !$fullBox.is(e.target) &&
                    $fullBox.has(e.target).length === 0 &&
                    !$miniBox.is(e.target) &&
                    $miniBox.has(e.target).length === 0
                ) {
                    closeFullForm();
                }
            });

            function closeFullForm() {
                $fullBox.removeClass("show").hide();
                $miniBox.show();
            }
        });
        $(document).ready(function() {
            const popup = $('#homePopup');
            const closeBtn = $('#closePopup');
            const popupBody = $('#popupBody');
            const popupLink = $('#popupLink');

            // Danh sách popup (ảnh + link)
            const popups = [
                @foreach ($popups as $popup)
                    {
                        image: "{{ asset($popup->image) }}",
                        link: "{{ $popup->link }}"
                    },
                @endforeach
            ];

            // Hiện popup nếu chưa tắt
            if (!localStorage.getItem('home_popup_closed') && popups.length > 0) {
                let current = 0;

                function showPopup(index) {
                    const item = popups[index];
                    popupLink.css('background-image', 'url(' + item.image + ')');
                    popupLink.attr('href', item.link);
                    popup.css('display', 'flex').hide().fadeIn(300);
                }

                showPopup(current);

                // Nếu có nhiều popup thì tự chuyển
                if (popups.length > 1) {
                    setInterval(() => {
                        current = (current + 1) % popups.length;
                        popupBody.fadeOut(200, function() {
                            const item = popups[current];
                            popupLink.css('background-image', 'url(' + item.image + ')');
                            popupLink.attr('href', item.link);
                            popupBody.fadeIn(300);
                        });
                    }, 4000);
                }

                // Đóng popup
                closeBtn.on('click', function(e) {
                    e.preventDefault();
                    popup.fadeOut(200);
                    sessionStorage.setItem('home_popup_closed', 'true');
                });
            }
            
            // Handle AJAX click for project categories
            $(document).on('click', '.project-nav-fixed a, .project-nav-scroll a', function (e) {
                var href = $(this).attr('href');
                if (href && href.indexOf('#investment-section') !== -1) {
                    e.preventDefault();
                    
                    var $wrapper = $('#project-slider-wrapper');
                    
                    // Add loading state
                    $wrapper.css('opacity', '0.5');

                    // Update active classes
                    $('.project-nav-fixed a, .project-nav-scroll a').removeClass('active');
                    $(this).addClass('active');

                    $.ajax({
                        url: href,
                        type: 'GET',
                        data: { ajax_project_slider: 1 },
                        success: function (response) {
                            $wrapper.html(response);
                            $wrapper.css('opacity', '1');

                            // Re-initialize Swiper specifically for the project section
                            var $sliderContainer = $('#project-slider-wrapper .news-slider');
                            if ($sliderContainer.length > 0 && typeof Swiper !== 'undefined') {
                                $sliderContainer.addClass('has-nav');
                                new Swiper($sliderContainer.find('.news-slider__container')[0], {
                                    loop: false,
                                    navigation: {
                                        prevEl: $sliderContainer.find('.news-slider__prev')[0],
                                        nextEl: $sliderContainer.find('.news-slider__next')[0]
                                    },
                                    spaceBetween: 0,
                                    speed: 500,
                                    slidesPerView: 2,
                                    breakpoints: {
                                        992: { slidesPerView: 3 }
                                    }
                                });
                            }

                            // Re-initialize Tippy tooltips
                            if (typeof tippy === 'function') {
                                tippy('#project-slider-wrapper [data-tippy-content]');
                            }
                        },
                        error: function () {
                            $wrapper.css('opacity', '1');
                            console.error('Failed to load projects');
                        }
                    });
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            const $input = $('#searchInputSp');
            const $popup = $('#suggestionPopupSp');
            const $list = $('#suggestionListSp');
            const $loader = $('#suggestionLoaderSp');
            let delayTimer;

            // Hàm thực hiện gọi AJAX
            function fetchSuggestions(keyword = '') {
                $loader.removeClass('d-none');

                $.ajax({
                    url: "{{ route('ajax_project_suggestions') }}",
                    method: 'GET',
                    data: {
                        keyword: keyword
                    },
                    success: function(data) {
                        $loader.addClass('d-none');
                        let html = '';

                        if (data && data.length > 0) {
                            data.forEach(function(item) {
                                html += `
                            <li>
                                <a href="javascript:void(0)" class="suggestion-item" data-name="${item.name}">
                                    <div class="info">
                                        <div class="name text-truncate fw-bold">${item.name}</div>
                                        <div class="sub text-truncate small text-muted">${item.district_name || 'Đang cập nhật vị trí'}</div>
                                    </div>
                                </a>
                            </li>`;
                            });
                            $list.html(html);
                            $popup.addClass('active');
                        } else {
                            $list.html(
                                '<div class="p-3 text-muted small text-center">Không tìm thấy dự án phù hợp</div>'
                                );
                            $popup.addClass('active');
                        }
                    },
                    error: function() {
                        $loader.addClass('d-none');
                    }
                });
            }

            // Sự kiện khi gõ phím (Debounce)
            $input.on('keyup', function() {
                let keyword = $(this).val();
                clearTimeout(delayTimer);

                delayTimer = setTimeout(function() {
                    fetchSuggestions(keyword);
                }, 300);
            });

            $input.on('focus click', function() {
                let keyword = $(this).val();
                if (!$popup.hasClass('active')) {
                    fetchSuggestions(keyword);
                }
            });

            $(document).on('click', '.suggestion-item', function(e) {
                e.preventDefault();
                const projectName = $(this).data('name');
                $input.val(projectName);
                $popup.removeClass('active');
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.pj-search__col').length) {
                    $popup.removeClass('active');
                }
            });
        });
    </script>
@endpush
