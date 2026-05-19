@extends('frontend.index')

@section('content')
    <div class="page__content">

        @guest('guest') {{-- nếu chưa login bằng guard guest --}}
            <div class="alert alert-warning text-center my-4">
                {!! __('app.login_required') !!}
            </div>
        @endguest

        @auth('guest') {{-- nếu đã login --}}
            <!-- main content-->
            <article class="banner d-block px-0">
                <img class="banner__bg" src="{{ asset('images/banner-project.jpg') }}" alt="">
            </article>
            <section class="acc-info">
                <div class="container">
                    <div class="acc-info__header">
                        <div class="acc-info__left">
                            <div class="acc-info__avatar">
                                <img id="avatarPreview"
                                    src="{{ optional($user)->avatar ? asset('storage/' . $user->avatar) : asset('images/avatar-placeholder.png') }}"
                                    alt="avatar" />
                            </div>
                            <label class="upload-img acc-info__upload"><i class="fal fa-camera"></i>
                                <input id="avatarInput" type="file" accept="image/png, image/jpeg" />
                            </label>
                        </div>
                        <div class="acc-info__right">
                            <div class="acc-info__name">{{$user->name ?? ''}}</div>
                            <div class="acc-info__phone">
                                <i class="fal fa-phone me-2"></i>
                                <a href="tel:{{$user->phone ?? ''}}">{{$user->phone ?? ''}}</a>
                            </div>
                        </div>
                    </div>

                    {{-- Form chỉnh sửa thông tin --}}
                    <form class="acc-info__form" action="{{route('guest_update_account')}}" method="post">
                        @csrf
                        <!-- input hidden để submit avatar -->
                        <input type="hidden" name="avatar" id="avatarHidden">

                        {{-- Hiển thị lỗi validate --}}
                        @if($errors->any())
                            <div class="alert alert-danger mb-3 position-relative">
                                <ul class="mb-0" style="list-style: none; padding-left: 0;">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if(session('success'))
                            <div class="alert alert-success mb-3 position-relative">
                                {{ session('success') }}
                                <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row gx-4 gy-30">
                            <div class="col-md-6 col-xl-4">
                                <label class="form-label">{{ __('app.full_name') }}</label>
                                <input class="form-control" type="text" name="name"
                                    value="{{ old('name', $user->name ?? '') }}">
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <label class="form-label">{{ __('app.email_address') }}</label>
                                <input class="form-control" type="email" name="email"
                                    value="{{ old('email', $user->email ?? '') }}">
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <label class="form-label">{{ __('app.phone_number') }}</label>
                                <input class="form-control" type="text" name="phone"
                                    value="{{ old('phone', $user->phone ?? '') }}">
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <label class="form-label">{{ __('app.address') }}</label>
                                <input class="form-control" type="text" name="address"
                                    value="{{ old('address', $user->address ?? '') }}">
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <label class="form-label">{{ __('app.password') }}</label>
                                <input class="form-control" type="password" name="password">
                            </div>
                        </div>
                        <div class="text-center mt-30">
                            <button class="button" type="submit">{{ __('app.edit') }}</button>
                        </div>
                    </form>
                </div>
            </section>
            <section class="section" id="project-interest">
                <div class="container">
                    <h2 class="section__title mb-3 text-uppercase">{{ __('app.interested_projects') }}</h2>
                    <div class="project-nav-wrapper mb-60">
                        <!-- "Tất cả" cố định -->
                        <div class="project-nav-fixed">
                          <a class="{{ request('industry') ? '' : 'active' }}"
                             href="{{ route('account', ['keyword' => request('keyword')]) }}#project-interest">
                            {{ __('app.all') }}
                          </a>
                        </div>
                    
                        <!-- Các ngành scroll ngang -->
                        <div class="project-nav-scroll swiper-container">
                            <ul class="project-nav__list_custom swiper-wrapper">
                              @foreach($industries as $industry)
                                <li class="swiper-slide" data-tippy-content="{{ $industry['name'] }}">
                                  <a class="{{ request('industry') == $industry['id'] ? 'active' : '' }}"
                                     href="{{ route('account', ['industry' => $industry['id']]) }}#project-interest">
                                    {{ $industry['name'] }}
                                  </a>
                                </li>
                              @endforeach
                            </ul>
                          </div>
                    </div>
                    

                    <form class="pj-search mb-60" action="{{ route('account') }}#project-interest" method="GET" style="padding: 0; background: 0;">
                        <div class="input-group">
                            <input name="keyword" value="{{ request('keyword') }}" class="form-control" type="text" placeholder="{{ __('app.search') }}" />
                            <div class="input-group-text">
                                <button type="submit" class="btn p-0"><i class="fal fa-lg fa-search"></i></button>
                            </div>
                        </div>
                        @if(request('industry'))
                            <input type="hidden" name="industry" value="{{ request('industry') }}">
                        @endif
                    </form>                    
                    @if(count($list_project_interest) === 0)
                    <p class="text-center">{{ __('app.no_interested_projects') }}</p>
                @else                 
                    <div class="news-slider">
                        <div class="news-slider__nav">
                            <div class="news-slider__prev"><i class="fal fa-fw fa-lg fa-angle-left"></i></div>
                            <div class="news-slider__next"><i class="fal fa-fw fa-lg fa-angle-right"></i></div>
                        </div>
                        <div class="news-slider__container swiper-container">
                            <div class="swiper-wrapper">
                                @foreach($list_project_interest as $item)
                                    <div class="swiper-slide project-slide" data-name="{{ strtolower($item['name']) }}">
                                        <div>
                                            <div class="project"><a class="project__frame" href="{{ route('project_detail',['slug' => $item['slug'], 'ref' => 'Dự án kêu gọi đầu tư']) }}"><img src="{{$item['detail_image'] ?? './images/project-1.jpg' }}"
                                                        alt="" /></a>
                                                <div class="project__body">
                                                    <h3 class="project__title"><a href="{{ route('project_detail',['slug' => $item['slug'], 'ref' => 'Dự án kêu gọi đầu tư']) }}" data-tippy-content="{{$item['name']}}">{{$item['name']}}</a></h3>
                                                    @if($item['is_invest'] == 0)
                                                    <div class="project__overlay"><span>{{ __('app.projects_calling_for_investment') }}</span>
                                                        <a class="project__like" href="javascript:void(0)" data-id="{{ $item['id'] }}" data-type="App\Models\Project"><i class="fas fa-fw fa-lg fa-heart {{ $item['is_interested'] ? 'text-danger' : '' }}"></i></a>
                                                    </div>
                                                @else
                                                    <div class="project__overlay"><span>{{ __('app.projects_with_investors') }}</span>
                                                        <a class="project__like" href="javascript:void(0)" data-id="{{ $item['id'] }}" data-type="App\Models\Project"><i class="fas fa-fw fa-lg fa-heart {{ $item['is_interested'] ? 'text-danger' : '' }}"></i></a>
                                                    </div>
                                                @endif
                                                    <ul class="project__info">
                                                        <li><img class="me-2" src="{{ asset('/images/icon-map-marker.svg') }}"
                                                                alt="" /><span data-tippy-content="{{ __('app.project_under') }} {{ $item->districts->pluck('name')->join(', ') }}, {{ __('app.hanoi_city') }}">{{ __('app.project_under') }} {{ $item->districts->pluck('name')->join(', ') }}, {{ __('app.hanoi_city') }}</span></li>
                                                        <li>
                                                            <img class="me-2" src="{{ asset('/images/icon-dimension.svg') }}" alt="" />
                                                            @php
                                                                $formattedArea = formatDecimalByLocale($item->area ?? 0);
                                                            @endphp
                                                            <span>{{ $formattedArea }} {{ $item->unit_type_text ?? '' }}</span>
                                                        </li>
                                                        <li><img class="me-2" src="{{ asset('/images/icon-save-money.svg') }}" alt="" /><span>{{ number_format($item->price, 0, ',', '.') }} {{ __('app.billion_vnd') }}</span></li>
                                                    </ul>
                                                </div>
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
                    <nav class="d-flex justify-content-center mt-40 mt-lg-60"><a class="button" href="{{ url($locale . '/' . __('app.projects_link')) }}" style="text-transform: capitalize;">{{ __('app.view_more') }}</a></nav>
                    @endif
                </div>
            </section>
            <section class="section section--bg-pattern">
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
                                                {{ $item['title'][$locale] ?? $item['title']['vi'] ?? '0' }}
                                            </div>
                                            <div class="counter__title">
                                                {{ $item['content'][$locale] ?? $item['content']['vi'] ?? '' }}
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
            <section class="section" id="post-interest">
                <div class="container">
                    <h2 class="section__title">{{ __('app.interested_news') }}</h2>
                
                    @if(count($list_post_interest) === 0)
                        <p class="text-center">{{ __('app.no_interested_news') }}</p>
                    @else
                        <div class="news-slider">
                            <div class="news-slider__nav">
                                <div class="news-slider__prev"><i class="fal fa-fw fa-lg fa-angle-left"></i></div>
                                <div class="news-slider__next"><i class="fal fa-fw fa-lg fa-angle-right"></i></div>
                            </div>
                            <div class="news-slider__container swiper-container">
                                <div class="swiper-wrapper">
                                    @foreach($list_post_interest as $item)
                                        <div class="swiper-slide">
                                            <div class="news">
                                                <a class="news__frame" href="{{ route('post_detail',['id' => $item['id'], 'slug' => $item['slug'], 'ref' => 'app.news']) }}">
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
                                                            <i class="fas fa-fw fa-heart {{ $item['is_interested'] ? 'text-danger' : '' }}"></i>
                                                        </a>
                                                    </div>
                                                    <h3 class="news__title custom-desc">
                                                        <a href="{{ route('post_detail',['id' => $item['id'], 'slug' => $item['slug'], 'ref' => 'app.news']) }}"
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
                        <nav class="d-flex justify-content-center mt-40 mt-lg-60">
                            <a class="button" href="{{ url($locale . '/' . __('app.news_link')) }}" style="text-transform: capitalize;">{{ __('app.view_more') }}</a>
                        </nav>
                    @endif
                </div>
                
            </section>
        @endauth
    </div>
@endsection

@push('bottom')

@endpush
