@extends('frontend.index')

@section('content')
<div class="page__content">
    <!-- main content-->
    @if ($project->layout_id == 1)
    <section class="pj-banner">
        <nav>
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a class="link-unstyled" href="{{ asset('/') }}"><i
                                class="fal fa-home me-2"></i><span>{{ __('app.home') }}</span></a></li>
                    @if (!empty($backUrl) && $backUrl !== url()->current())
                    <li class="breadcrumb-item">
                        <a href="{{ $backUrl }}">{{$backLabel}}</a>
                    </li>
                    @endif
                    <li class="breadcrumb-item active">{{$project->name}}</li>
                </ol>
            </div>
        </nav>
        <img class="pj-banner__bg" src="{{ $project->banner_image ?? 'default-banner.jpg' }}" alt="">
        <div class="pj-banner__wrapper">
            <div class="container" style="text-align: center;">
                <div style="background: rgba(255, 255, 255, 0.349);border-radius:8px;padding:10px 20px;display: inline-block;">
                    <div class="pj-banner__subtitle">{{ __('app.projects') }}</div>
                    <div class="pj-banner__title">{{ $project->name }}</div>
                </div>
                <div class="pj-banner__separator"></div>
                <div class="pj-banner__desc" style="background: white;border-radius:8px;padding:10px 20px;opacity: 0.9; text-align: justify;">{{ $project->short_desc ?? '' }}</div>
            </div>
        </div>
    </section>
    @elseif($project->layout_id == 2)
    <section class="pj-banner">
        <nav>
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a class="link-unstyled" href="{{ asset('/') }}"><i
                                class="fal fa-home me-2"></i><span>{{ __('app.home') }}</span></a></li>
                    <li class="breadcrumb-item active">{{ __('app.investment_project_list') }}</li>
                </ol>
            </div>
        </nav>
        <img class="pj-banner__bg" src="{{ $project->banner_image ?? 'default-banner.jpg' }}" alt="">
        <div class="pj-banner__wrapper custom-wrapper">
            <div class="container" style="text-align: right;">
                <div style="background: rgba(255, 255, 255, 0.349);border-radius:8px;padding:10px 20px;display: inline-block;text-align: right;">
                    <div class="pj-banner__subtitle text-end">{{ __('app.projects') }}</div>
                    <div class="pj-banner__title text-end">{{ $project->name }}</div>
                </div>
                <div class="custom_desc" style="text-align:start">{{ $project->short_desc ?? '' }}</div>
            </div>
        </div>
    </section>
    @elseif($project->layout_id == 3)
    <section class="pj-banner">
        <nav>
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a class="link-unstyled" href="{{ asset('/') }}"><i
                                class="fal fa-home me-2"></i><span>{{ __('app.home') }}</span></a></li>
                    <li class="breadcrumb-item active">{{ __('app.investment_project_list') }}</li>
                </ol>
            </div>
        </nav>
        <img class="pj-banner__bg" src="{{ $project->banner_image ?? 'default-banner.jpg' }}" alt="">
        <div class="pj-banner__wrapper custom-wrapper">
            <div class="container">
                <div style="background: rgba(255, 255, 255, 0.349);border-radius:8px;padding:10px 20px;display: inline-block;text-align: left;">
                    <div class="pj-banner__subtitle text-start">{{ __('app.projects') }}</div>
                    <div class="pj-banner__title text-start">{{ $project->name }}</div>
                </div>
                <div class="d-flex justify-content-end">
                    <div class="custom_desc">{{ $project->short_desc ?? '' }}</div>
                </div>
            </div>
        </div>
    </section>
    @endif

    @php
    // Kiểm tra các section có content hay không (strip tags và trim để loại bỏ HTML rỗng)
    $hasGeneralInfo = !empty(trim(strip_tags($project->description ?? '')));
    $hasLocation = !empty($project->location_image);
    $hasAdvantages = count(array_filter(explode(';', $project->advantage_images ?? ''))) > 0;
    $hasVirtualMap = !empty($project->link_sand_table) || !empty($project->link_vrtour);
    $hasDesign = count(array_filter(explode(';', $project->design_images ?? ''))) > 0;
    $hasLegal = count(array_filter(explode(';', $project->legal_file ?? ''))) > 0;
    $hasInvestmentGuide = isset($preferential) && count($preferential) > 0;
    $hasPlan = isset($project->plan) && (
    !empty(trim(strip_tags($project->plan->content1 ?? ''))) ||
    !empty(trim(strip_tags($project->plan->content2 ?? ''))) ||
    !empty(trim(strip_tags($project->plan->content3 ?? '')))
    );
    $hasNews = isset($posts) && count($posts) > 0;
    @endphp

    <nav class="project-nav">
        <div class="container">
            <ul class="project-nav__list">
                @if($hasGeneralInfo)
                <li><a class="active" href="#thong-tin-chung">{{ __('app.general_info') }}</a></li>
                @endif

                @if($hasLocation)
                <li><a href="#vi-tri">{{ __('app.location') }}</a></li>
                @endif

                @if($hasAdvantages)
                <li><a href="#loi-the-noi-bat">{{ __('app.key_advantages') }}</a></li>
                @endif

                @if($hasVirtualMap)
                <li><a href="#sa-ban-ao">{{ __('app.virtual_map') }}</a></li>
                @endif

                @if($hasDesign)
                <li><a href="#thiet-ke-va-mat-bang">{{ __('app.design_and_layout') }}</a></li>
                @endif

                @if($hasLegal)
                <li><a href="#phap-ly">{{ __('app.legal_documents') }}</a></li>
                @endif

                @if($hasInvestmentGuide)
                <li><a href="#thu-tuc-dau-tu">{{ __('app.investment_procedure') }}</a></li>
                @endif

                @if($hasPlan)
                <li><a href="#ke-hoach-trien-khai">{{ __('app.implementation_plan') }}</a></li>
                @endif

                @if($hasNews)
                <li><a href="#tin-tuc">{{ __('app.news') }}</a></li>
                @endif
            </ul>
        </div>
    </nav>

    @if($hasGeneralInfo)
    <section class="section" id="thong-tin-chung">
        <img class="section__bg" src="{{ asset('./images/achitect-bg.png') }}" alt="">
        <div class="container">
            <h2 class="section__title">{{ __('app.general_info') }}</h2>
            <div class="mx-auto" style="max-width: 800px;">
                {!! $project->description !!}
            </div>
        </div>
    </section>
    @endif

    @if($hasLocation)
    <section class="section pb-0" id="vi-tri">
        <div class="container">
            <h2 class="section__title">{{ __('app.location') }}</h2>
            <img class="w-100" src="{{ $project->location_image ?? asset('./images/position.jpg') }}" alt="">
        </div>
    </section>
    @endif

    @if($hasAdvantages)
    <section class="section section--light-blue" id="loi-the-noi-bat">
        <div class="container">
            <h2 class="section__title">{{ __('app.key_advantages') }}</h2>
            <div>
                @php
                $images = $project->advantage_images ? array_filter(explode(';', $project->advantage_images)) : [];
                $titlesRaw = is_array($project->advantage_titles)
                ? $project->advantage_titles
                : json_decode($project->advantage_titles, true);
                $titles = $titlesRaw['vi'] ?? (is_array($titlesRaw) ? $titlesRaw : []);
                $descsRaw = is_array($project->advantage_descriptions)
                ? $project->advantage_descriptions
                : json_decode($project->advantage_descriptions, true);
                $descs = $descsRaw['vi'] ?? (is_array($descsRaw) ? $descsRaw : []);
                $count = count($images);
                @endphp

                @for ($i = 0; $i < $count; $i++)
                    <div class="advantage mt-20">
                    <a class="advantage__frame" href="#!">
                        <img src="{{ $images[$i] ?? '' }}" alt="" />
                    </a>
                    <div class="advantage__body">
                        <div class="advantage__index">{{ $i + 1 }}</div>
                        <div class="advantage__index-bg">{{ $i + 1 }}</div>
                        <div class="advantage__title">{{ $titles[$i] ?? '' }}</div>
                        <div class="advantage__desc text-justify">
                            {!! $descs[$i] ?? '' !!}
                        </div>
                    </div>
            </div>
            @endfor
        </div>
