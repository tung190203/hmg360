@extends('frontend.index')

@section('content')
    <div class="page__content">
        <!-- main content-->
        <article class="banner">
            <div class="banner__breadcrumb">
                <nav>
                    <div class="container">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active"></li>
                        </ol>
                    </div>
                </nav>
            </div>
            <img class="banner__bg" src="{{ asset('images/tin-tuc-banner.jpg') }}" alt=""/>
            <div class="banner__title">{{ __('app.news') }}</div>
        </article>
        <section class=" pt-40 pb-40">
            <div class="container" id="data-wrapper">
                @include('frontend.home.partials.news_list')
            </div>
        </section>
    </div>
@endsection

@push('bottom')
<script>
    $(document).ready(function () {
        $(document).on('click', '.pagination a', function(event) {
            event.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            fetch_data(page);
        });

        function fetch_data(page) {
            var url = new URL(window.location.href);
            url.searchParams.set('page', page);

            $.ajax({
                url: url.toString(),
                type: "GET",
                success: function(data) {
                    $('#data-wrapper').html(data.html);
                    // re-initialize tippy if used
                    if (typeof tippy !== 'undefined') {
                        tippy('[data-tippy-content]');
                    }
                    // scroll to top of section
                    $('html, body').animate({
                        scrollTop: $("#data-wrapper").offset().top - 100
                    }, 500);
                },
                error: function(xhr) {
                    console.error("Lỗi khi tải trang");
                }
            });
        }
    });
</script>
@endpush
