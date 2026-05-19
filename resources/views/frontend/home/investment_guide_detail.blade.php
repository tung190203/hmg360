@extends('frontend.index')

@section('content')
    <div class="page__content">
        <!-- main content-->
        <div class="pt-20">
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
                        <li class="breadcrumb-item active">{{ $investment_guide->name }}</li>
                    </ol>
                </div>
            </nav>
        </div>
        <article class="post custom-news">
            <div class="container text-justify">
                <h1 class="section__title fw-700 text-justify">{{ $investment_guide->name }}</h1>

                @if (!empty($investment_guide->description))
                    <h2 class="section__subtitle custom-text-bold mb-4" style="font-weight: normal; font-size: 16px;">
                        {{ $investment_guide->description }}
                    </h2>
                @endif

                <div class="post__content">
                    {!! $investment_guide->content !!}
                </div>

                <div class="post__footer">
                    <span class="post__time me-4">
                        <i class="fal fa-clock me-2"></i><span>{{ \Carbon\Carbon::parse($investment_guide->published_at)->format('d/m/Y') }}</span>
                    </span>
                </div>
            </div>
            <div class="mt-4">
                @php
                    $files = $investment_guide->files ? explode(';', $investment_guide->files) : [];
                    $content = $investment_guide->short_file_descs
                        ? json_decode($investment_guide->short_file_descs, true)
                        : [];
                @endphp

                @if (count($files) > 0)
                    <div class="container mb-4">
                        <h3 class="mb-3">{{ __('app.attached_documents') }}</h3>
                        <div class="row g-3">
                            @foreach ($files as $index => $file)
                                <div class="col-md-6">
                                    <div class="card shadow-sm h-100 hover-shadow-custom border-0 file-item" 
                                        data-file="{{ asset($file) }}">
                                        <div class="card-body d-flex align-items-center" style="cursor:pointer;">
                                            <i class="fas fa-file-alt fa-2x text-primary me-3"></i>
                                            @if (isset($content[$index]) && !empty($content[$index]))
                                                <div class="fw-bold text-truncate-multiline">
                                                    {{ $content[$index] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <iframe id="fileViewer" src="{{ asset($files[0]) }}" width="100%" height="700px" frameborder="0"></iframe>
                        </div>
                    </div>
                @endif
            </div>
        </article>
        <section class="">
            {{-- <img class="texture-1" src="./images/texture-1.png" alt="">
            <img class="texture-2" src="./images/texture-2.png" alt=""> --}}
            <div class="container">
                <h2 class="section__title">{{ __('app.related_investment_guides') }}</h2>
                <div class="news-slider">
                    <div class="news-slider__nav">
                        <div class="news-slider__prev"><i class="fal fa-fw fa-lg fa-angle-left"></i></div>
                        <div class="news-slider__next"><i class="fal fa-fw fa-lg fa-angle-right"></i></div>
                    </div>
                    <div class="news-slider__container swiper-container">
                        <div class="swiper-wrapper">
                            @foreach ($list_investment_guide_popular as $item)
                                <div class="swiper-slide">
                                    <div class="news"><a class="news__frame"
                                            href="{{ route('investment_guide_detail', ['id' => $item->id, 'slug' => $item->slug, 'ref' => 'app.investment_guide']) }}"><img
                                                src="{{ $item->image }}" alt="" /></a>
                                        <div class="news__body">
                                            <div class="news__info">
                                                <div class="news__time"><i
                                                        class="fal fa-clock me-2"></i><span>{{ \Carbon\Carbon::parse($item->published_at)->format('d/m/Y') }}</span>
                                                </div>
                                                <a class="news__like" href="javascript:void(0)"
                                            data-id="{{ $item->id }}" data-type="App\Models\InvestmentGuide">
                                             <i class="fas fa-fw fa-heart {{ $item->is_interested ? 'text-danger' : '' }}"></i>
                                            </div>
                                            <h3 class="news__title custom-desc"><a
                                                    href="{{ route('investment_guide_detail', ['id' => $item->id, 'slug' => $item->slug, 'ref' => 'app.investment_guide']) }}">{{ $item->name }}</a>
                                            </h3>
                                            <div class="news__desc">
                                                {{ $item->description }}
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
                <nav class="d-flex justify-content-center mt-40 mt-lg-60"><a class="button" href="{{ url($locale . '/' . __('app.investment_guide_link')) }}" style="text-transform: capitalize;">{{ __('app.view_more') }}</a>
                </nav>
            </div>
        </section>
    </div>
@endsection

@push('bottom')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileItems = document.querySelectorAll('.file-item');
        const iframe = document.getElementById('fileViewer');

        fileItems.forEach(item => {
            item.addEventListener('click', function() {
                const fileUrl = this.getAttribute('data-file');
                iframe.src = fileUrl;
            });
        });
    });
</script>
@endpush