</div>
</section>
@endif

@if($hasVirtualMap)
<section class="position-relative" id="sa-ban-ao">
    <div class="section section--overlay">
        <div class="container">
            <h2 class="section__title text-white">{{ __('app.virtual_map') }}</h2>
            @if($project->link_vrtour)
            <div class="mt-3">
                <a href="{{ $project->link_vrtour }}" class="btn btn-warning text-white custom-btn-vrtour"
                    target="_blank" rel="noopener noreferrer">
                    {{ __('app.view_vr_tour') }}
                </a>
            </div>
            @endif
        </div>
    </div>
    @if($project->link_sand_table)
    <div class="ratio ratio-2x1">
        <iframe src="{{ $project->link_sand_table }}" frameborder="0" allowfullscreen allow="fullscreen"></iframe>
    </div>
    @endif
</section>
@endif

@if($hasDesign)
<section class="section" id="thiet-ke-va-mat-bang">
    <div class="container">
        <h2 class="section__title">{{ __('app.design_and_layout') }}</h2>
        @if(!empty($project->design_short_desc))
        <div class="section__desc">{{ $project->design_short_desc }}</div>
        @endif
        <div class="design-slider">
            <div class="design-slider__container swiper-container">
                <div class="swiper-wrapper">
                    @php
                    $design_images = $project->design_images ? array_filter(explode(';', $project->design_images)) : [];
                    $design_descs_raw = is_array($project->design_description)
                    ? $project->design_description
                    : json_decode($project->design_description, true);
                    $design_descs = $design_descs_raw ?? [];
                    if (!is_array($design_descs)) {
                    $design_descs = [$design_descs];
                    }
                    $design_count = count($design_images);
                    @endphp

                    @if($design_count > 0)
                    @foreach ($design_images as $index => $image)
                    <div class="design-slider__slide swiper-slide">
                        <div class="design-slider__frame">
                            <img src="{{ $image }}" alt="Design image {{ $index + 1 }}" />
                        </div>
                        @if(!empty($design_descs[$index]))
                        <div class="design-slider__overlay">
                            <div class="design-slider__content">
                                {{ $design_descs[$index] }}
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                    @else
                    <p>Không có hình ảnh thiết kế.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="design-thumb-slider mt-3 mt-lg-20">
            <div class="design-thumb-slider__prev"><i class="fal fa-lg fa-angle-left"></i></div>
            <div class="design-thumb-slider__next"><i class="fal fa-lg fa-angle-right"></i></div>
            <div class="design-thumb-slider__container swiper-container">
                <div class="swiper-wrapper">
                    @for ($i = 0; $i < $design_count; $i++)
                        <div class="swiper-slide">
                        <div class="design-thumb-slider__frame"><img src="{{ $design_images[$i] }}" alt="" /></div>
                </div>
                @endfor
            </div>
        </div>
    </div>
    </div>
