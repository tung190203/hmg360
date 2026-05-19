// menu toggle
$(function () {
  $(".menu-toggle").on("click", function () {
    var $toggle = $(this);

    $toggle.toggleClass("active").siblings(".menu-sub").slideToggle();

    $toggle.siblings(".menu-mega").children(".menu-sub").slideToggle();

    $toggle.parent().siblings(".menu-item-group").children(".menu-sub").slideUp();

    $toggle.parent().siblings(".menu-item-group").children(".menu-mega").children(".menu-sub").slideUp();

    $toggle.parent().siblings(".menu-item-group").children(".menu-toggle").removeClass("active");
  });

  $(".menu-item-group > .menu-link, .menu-item-mega > .menu-link").on("click", function (e) {
    if ($(window).width() < 1200 || !mobileAndTabletCheck()) return;

    e.preventDefault();
  });
});

// navbar mobile toggle
$(function () {
  var $body = $("html, body");
  var $navbar = $(".js-navbar");
  var $navbarToggle = $(".js-navbar-toggle");

  $navbarToggle.on("click", function () {
    $navbarToggle.toggleClass("active");
    $navbar.toggleClass("is-show");
    $body.toggleClass("overflow-hidden");
  });
});

$(function () {
  var $moveTop = $(".btn-movetop");
  var $window = $(window);
  var $body = $("html");

  if (!$moveTop.length) return;

  $window.on("scroll", function () {
    if ($window.scrollTop() > 150) {
      $moveTop.addClass("show");

      return;
    }

    $moveTop.removeClass("show");
  });

  $moveTop.on("click", function () {
    window.scrollTo({
      top: 0,
      left: 0,
      behavior: "smooth"
    });
  });
});

// swiper template
function addSwiper(selector, options = {}) {
  return Array.from(document.querySelectorAll(selector), function (item) {
    var $sliderContainer = $(item),
        $sliderEl = $sliderContainer.find(selector + "__container");

    if (options.navigation) {
      $sliderContainer.addClass("has-nav");
      options.navigation = {
        prevEl: $sliderContainer.find(selector + "__prev"),
        nextEl: $sliderContainer.find(selector + "__next")
      };
    }

    if (options.pagination) {
      $sliderContainer.addClass("has-pagination");
      options.pagination = {
        el: $sliderContainer.find(selector + "__pagination"),
        clickable: true
      };
    }

    return new Swiper($sliderEl, options);
  });
}

$(function () {
  designSyncSlider();

  addSwiper('.news-slider', {
    loop: false,
    navigation: true,
    spaceBetween: 0,
    speed: 500,
    slidesPerView: 2,
    breakpoints: {
      992: {
        slidesPerView: 3
      }
    }
  });
});

function designSyncSlider() {
  if (!$(".design-slider, .design-thumb-slider").length) {
    return;
  }

  const thumbSlider = addSwiper(".design-thumb-slider", {
    loop: false,
    navigation: true,
    freeMode: true,
    slidesPerView: 3,
    spaceBetween: 8,
    watchSlidesProgress: true,
    watchSlidesVisibility: true,
    breakpoints: {
      576: {
        spaceBetween: 16
      },
      768: {
        slidesPerView: 3,
        spaceBetween: 20
      },
      992: {
        slidesPerView: 4,
        spaceBetween: 20
      },
      1200: {
        slidesPerView: 5,
        spaceBetween: 20
      }
    }
  })[0];

  addSwiper(".design-slider", {
    loop: false,
    effect: "fade",
    navigation: true,
    pagination: true,
    allowTouchMove: false,
    thumbs: {
      swiper: thumbSlider
    }
  });
}

$(function () {
  $('.search').on('click', function (e) {
    e.stopPropagation();
  });

  $('.js-search-btn').on('click', function (e) {
    e.stopPropagation();
    e.preventDefault();

    $('.search').fadeToggle('fast').find('input').focus();
  });

  $('.h-dropdown__toggle').on('click', function (e) {
    e.stopPropagation();
    const $menu = $(this).siblings('.h-dropdown__menu');
    $('.h-dropdown__menu').not($menu).hide();
    $menu.fadeToggle('fast');
  });

  $('.h-dropdown__menu').on('click', function (e) {
    e.stopPropagation();

    $('.h-dropdown__menu').not(this).hide();
  });

  $('html, body').on('click', function (e) {
    $('.h-dropdown__menu').fadeOut('fast');
  });

  $('.js-switch-modal').on('click', function (e) {
    e.preventDefault();
    const target = $(this).attr('href');

    $(this).closest('.modal').modal('hide');

    if (!$(target).length) return;

    setTimeout(function () {
      $(target).modal('show');
    }, 300);
  });

  $('.range-input__input').on('input', function () {
    const value = Number(this.value).toLocaleString('en-Us') + ' tỷ đồng';

    // console.log('value: ', this.value, this.value.toLocaleString());
    $('.range-input__price').text(value);
  });
  $('.white-range').on('input', function () {
    const value = Number(this.value).toLocaleString('en-Us');

    // console.log('value: ', this.value, this.value.toLocaleString());
    $('.range-input__price1').text(value);
  });
});

$(function () {
  const $window = $(window);
  const $header = $('.header');
  const $headerWrapper = $('.header__wrapper');

  $window.on('scroll', function () {
    if ($window.width() < 1200) return;

    if ($(window).scrollTop() > 80) {
      if (!$header.hasClass('is-sticky')) {
        $header.css('min-height', $headerWrapper.outerHeight() + 'px');
        $header.addClass('is-sticky');
      }
    } else {
      if ($header.hasClass('is-sticky')) {
        $header.removeClass('is-sticky');
        $header.css('min-height', '');
      }
    }
  });
});

$(function () {
  var $slider = $('.features-slider');
  var $wrapper = $slider.find('.swiper-wrapper');
  var totalSlides = $wrapper.find('.swiper-slide').length;

  var autoplayOptions = false;
  if (totalSlides > 4) {
    autoplayOptions = {
      delay: 2000,
      disableOnInteraction: false
    };
  }

  addSwiper('.features-slider', {
    loop: totalSlides > 4,
    autoplay: autoplayOptions,
    slidesPerView: 2,
    spaceBetween: 24,
    breakpoints: {
      768: {
        slidesPerView: 3
      },
      1200: {
        slidesPerView: 4
      }
    }
  });
});

$(function () {
  var $slider = $('.partners-slider');
  var $wrapper = $slider.find('.swiper-wrapper');
  var totalSlides = $wrapper.find('.swiper-slide').length;

  var autoplayOptions = false;
  if (totalSlides > 5) {
    autoplayOptions = {
      delay: 2000,
      disableOnInteraction: false
    };
  }

  addSwiper('.partners-slider', {
    loop: totalSlides > 5,
    autoplay: autoplayOptions,
    slidesPerView: 2,
    spaceBetween: 24,
    breakpoints: {
      768: {
        slidesPerView: 3
      },
      1200: {
        slidesPerView: 5
      }
    }
  });
});


$(document).ready(function () {
  new Swiper('.project-nav-scroll', {
      slidesPerView: 'auto', 
      spaceBetween: 10,
      freeMode: true, 
      loop: true, 
  });
});