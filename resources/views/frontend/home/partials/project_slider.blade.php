@if (empty($project_category))
    <p class="text-center">
        {{ __('app.no_suitable_project') }}
    </p>
@else
    <!-- phần slider giữ nguyên -->
    <div class="news-slider">
        <div class="news-slider__nav">
            <div class="news-slider__prev"><i class="fal fa-fw fa-lg fa-angle-left"></i></div>
            <div class="news-slider__next"><i class="fal fa-fw fa-lg fa-angle-right"></i></div>
        </div>
        <div class="news-slider__container swiper-container">
            <div class="swiper-wrapper">
                @foreach ($project_category as $item)
                    <div class="swiper-slide">
                        <div>
                            <div class="project">
                                <a class="project__frame"
                                    href="{{ route('project_detail', ['slug' => $item['slug']]) }}">
                                    <img src="{{ $item['detail_image'] ?? './images/project-1.jpg' }}"
                                        alt="" />
                                </a>
                                <div class="project__body">
                                    <h3 class="project__title">
                                        <a href="{{ route('project_detail', ['slug' => $item['slug']]) }}"
                                            data-tippy-content="{{ $item['name'] }}">
                                            {{ $item['name'] }}
                                        </a>
                                    </h3>
                                    @if ($item['is_invest'] == 0)
                                        <div class="project__overlay">
                                            <span>{{ __('app.projects_calling_for_investment') }}</span>
                                            <a class="project__like" href="javascript:void(0)"
                                                data-id="{{ $item['id'] }}"
                                                data-type="App\Models\Project"><i
                                                    class="fas fa-fw fa-lg fa-heart {{ $item['is_interested'] ? 'text-danger' : '' }}"></i></a>
                                        </div>
                                    @else
                                        <div class="project__overlay">
                                            <span>{{ __('app.projects_with_investors') }}</span>
                                            <a class="project__like" href="javascript:void(0)"
                                                data-id="{{ $item['id'] }}"
                                                data-type="App\Models\Project"><i
                                                    class="fas fa-fw fa-lg fa-heart {{ $item['is_interested'] ? 'text-danger' : '' }}"></i></a>
                                        </div>
                                    @endif
                                    <ul class="project__info">
                                        <li>
                                            <img class="me-2" src="{{ asset('/images/icon-map-marker.svg') }}"
                                                alt="" />
                                            <span
                                                data-tippy-content="{{ __('app.project_under') }} {{ $item['districts'] }}">
                                                {{ __('app.project_under') }} {{ $item['districts'] }}
                                            </span>
                                        </li>
                                        <li>
                                            <img class="me-2" src="{{ asset('/images/icon-dimension.svg') }}" alt="" />
                                            @php
                                                $formattedArea = formatDecimalByLocale($item['area'] ?? 0);
                                            @endphp
                                            <span>{{ $formattedArea }} {{ $item['unit'] ?? '' }}</span>
                                        </li>                                                                 
                                        </li>
                                        @php
                                            $locale = app()->getLocale();
                                        @endphp

                                        <li>
                                            <img class="me-2" src="{{ asset('/images/icon-save-money.svg') }}" alt="" />
                                            <span>
                                                {{ $locale == 'vn'
                                                    ? number_format($item['price'], 0, ',', '.')
                                                    : number_format($item['price'], 0, '.', ',') }}
                                                {{ __('app.billion_vnd') }}
                                            </span>
                                        </li>
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
    <nav class="d-flex justify-content-center mt-40 mt-lg-60">
        <a class="button" href="{{ url($locale . '/' . __('app.projects_link') ) }}" style="text-transform: capitalize;">{{ __('app.view_more') }}</a>
    </nav>
@endif