</section>
@endif

@if($hasLegal)
<section class="section section--light-blue" id="phap-ly">
    <div class="container">
        <h2 class="section__title">{{ __('app.legal_documents') }}</h2>
        @if(!empty($project->legal_short_desc))
        <div class="section__desc">{{ $project->legal_short_desc }}</div>
        @endif
        <div class="legal-grid">
            @php
            $legal_files = $project->legal_file ? explode(';', $project->legal_file) : [];
            $legal_descs_data = is_array($project->legal_description)
            ? $project->legal_description
            : json_decode($project->legal_description, true);
            $legal_descs = $legal_descs_data ?? [];
            @endphp
            @if(count($legal_files) > 0)
            @foreach ($legal_files as $index => $file)
            <a class="legal" href="{{ asset($file) }}" target="_blank" rel="noopener noreferrer">
                <img class="legal__icon" src="{{ asset('./images/icon-pdf.svg') }}" alt="" />
                <div class="legal__body">
                    <div class="legal__title">
                        @if(isset($legal_descs[$index]) && !empty($legal_descs[$index]))
                        <div class="fw-bold text-truncate-multiline">
                            {{ $legal_descs[$index] }}
                        </div>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
            @endif
        </div>
        <nav class="d-flex justify-content-center mt-40 mt-lg-60">
        </nav>
    </div>
</section>
@endif

