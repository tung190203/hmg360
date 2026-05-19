<section class="section section--bg-pattern">
    <div class="container">
        <h2 class="section__title text-white mb-4 text-uppercase">{{ __('app.subscribe_project_info') }}</h2>
        @php
            $locale = app()->getLocale() === 'vi' ? 'vn' : app()->getLocale();
        @endphp

        <div class="text-center">
            <a class="button" href="{{ url($locale . '/' . __('app.contact_link')) }}">
                {{ __('app.subscrible_text') }}
            </a>
        </div>
    </div>
</section>
<footer class="footer">
    <div class="footer__inner"><img class="footer__bg-1" src="{{asset('./images/texture-4.png')}}" alt="" /><img
            class="footer__bg-2" src="{{asset('./images/texture-5.png')}}" alt="" />
            {{-- <img class="footer__bg-3" src="{{asset('./images/texture-6.png')}}" alt="" /> --}}
        <div class="container">
            <div class="d-flex justify-content-start align-items-center mb-4">
                <div>
                    <a class="footer__logo" href="/"><img src="{{$setting['logo_footer'] ?? ''}}" alt="" /></a>
                </div>
                <div class="ms-3">
                    <div class="footer__title custom-text-footer">
                        {!! \App\Models\Setting::getSettingByKey('footer_info') !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-7 mb-40 mb-lg-0">
                    <ul class="f-contact">
                        <li><i class="fal fa-fw fa-map-marker-alt me-2"></i><span>{{ __('app.address') }}: {!! \App\Models\Setting::getSettingByKey('address') !!}</span>
                        </li>
                        <li><i class="fal fa-fw fa-globe me-2"></i>{{ __('app.website') }}: <a
                                href="{{$setting['website'] ?? ''}}"
                                target="_blank">{{$setting['website'] ?? ''}}</a></li>
                        <li><i class="fal fa-fw fa-phone me-2" style="transform: rotate(-270deg);"></i>{{ __('app.phone') }}: <a
                                href="tel:{{ preg_replace('/\D+/', '', $setting['phone'] ?? '') }}">{{ $setting['phone'] ?? '' }}</a></li>
                    </ul>
                    <div class="footer__content mt-4">
                       {!! \App\Models\Setting::getSettingByKey('copyright_notice') !!}
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="footer__title">{!! \App\Models\Setting::getSettingByKey('social_title') !!}</div>
                    <ul class="f-social">
                        <li><a href="{{ $setting['facebook'] ?? '' }}"><img src="{{asset('./images/icon-facebook.svg')}}" alt="" /></a></li>
                        <li><a href="#!"><img src="{{asset('./images/icon-youtube.svg')}}" alt="" /></a></li>
                        <li><a href="#!"><img src="{{asset('./images/icon-tik-tok.svg')}}" alt="" /></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="footer__copyright">
        <div class="container">
            {!! \App\Models\Setting::getSettingByKey('copyright') !!}
        </div>
    </div>
</footer>