@if($hasInvestmentGuide)
<section class="section" id="thu-tuc-dau-tu">
    <div class="container">
        <h2 class="section__title pb-2">{{ __('app.incentives_process_procedures') }}</h2>
        <div class="news-slider">
            <div class="news-slider__nav">
                <div class="news-slider__prev"><i class="fal fa-fw fa-lg fa-angle-left"></i></div>
                <div class="news-slider__next"><i class="fal fa-fw fa-lg fa-angle-right"></i></div>
            </div>
            <div class="news-slider__container swiper-container">
                <div class="swiper-wrapper">
                    @foreach ($preferential as $item)
                    <div class="swiper-slide">
                        <div class="news"><a class="news__frame"
                                href="{{ route('investment_guide_detail', ['id' => $item->id, 'slug' => $item->slug, 'ref' => $project->name]) }}"><img
                                    src="{{ $item->image }}" alt="" /></a>
                            <div class="news__body">
                                <div class="news__info">
                                    <div class="news__time"><i
                                            class="fal fa-clock me-2"></i><span>{{ \Carbon\Carbon::parse($item->published_at)->format('d/m/Y') }}</span>
                                    </div>
                                    <a class="news__like" href="javascript:void(0)"
                                        data-id="{{ $item->id }}" data-type="App\Models\InvestmentGuide">
                                        <i class="fas fa-fw fa-heart {{ $item->is_interested ? 'text-danger' : '' }}"></i>
                                    </a>
                                </div>
                                <h3 class="news__title custom-desc"><a
                                        href="{{ route('investment_guide_detail', ['id' => $item->id, 'slug' => $item->slug, 'ref' => $project->name]) }}"
                                        data-tippy-content="{{$item->name}}">{{ $item->name }}</a>
                                </h3>
                                <div class="news__desc">{{ $item->description }}</div>
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
                <nav class="d-flex justify-content-center mt-40 mt-lg-60"><a class="button" href="{{ url($locale . '/' . __('app.investment_guide_link')) }}" style="text-transform: capitalize;">{{ __('app.view_more') }}</a>
                </nav>
    </div>
</section>
@endif

@if($hasPlan)
<section class="section position-relative" id="ke-hoach-trien-khai">
    <img src="{{asset('/images/texture-8.png')}}" class="texture texture-bottom-left" alt="">
    <img src="{{asset('/images/texture-9.png')}}" class="texture texture-top-right" alt="">
    <div class="container">
        <h2 class="section__title text-center mb-5">{{ __('app.implementation_plan') }}</h2>
        <div class="row g-2 align-items-stretch justify-content-center position-relative">

            <!-- Bước 1 -->
            <div class="col-12 col-lg-3 d-flex">
                <div class="step-card flex-fill d-flex flex-column align-items-center p-4 rounded">
                    <img src="{{asset('images/search-icon.png')}}" alt="">
                    <h5 class="fw-bold text-uppercase mb-3 text-center">
                        {{ __('app.project_preparation') }}
                    </h5>
                    <ul class="list-unstyled flex-fill">
                        @if (app()->getLocale() === 'vi' || app()->getLocale() === 'vn')
                        {!! optional($project->plan)->content1 ?? '' !!}
                        @else
                        {!! optional($project->plan)->content1_en ?? '' !!}
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Arrow 1 -->
            <div class="col-12 col-md-1 d-flex justify-content-center align-items-baseline">
                <div class="arrow-container">
                    <img src="{{ asset('images/arrow.svg') }}" class="arrow-img" alt="">
                </div>
            </div>

            <!-- Bước 2 -->
            <div class="col-12 col-lg-3 d-flex">
                <div class="step-card flex-fill d-flex flex-column align-items-center p-4 rounded">
                    <img src="{{asset('images/partner-icon.png')}}" alt="">
                    <h5 class="fw-bold text-uppercase mb-3 text-center">
                        {{ __('app.investment_promotion') }}
                    </h5>
                    <ul class="list-unstyled flex-fill">
                        @if (app()->getLocale() === 'vi' || app()->getLocale() === 'vn')
                        {!! optional($project->plan)->content2 ?? '' !!}
                        @else
                        {!! optional($project->plan)->content2_en ?? '' !!}
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Arrow 2 -->
            <div class="col-12 col-md-1 d-flex justify-content-center align-items-baseline">
                <div class="arrow-container">
                    <img src="{{ asset('images/arrow.svg') }}" class="arrow-img" alt="">
                </div>
            </div>

            <!-- Bước 3 -->
            <div class="col-12 col-lg-3 d-flex">
                <div class="step-card flex-fill d-flex flex-column align-items-center p-4 rounded">
                    <img src="{{asset('images/analytic-icon.png')}}" alt="">
                    <h5 class="fw-bold text-uppercase mb-3 text-center">
                        {{ __('app.implementation_monitoring') }}
                    </h5>
                    <ul class="list-unstyled flex-fill">
                        @if (app()->getLocale() === 'vi' || app()->getLocale() === 'vn')
                        {!! optional($project->plan)->content3 ?? '' !!}
                        @else
                        {!! optional($project->plan)->content3_en ?? '' !!}
                        @endif
                    </ul>
                </div>
            </div>

        </div>
        <div class="d-flex justify-content-end align-items-start align-items-md-center mt-4 mt-lg-5 gap-2 gap-md-4 note">
            <div>
                <img src="{{asset('images/warning-arrow.svg')}}" style="width:15px;height:15px" alt="">
                <div class="d-inline-block ms-2">{{ __('app.in_progress') }}</div>
            </div>
            <div>
                <img src="{{asset('images/success-traces.svg')}}" style="width:15px;height:15px" alt="">
                <div class="d-inline-block ms-2">{{ __('app.completed') }}</div>
            </div>
        </div>
    </div>
</section>
@endif

@if($hasNews)
<section class="section" id="tin-tuc">
    <div class="container">
        <h2 class="section__title">{{ __('app.news') }}</h2>
        <div class="news-slider">
            <div class="news-slider__nav">
                <div class="news-slider__prev"><i class="fal fa-fw fa-lg fa-angle-left"></i></div>
                <div class="news-slider__next"><i class="fal fa-fw fa-lg fa-angle-right"></i></div>
            </div>
            <div class="news-slider__container swiper-container">
                <div class="swiper-wrapper">
                    @foreach ($posts as $item)
                    <div class="swiper-slide">
                        <div class="news"><a class="news__frame"
                                href="{{ route('post_detail', ['id' => $item->id, 'slug' => $item->slug, 'ref' => $project->name]) }}"><img
                                    src="{{ $item->image }}" alt="" /></a>
                            <div class="news__body">
                                <div class="news__info">
                                    <div class="news__time"><i
                                            class="fal fa-clock me-2"></i><span>{{ \Carbon\Carbon::parse($item->published_at)->format('d/m/Y') }}</span>
                                    </div>
                                    <a class="news__like" href="javascript:void(0)"
                                        data-id="{{ $item->id }}" data-type="App\Models\Post">
                                        <i class="fas fa-fw fa-heart {{ $item->is_interested ? 'text-danger' : '' }}"></i>
                                    </a>
                                </div>
                                <h3 class="news__title custom-desc"><a
                                        href="{{ route('post_detail', ['id' => $item->id, 'slug' => $item->slug, 'ref' => $project->name]) }}"
                                        data-tippy-content="{{$item->name}}">{{ $item->name }}</a>
                                </h3>
                                <div class="news__desc">{{ $item->description }}</div>
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
                <nav class="d-flex justify-content-center mt-40 mt-lg-60"><a class="button" href="{{ url($locale . '/' . __('app.news_link')) }}" style="text-transform: capitalize;">{{ __('app.view_more') }}</a>
                </nav>
    </div>
</section>
@endif
</div>
@endsection

@push('bottom')
<script>
    $(document).ready(function() {
        const OFFSET = 160;
        const $navLinks = $('.project-nav__list a');

        // Click scroll mượt
        $navLinks.on('click', function(e) {
            e.preventDefault();

            const targetId = $(this).attr('href');
            const $target = $(targetId);

            if ($target.length) {
                $('html, body').animate({
                    scrollTop: $target.offset().top - OFFSET
                }, 500);
            }
        });

        // Scroll spy
        $(window).on('scroll', function() {
            const scrollPos = $(window).scrollTop() + OFFSET + 5;

            let currentId = null;

            $('section[id]').each(function() {
                const top = $(this).offset().top;
                const bottom = top + $(this).outerHeight();

                if (scrollPos >= top && scrollPos < bottom) {
                    currentId = '#' + this.id;
                    return false; // break loop
                }
            });

            if (currentId) {
                $navLinks.removeClass('active');
                $navLinks.filter(`[href="${currentId}"]`).addClass('active');
            }
        });
    });
</script>
@endpush