(function($) {

  "use strict";

    // fixed menu js
  $(window).on('scroll', function () {
    var scroll = $(window).scrollTop();
    if (scroll < 120) {
      $(".header-3").removeClass("sticky-menu");
    } else {
      $(".header-3").addClass("sticky-menu");
    }
  });

  function preloaderLoad() {
    if($('.preloader').length){
      $('.preloader').delay(200).fadeOut(300);
    }
    $(".preloader_disabler").on('click', function() {
      $("#preloader").hide();
    });
  }

  $(document).on('click', '.close-newsletter', function () {
    $('.newsletter-area').addClass('remove');
  });


  // Countdown timer js
  function initializeCountdown(targetDate, countdownElement) {
    if (!countdownElement) {
      // Exit the function if the countdown element is not found
      return;
    }


  // Looking Product js For Home 21
    document.addEventListener('DOMContentLoaded', () => {
      // Get the necessary elements
      const productShowcase = document.getElementById('productShowcase');
      const infoButton = document.getElementById('infoButton');
      const infoButton2 = document.getElementById('infoButton2');
      const infoButton3 = document.getElementById('infoButton3');
      const productInfo = document.getElementById('productInfo');
      const productInfo2 = document.getElementById('productInfo2');
      const productInfo3 = document.getElementById('productInfo3');

      // Check if elements exist before adding event listeners
      if (infoButton && productInfo) {
        infoButton.addEventListener('click', (event) => {
              event.stopPropagation(); // Prevent event from bubbling to the container
              productInfo.style.display = 'block';
            });
      }

      if (infoButton2 && productInfo2) {
        infoButton2.addEventListener('click', (event) => {
              event.stopPropagation(); // Prevent event from bubbling to the container
              productInfo2.style.display = 'block';
            });
      }
      if (infoButton3 && productInfo3) {
        infoButton3.addEventListener('click', (event) => {
              event.stopPropagation(); // Prevent event from bubbling to the container
              productInfo3.style.display = 'block';
            });
      }

      if (productShowcase) {
          // Hide product info when clicking anywhere in the container
        productShowcase.addEventListener('click', () => {
          if (productInfo) productInfo.style.display = 'none';
          if (productInfo2) productInfo2.style.display = 'none';
          if (productInfo3) productInfo3.style.display = 'none';
        });
      }

      if (productInfo) {
          // Prevent hiding product info when clicking inside the info box
        productInfo.addEventListener('click', (event) => {
              event.stopPropagation(); // Prevent event from bubbling to the container
            });
      }

      if (productInfo2) {
          // Prevent hiding product info when clicking inside the info box
        productInfo2.addEventListener('click', (event) => {
              event.stopPropagation(); // Prevent event from bubbling to the container
            });
      }
    });

    function updateCountdown() {
      const now = new Date().getTime();
      const distance = targetDate - now;

      if (distance < 0) {
        clearInterval(interval);
        return;
      }

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Update the countdown values only if the elements are found
      const daysElement = countdownElement.querySelector('.days .count');
      const hoursElement = countdownElement.querySelector('.hours .count');
      const minutesElement = countdownElement.querySelector('.minutes .count');
      const secondsElement = countdownElement.querySelector('.seconds .count');

      if (daysElement) daysElement.textContent = days;
      if (hoursElement) hoursElement.textContent = hours;
      if (minutesElement) minutesElement.textContent = minutes;
      if (secondsElement) secondsElement.textContent = seconds;
    }

    const interval = setInterval(updateCountdown, 1000);
    updateCountdown();
  }
  // Set the target date and time
  const targetDate = new Date('2026-06-01T00:00:00').getTime(); // Adjust the target date
  const countdownBox = document.querySelector('.countdown-box');

  // Initialize the countdown
  initializeCountdown(targetDate, countdownBox);

  function mobileNavToggle() {
    if ($('#main-nav-bar .navbar-nav .sub-menu').length) {
      var subMenu = $('#main-nav-bar .navbar-nav .sub-menu');
      subMenu.parent('li').children('a').append(function () {
        return '<button class="sub-nav-toggler"> <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>';
      });
      var subNavToggler = $('#main-nav-bar .navbar-nav .sub-nav-toggler');
      subNavToggler.on('click', function () {
        var Self = $(this);
        Self.parent().parent().children('.sub-menu').slideToggle();
        return false;
      });

    };
  }

    // Inner pages sidebar fixed and content scroll
  (function($) {
    var scroll_childs = $('.scroll-to-fixed-child');
    for (var i = 0, length = scroll_childs.length; i < length; i++) {
      var scroll_child = $(scroll_childs[i]);

      scroll_child.scrollToFixed({
        marginTop: $('header').outerHeight(true) + 10,
        zIndex: 2,
        spacerClass: 'd-none',
        removeOffsets: true,
        limit: function() {
          var parent = this.parents('.scroll-to-fixed-parent');
          return parent.offset().top + parent.outerHeight(true) - this.outerHeight(true) - 20;

        }
      });
    }
  })(window.jQuery);

    // Home 17 THIS JUST IN Section Code
  $(function() {
    $('.info-open').on('click', function(){
      $(this).siblings('.product-size-info').slideDown(400);
    });

    $('.info-close').on('click', function(){
      $(this).closest('.product-size-info').slideUp(400);
    });
  });


    // === jQuery MMENU S T A R T ===
  $(function () {
    var $menuElement = $("#menu");

    if (!$menuElement.length || $menuElement.hasClass("mm-menu")) {
      return;
    }

    var site = window.PP_SITE || {};
    var homeUrl = site.home || "/";
    var logoUrl = site.logo || (site.logoAlt || "/vastu/images/logo.svg");
    var loginUrl = site.login || "#";
    var cartUrl = site.cart || "#";
    var social = site.social || {};

    var accountUrl = (site.authenticated && site.accountOverview) ? site.accountOverview : loginUrl;
    var accountLabel = site.authenticated ? 'My account' : 'Account';
    var accountIcon = '<i class="flaticon-user-1"></i>';
    if (site.authenticated) {
      var name = (site.user && site.user.name) ? String(site.user.name).trim() : 'U';
      var parts = name.split(/\s+/);
      var initials = (parts[0] || 'U').charAt(0).toUpperCase();
      if (parts[1]) initials += parts[1].charAt(0).toUpperCase();
      accountIcon = '<span class="site-account-avatar notranslate" translate="no" aria-hidden="true">' + initials + '</span>';
    }

    var headerHTML =
    '<div class="mmx-header">' +
    '<div class="mmx-left">' +
    '<a href="#" class="js-mm-close" aria-label="Close menu"><i class="flaticon-close"></i></a>' +
    '<a href="#" class="js-mm-search cart-search-btn" aria-label="Search"><i class="flaticon-web"></i></a>' +
    '</div>' +
    '<a class="mmx-logo" href="' + homeUrl + '" aria-label="Vastutathastu home"><img src="' + logoUrl + '" alt="Vastutathastu" onerror="this.onerror=null;this.src=\'' + (site.logoAlt || logoUrl) + '\'"></a>' +
    '<div class="mmx-right">' +
    '<a class="signin-cart-btn' + (site.authenticated ? ' is-logged-in' : '') + '" href="' + accountUrl + '" aria-label="' + accountLabel + '">' + accountIcon + '</a>' +
    '<a href="' + cartUrl + '" aria-label="Shopping bag"><i class="flaticon-shopping-bag"></i></a>' +
    '</div>' +
    '</div>';

    var footerHTML =
    '<div class="mmx-footer">' +
    '<h4 class="social-title">FOLLOW US</h4>' +
    '<div class="mmx-social">' +
    '<a href="' + (social.facebook || '#') + '" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>' +
    '<a href="' + (social.instagram || '#') + '" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>' +
    '<a href="' + (social.youtube || '#') + '" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>' +
    // '<a href="' + (social.pinterest || '#') + '" target="_blank" rel="noopener" aria-label="Pinterest"><i class="fa-brands fa-pinterest"></i></a>' +
    // '<a href="' + (social.tiktok || '#') + '" target="_blank" rel="noopener" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>' +
    '</div>' +
    '</div>';

      // init mmenu with back button enabled
    var mmenuOptions = {
      extensions: ["position-left","pagedim-white","theme-light"],
      slidingSubmenus: true,
        navbar: { title: "Menu" }, // shows "Back" button when in submenus
        navbars: [
          { position: "top", content: [ headerHTML ] },
          { position: "bottom", content: [ footerHTML ] }
        ]
      };
    var mmenuConfig = {};
    // Keep support/account header at top — don't let mmenu wrap random body divs
    if (document.getElementById('page')) {
      mmenuConfig.offCanvas = {
        page: { selector: '#page' }
      };
    }
    var $menu = $menuElement.mmenu(mmenuOptions, mmenuConfig);

    var api = $menu.data("mmenu");

    $(document).on("click", '.menubar[href="#menu"], #menu-btn', function (e) {
      e.preventDefault();
      api.open();
    });

    $(document).on("click", ".js-mm-close", function (e) {
      e.preventDefault();
      api.close();
    });

    // Search from menu header: close drawer then open search (same as homepage)
    $(document).on("click", ".mmx-left .js-mm-search, .mmx-header .cart-search-btn", function () {
      api.close();
    });

    $(document).on("keydown", function (e) {
      if (e.key === "Escape" && $menu.hasClass("mm-menu_opened")) {
        api.close();
      }
    });
  });
    // === jQuery MMENU E N D ===


    /* ----- Shop List Page Side Panel ----- */
  $(function() {
      // Open panel
    $(document).on('click', '.open-panel-lg', function() {
      $('.side-panel-lg').addClass('active');
      $('.side-panel-overlay, .shop-list3').addClass('active');
    });

      // Close panel on button or overlay click
    $(document).on('click', '.close-panel-lg, .side-panel-overlay', function() {
      $('.side-panel-lg').removeClass('active');
      $('.side-panel-overlay, .shop-list3').removeClass('active');
    });
  });

  /*=============================================
      =        color swatch product    =
  =============================================*/
  var swatchColor = function () {
    if ($(".card-product").length > 0) {
      $(".color-swatch").on("click, mouseover", function () {
        var swatchColor = $(this).find("img").attr("src");
        var imgProduct = $(this).closest(".card-product").find(".img-product");
        imgProduct.attr("src", swatchColor);
        $(this)
        .closest(".card-product")
        .find(".color-swatch.active")
        .removeClass("active");

        $(this).addClass("active");
      });
    }
  };

    /* ----- This code for menu ----- */
  $(window).on('scroll', function() {
    if ($('.scroll-to-top').length) {
      var strickyScrollPos = 100;
      if ($(window).scrollTop() > strickyScrollPos) {
        $('.scroll-to-top').fadeIn(500);
      } else if ($(this).scrollTop() <= strickyScrollPos) {
        $('.scroll-to-top').fadeOut(500);
      }
    };
    if ($('.stricky').length) {
      var headerScrollPos = $('.header-navigation').next().offset().top;
      var stricky = $('.stricky');
      if ($(window).scrollTop() > headerScrollPos) {
        stricky.removeClass('slideIn animated');
        stricky.addClass('stricky-fixed slideInDown animated');
      } else if ($(this).scrollTop() <= headerScrollPos) {
        stricky.removeClass('stricky-fixed slideInDown animated');
        stricky.addClass('slideIn animated');
      }
    };
  });
    /** Main Menu Custom Script End **/

  /*===========================================
      =          Data Background    =
  =============================================*/
  $("[data-background]").each(function () {
    $(this).css("background-image", "url(" + $(this).attr("data-background") + ")")
  });

  $("[data-bg-color]").each(function () {
    $(this).css("background-color", $(this).attr("data-bg-color"));
  });

  /*===========================================
          =       Odometer Active    =
  =============================================*/
  // Only when the jquery.appear plugin is loaded: without the guard this line threw on pages that
  // don't include it and stopped the rest of this file (search button, menus …) from running.
  if ($.fn.appear && $('.odometer').length) {
    $('.odometer').appear(function (e) {
      var odo = $(".odometer");
      odo.each(function () {
        var countNumber = $(this).attr("data-count");
        $(this).html(countNumber);
      });
    });
  }



  /*=============================================
      =  Initialize Swipers on page load instagram home20 =
  =============================================*/
    // var swiperInstances = [];
    // $('.gt-slider').each(function () {
    //     var sliderContainer = $(this);
    //     var swiperInstance = initializeSwiper(sliderContainer);
    //     swiperInstances.push(swiperInstance);
    // });


  // home29 banner slider
  var slider = new Swiper('.home29-banner-slider', {
      slidesPerView: 1,
      speed: 1500,
      spaceBetween: 10,
      loop: true,
      parallax: true,
      autoplay: {
        delay: 3500,
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
      },
    });


  /*=============================================
      =        su-blog-4-slider      =
  =============================================*/
  var slider = new Swiper('.su-blog-4-slider', {
    speed: 700,
    spaceBetween: 30,
    loop: true,
    scrollbar: {
      el: ".swiper-scrollbar",
      hide: false,
    },
    navigation: {
      nextEl: ".blog-next",
      prevEl: ".blog-prev",
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 2,
      },
      '1200': {
        slidesPerView: 2,
      },
      '991': {
        slidesPerView: 2,
      },
      '768': {
        slidesPerView: 1,
      },
      '576': {
        slidesPerView: 1,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  var slider = new Swiper('.two-grid-slider', {
    speed: 700,
    spaceBetween: 30,
    loop: true,
    scrollbar: {
      el: ".swiper-scrollbar",
      hide: false,
    },
    autoplay: {
      delay: 4000,
    },
    thumbs: {
      swiper: swiper,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 2,
      },
      '1200': {
        slidesPerView: 2,
      },
      '991': {
        slidesPerView: 2,
      },
      '768': {
        slidesPerView: 1,
      },
      '576': {
        slidesPerView: 1,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  // Blog Slider Section Home Layout 33
  var inspirationSwiper = new Swiper('.blog-slider-home33', {
    speed: 700,
    loop: true,
    spaceBetween: 40,
    navigation: {
      nextEl: ".explore-next",
      prevEl: ".explore-prev",
    },
    autoplay: {
      delay: 1200,
    },
    breakpoints: {
      1400: { slidesPerView: 2 },
      1366: { slidesPerView: 1 },
      767:  { slidesPerView: 1 },
      0:    { slidesPerView: 1 }
    }
  });

  // Testimonial Slider Thumb 23
  var swiper = new Swiper(".three-grid-thumb-slider", {
    loop: true,
    spaceBetween: 10,
    slidesPerView: 4,
    freeMode: true,
    watchSlidesProgress: true,
  });
  var swiper2 = new Swiper(".one-grid-text-slider", {
    loop: true,
    spaceBetween: 10,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    thumbs: {
      swiper: swiper,
    },
  });

  $(document).on('click', '.collection-page__save', function () {
    var button = $(this);
    var active = !button.hasClass('is-active');
    button.toggleClass('is-active', active).attr('aria-pressed', active);
  });

  /*=============================================
    =        su-product-7-slider       =
  =============================================*/
  var headerSlider = new Swiper('.su-product-7-slider', {
    slidesPerView: 1,
    spaceBetween: 24,
    loop: true,
      // autoplay: true,
    speed: 600,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });
  var headerSlider = new Swiper('.su-product-7-slider-2', {
    slidesPerView: 1,
    spaceBetween: 24,
    loop: true,
      // autoplay: true,
    speed: 600,
    pagination: {
      el: ".swiper-pagination2",
      clickable: true,
    },
  });

  // Instagram-Slider-Home20
  var beFeaturedSlider20 = new Swiper('.insta-slider-home20', {
    loop: true,
    slidesPerView: 3,
    spaceBetween: 10,
    breakpoints: {
      0: {
        slidesPerView: 1,
        spaceBetween: 13,
      },
      576: {
        slidesPerView: 2,
        spaceBetween: 13,
      },
      768: {
        slidesPerView: 3,
        spaceBetween: 13,
      },
      992: {
        slidesPerView: 3,
        spaceBetween: 13,
      },
      1200: {
        slidesPerView: 5,
        spaceBetween: 13,
      },
    },
  });

  // fave-brand-slider20
  var faveBrandSlider20 = new Swiper('.fave-brand-slider20', {
    loop: true,
    slidesPerView: 4,
    spaceBetween: 10,
    breakpoints: {
      0: {
        slidesPerView: 1,
        spaceBetween: 13,
      },
      576: {
        slidesPerView: 2,
        spaceBetween: 13,
      },
      768: {
        slidesPerView: 3,
        spaceBetween: 13,
      },
      992: {
        slidesPerView: 3,
        spaceBetween: 13,
      },
      1200: {
        slidesPerView: 4,
        spaceBetween: 13,
      },
    },
    navigation: {
      nextEl: ".swiper-next",
      prevEl: ".swiper-prev",
    },
  });

  /*=============================================
    =        header-top-slider-active      =
  =============================================*/
  var headerSlider = new Swiper('.header-top-slider-active', {
    slidesPerView: 1,
    spaceBetween: 24,
    loop: true,
    autoplay: {
      delay: 1500,
    },
    speed: 600,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
  });

  /*=============================================
    =        cu-banner-4-zoom      =
  =============================================*/
  var slider = new Swiper('.su-banner-4-zoom', {
    slidesPerView: 1,
    speed:1500,
    spaceBetween: 0,
    loop: true,
    parallax: true,
    autoplay: {
      delay: 3500,
    },
    navigation: {
      nextEl: ".su-banner-4-next",
      prevEl: ".su-banner-4-prev",
    },
  });

  /*=============================================
    =        One Grid Slider      =
  =============================================*/
  var slider = new Swiper('.one-grid-slider', {
    slidesPerView: 1,
    speed:1500,
    spaceBetween: 0,
    loop: true,
    parallax: true,
    autoplay: {
      delay: 3500,
    },
    navigation: {
      nextEl: ".su-banner-4-next, .next",
      prevEl: ".su-banner-4-prev, .prev",
    },
    pagination: {
      el: ".swiper-pagination",
      type: "fraction",
    },
  });
  var slider = new Swiper('.one-grid-slider-home29', {
    slidesPerView: 1,
    speed:1500,
    spaceBetween: 0,
    loop: true,
    parallax: true,
    autoplay: {
      delay: 3500,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });

  // Banner Page Number Slider Style 
  var oneGridBannerSlider = new Swiper('.one-grid-pagination-slider', {
    slidesPerView: 1,
    spaceBetween: 0,
    loop: true,
    // autoplay: true,
    speed: 600,
    effect: "fade",
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
      renderBullet: function (index, className) {
        var formattedIndex = (index + 1).toString().padStart(2, '0');
        return '<span class="' + className + '">' + formattedIndex + "</span>";
      },
    },
  });

  // Banner Pagination Slider Style 
  var oneGridBannerSlider = new Swiper('.one-grid-bullet-slider', {
    slidesPerView: 1,
    spaceBetween: 0,
    loop: true,
    autoplay: true,
    speed: 600,
    effect: "fade",
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });

  /*=============================================
     =        cu-banner-4-zoom      =
   =============================================*/
  var slider = new Swiper('.su-product-slide', {
    slidesPerView: 1,
    speed: 500,
    spaceBetween: 0,
    loop: true,
    autoplay: {
      delay: 3500,
    },
    navigation: {
      nextEl: ".product-next",
      prevEl: ".product-prev",
    },
  });

  /*=============================================
    =        cu-banner-3      =
  =============================================*/
  var slider = new Swiper('.su-banner-3-zoom', {
    slidesPerView: 1,
    speed: 1500,
    direction: "vertical",
    spaceBetween: 0,
    loop: false,
    mousewheel: {
      releaseOnEdges: true, // Allow scrolling outside the slider
    },
    parallax: true,
    // autoplay: {
    //   delay: 3500,
    // },
    navigation: {
      nextEl: ".su-banner-16-next",
      prevEl: ".su-banner-16-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });

  /*=============================================
    =       su-collections-15-slider      =
  =============================================*/
  var slider = new Swiper('.su-collection-15-slider', {
    loop: "true",
    spaceBetween: 0,
    speed: 1000,
    breakpoints: {
      1199: {
        slidesPerView: 3,
      },
      991: {
        slidesPerView: 3,
      },
      575: {
        slidesPerView: 2,
      },
      320: {
        slidesPerView: 1,
      },
    },
  });

  /*=============================================
    =       su-collections-15-slider      =
  =============================================*/
  var slider = new Swiper('.su-collection-15-slider-2', {
    loop: "true",
    spaceBetween: 0,
    speed: 1000,
    breakpoints: {
      1199: {
        slidesPerView: 4,
      },
      991: {
        slidesPerView: 3,
      },
      575: {
        slidesPerView: 2,
      },
      320: {
        slidesPerView: 1,
      },
    },
  });


  $(document).on("mouseenter", ".swiper-slide", function () {
    let newBackground = $(this).find(".collection_item").data("bg");
    $(".swiper-slide.active-slider").removeClass("active-slider");
    $(this).addClass("active-slider");
    $(".collection-wrap-bg").css("background-image", "url(" + newBackground + ")");
  });

  $(document).on("mouseenter", ".hover-bg-img", function () {
    let newBackground = $(this).find(".item-block").data("bg");
    $(".hover-bg-img.active-slider").removeClass("active-slider");
    $(this).addClass("active-slider");
    $(".collection-wrap-bg").css("background-image", "url(" + newBackground + ")");
  });

  $(document).on("ready",function () {
    // Function to toggle play/pause
    function setupVideoControls(videoClass, buttonClass, playIcon, pauseIcon) {
      const $video = $(videoClass);
      const videoElement = $video.get(0);
      const $controlButton = $(buttonClass);
      const $icon = $controlButton.find('i');

      // Ensure button is always clickable
      $controlButton.css({
        "z-index": "10",
        "cursor": "pointer"
      });

      // Play/Pause toggle on button click
      $controlButton.on('click', function () {
        if (videoElement.paused) {
          videoElement.play();
          $icon.removeClass(playIcon).addClass(pauseIcon);
        } else {
          videoElement.pause();
          $icon.removeClass(pauseIcon).addClass(playIcon);
        }
      });

      // Update icon if video is played/paused manually
      $video.on('play', function () {
        $icon.removeClass(playIcon).addClass(pauseIcon);
      });

      $video.on('pause', function () {
        $icon.removeClass(pauseIcon).addClass(playIcon);
      });
    }

    // Initialize video controls
    setupVideoControls('.video-19', '.control-video', 'fa-play-circle', 'fa-pause-circle');
    setupVideoControls('.shop-video', '.control-video1', 'fa-play', 'fa-pause');
  });

  // Start Home layout 43 Banner Large Text Scroll effect
  $(function () {
    var scrollPoint = 150;
    $(window).on("scroll", function () {
      if ($(this).scrollTop() > scrollPoint) {
        $("body").addClass("header-active");
      } else {
        $("body").removeClass("header-active");
      }
    });
  });

  // End Home layout 43 Banner Large Text Scroll effect
  


  /*=============================================
    =        su-brands-5-text-slide       =
  =============================================*/
  var textslider5 = new Swiper('.brands-5-text-slide', {
    slidesPerView: 1,
    centeredSlides: true,
    loop: true,
    loopedSlides: 6,
  });

  /*=============================================
    =        su-brands-5-img-slide       =
  =============================================*/
  var imgslider5 = new Swiper('.brands-5-img-slide', {
    slidesPerView: 'auto',
    spaceBetween: 10,
    centeredSlides: true,
    loop: true,
    slideToClickedSlide: true,
    breakpoints: {
      '992': {
        spaceBetween: 90,
        slidesPerView: 3,
      },
      '768': {
        spaceBetween: 90,
        slidesPerView: 2,
      },
      '576': {
        spaceBetween: 90,
      },
      '0': {
        spaceBetween: 26,
        slidesPerView: 1,
      },
    }
  });

  textslider5.controller.control = imgslider5;
  imgslider5.controller.control = textslider5;


  /*=============================================
    =        su-product-7-slider       =
  =============================================*/
  var headerSlider = new Swiper('.su-product-7-slider', {
    slidesPerView: 1,
    spaceBetween: 24,
    loop: true,
    autoplay: true,
    speed: 600,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });

  var hoverPin = function () {
    if ($(".wrap-lookbook-hover").length) {
      $(".bundle-pin-item").on("mouseover", function () {
        $(".bundle-hover-wrap").addClass("has-hover");
        var $el = $('.' + this.id).show();
        $('.bundle-hover-wrap .bundle-hover-item').not($el).addClass("no-hover");
      });
      $(".bundle-pin-item").on("mouseleave", function () {
        $(".bundle-hover-wrap").removeClass("has-hover");
        $(".bundle-hover-item").removeClass("no-hover");
      });
    }
  };
  
  // home46-instagram-feed
  var slider = new Swiper('.home46-instagram-feed', {
    speed: 700,
    spaceBetween: 24,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    scrollbar: {
      el: ".swiper-scrollbar",
      hide: false,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 5,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  var slider = new Swiper('.five-grid-slider-1', {
    speed: 800,
    loop: true,
    spaceBetween: 20, 
    autoplay: {
      delay: 4000,
      disableOnInteraction: false,
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    navigation: {
      nextEl: '.next',
      prevEl: '.prev',
    },
    breakpoints: {
      1400: {
        slidesPerView: 3.5,
        spaceBetween: 20,
      },
      1200: {
        slidesPerView: 3,
        spaceBetween: 20,
      },
      991: {
        slidesPerView: 2.5,
        spaceBetween: 20,
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 20,
      },
      576: {
        slidesPerView: 1,
        spaceBetween: 20,
      },
      0: {
        slidesPerView: 1,
        spaceBetween: 20,
      },
    },
  });

  /*=============================================
    =        home46-brands-slide       =
  =============================================*/
  var slider = new Swiper('.home46-brands-slide', {
    loop: true,
    freemode: true,
    slidesPerView: 'auto',
    spaceBetween: 162,
    centeredSlides: true,
    allowTouchMove: false,
    speed: 6000,
    autoplay: {
      delay: 1,
      disableOnInteraction: true,
    },
    breakpoints: {
      '992': {
        spaceBetween: 55,
      },
      '768': {
        spaceBetween: 50,
      },
      '576': {
        spaceBetween: 50,
      },
      '0': {
        spaceBetween: 50,
      },
    }
  });

  /*=============================================
      =        su-brands-4-slide       =
  =============================================*/
  var slider = new Swiper('.su-brands-7-slide', {
    loop: true,
    freemode: true,
    slidesPerView: 'auto',
    spaceBetween: 162,
    centeredSlides: true,
    allowTouchMove: false,
    speed: 4000,
    autoplay: {
      delay: 1,
      disableOnInteraction: true,
    },
    breakpoints: {
      '992': {
        spaceBetween: 200,
      },
      '768': {
        spaceBetween: 90,
      },
      '576': {
        spaceBetween: 90,
      },
      '0': {
        spaceBetween: 50,
      },
    }
  });

  /*=============================================
      =        su-testimonial-7-gallery-thumbs       =
  =============================================*/
  var thumbs = new Swiper('.su-testimonial-7-gallery-thumbs', {
    slidesPerView: 5,
    spaceBetween: 80,
    centeredSlides: true,
    loop: true,
    slideToClickedSlide: true,
    breakpoints: {
      0: {
        slidesPerView: 3,
        spaceBetween: 15,
      },
      450: {
        slidesPerView: 3,
        spaceBetween: 15,
      },
      768: {
        slidesPerView: 3,
        spaceBetween: 15,
      },
      868: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
      1400: {
        slidesPerView: 5,
      },
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
  });

  var slider = new Swiper('.su-testimonial-7-gallery-slider', {
    slidesPerView: 1,
    centeredSlides: true,
    loop: true,
    loopedSlides: 5,
  });
  
  slider.controller.control = thumbs;
  thumbs.controller.control = slider;

  /*=============================================
      =        su-brands-4-slide       =
  =============================================*/
  var gallery = new Swiper('.su-banner-8-active', {
    slidesPerView: 1,
    loop: true,
    autoplay: true,
    arrow: false,
    spaceBetween: 0,
    speed: 2000,
    effect: 'fade',
    a11y: false,
    pagination: {
      el: ".su-banner-8-pagination",
      clickable: true,
    },
    autoplay: {
      delay: 3500,
      disableOnInteraction: false
    },
  });


  /*=============================================
    =        su-blog-4-slider      =
  =============================================*/
  var slider = new Swiper('.su-product-8-slider', {
    speed: 700,
    spaceBetween: 4,
    loop: true,
    scrollbar: {
      el: ".swiper-scrollbar",
      hide: false,
    },
    navigation: {
      nextEl: ".product-next",
      prevEl: ".product-prev",
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 4,
      },
      '1200': {
        slidesPerView: 3,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 1,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  var swiper = new Swiper('.home46-shop-look', {
    loop: true,
    speed: 800,
    slidesPerGroup: 1,
    slidesPerView: 3,
    spaceBetween: 20,
    autoplay: {
      delay: 1200,
    },
    breakpoints: {
      // Mobile
      0: {
        slidesPerView: 1
      },
      // Small Tablet
      576: {
        slidesPerView: 1.2,
        spaceBetween: 10,
      },
      // Large Tablet
      576: {
        slidesPerView: 1.4,
        spaceBetween: 10,
      },
      768: {
        slidesPerView: 1.8,
        spaceBetween: 10,
      },
      // Desktop
      992: {
        slidesPerView: 3
      },
      // Large Desktop
      1200: {
        slidesPerView: 3
      }
    }
  });

  var slider = new Swiper('.su-product-8-slider-2', {
    speed: 700,
    spaceBetween: 4,
    loop: true,
    navigation: true,
    scrollbar: {
      el: ".swiper-scrollbar-2",
      hide: false,
    },
    navigation: {
      nextEl: ".product-next",
      prevEl: ".product-prev",
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 4,
      },
      '1200': {
        slidesPerView: 3,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 1,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });


  /*=============================================
    =        su-product-7-slider       =
  =============================================*/
  var headerSlider = new Swiper('.su-testimonal-8-slider', {
    slidesPerView: 1,
    spaceBetween: 24,
    loop: true,
    autoplay: true,
    speed: 600,
    navigation: {
      nextEl: ".testimonal-next",
      prevEl: ".testimonal-prev",
    },
  });

  // su-testimonal-9-slider
  var headerSlider = new Swiper('.su-testimonal-9-slider', {
    slidesPerView: 1,
    spaceBetween: 24,
    loop: true,
    autoplay: true,
    speed: 600,
    navigation: {
      nextEl: ".testimonal-next",
      prevEl: ".testimonal-prev",
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
  });

  /*=============================================
    =        su-banner-15-zoom      =
  =============================================*/
  var slider = new Swiper('.su-banner-15-zoom', {
    slidesPerView: 1,
    speed: 1500,
    spaceBetween: 0,
    loop: true,
    parallax: true,
    autoplay: {
      delay: 3500,
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
  });

  /*=============================================
    =        su-banner-14-zoom      =
  =============================================*/
  var slider = new Swiper('.su-banner-14-zoom', {
    slidesPerView: 1,
    speed: 1500,
    spaceBetween: 0,
    loop: true,
    parallax: true,
    autoplay: {
      delay: 3500,
    },
    pagination: {
      el: ".swiper-pagination-14",
      clickable: true,
    },
  });

  /*=============================================
    =        su-banner-43-zoom      =
  =============================================*/
  var slider = new Swiper('.su-banner-43-zoom', {
    slidesPerView: 1,
    speed: 1500,
    spaceBetween: 0,
    loop: true,
    parallax: true,
    autoplay: {
      delay: 1500,
    },
    pagination: {
      el: ".pagination-home43",
      type: "fraction",
    },
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
  });
  


  /*=============================================
    =        su-testimonial-15      =
  =============================================*/
  var slider = new Swiper('.testimonial-slider-15', {
    slidesPerView: 1,
    speed: 1500,
    spaceBetween: 40,
    loop: true,
    parallax: true,
    autoplay: {
      delay: 3500,
    },
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
  });

  /*=============================================
    =        su-banner-16-zoom      =
  =============================================*/
  var slider = new Swiper('.su-banner-16-zoom', {
    slidesPerView: 1,
    speed: 1500,
    spaceBetween: 0,
    loop: true,
    parallax: true,
    autoplay: {
      delay: 3500,
    },
    navigation: {
      nextEl: ".su-banner-16-next, .swiper-button-next, .next",
      prevEl: ".su-banner-16-prev, .swiper-button-prev, .prev",
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });


  /*=============================================
    =        su-collections-9-slider       =
  =============================================*/
  var slider = new Swiper('.collection-9-slider', {
    speed: 700,
    spaceBetween: 0,
    loop: true,
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 3,
      },
      '1200': {
        slidesPerView: 3,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });


  /*=============================================
    =        su-categories-9-slider       =
  =============================================*/
  var slider = new Swiper('.categories-9-slider', {
    slidesPerView: 1,
    speed: 1500,
    spaceBetween: 0,
    loop: true,
    parallax: true,
    autoplay: {
      delay: 3500,
    },
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
  });


  /*=============================================
    =        products-12-slider       =
  =============================================*/
  var slider = new Swiper('.product-12-slider', {
    speed: 700,
    spaceBetween: 5,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 4,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });


    // Banner Slider Style20 
  var bannerSlider20 = new Swiper('.banner-slider-20', {
    slidesPerView: 1,
    spaceBetween: 24,
    loop: true,
    autoplay: true,
    speed: 600,
    effect: "fade"
  });


  /*=============================================
      =        su-collections-5-slider       =
  =============================================*/
  var slider = new Swiper('.su-collections-4-slider', {
    speed: 700,
    spaceBetween: 4,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    scrollbar: {
      el: ".swiper-scrollbar",
      hide: false,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 5,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });



  /*=============================================
    =        su-collections-4-slider       =
  =============================================*/
  var slider = new Swiper('.four-grid-slider', {
    speed: 700,
    spaceBetween: 4,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    scrollbar: {
      el: ".swiper-scrollbar",
      hide: false,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 4,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  var slider = new Swiper('.four-grid-slider-h43', {
    speed: 700,
    spaceBetween: 20,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    scrollbar: {
      el: ".swiper-scrollbar",
      hide: false,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 4,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  // Sustainability
  var Sustainability = new Swiper('.four-grid-slider', {
    speed: 700,
    spaceBetween: 20,
    slidesPerView: 4,
    loop: true,
    scrollbar: {
      el: ".swiper-scrollbar",
      hide: false,
    },
    autoplay: {
      delay: 4000,
    },
    navigation: {
      nextEl: '.swiper-next',
      prevEl: '.swiper-prev',
    },
    breakpoints: {
      '1400': {
        slidesPerView: 4,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 3,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  // Four Grid Slider
  new Swiper('.four-grid-slider1', {
    speed: 700,
    spaceBetween: 20,
    slidesPerView: 4,
    loop: true,
    autoplay: {
      delay: 4000,
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    breakpoints: {
      1400: { slidesPerView: 4 },
      1200: { slidesPerView: 3 },
      991: { slidesPerView: 2 },
      768: { slidesPerView: 2 },
      576: { slidesPerView: 1 },
      0: { slidesPerView: 1 },
    },
  });

  // Four Grid Slider 2
  new Swiper('.four-grid-slider2', {
    speed: 700,
    spaceBetween: 20,
    slidesPerView: 4,
    loop: true,
    autoplay: {
      delay: 4000,
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    breakpoints: {
      1400: { slidesPerView: 4 },
      1200: { slidesPerView: 4 },
      991: { slidesPerView: 3 },
      768: { slidesPerView: 3 },
      576: { slidesPerView: 2 },
      0: { slidesPerView: 1 },
    },
  });

  /*=============================================
      =        su-collections-4.7-slider       =
  =============================================*/
  var slider = new Swiper('.five-grid-slider', {
    speed: 700,
    spaceBetween: 4,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    scrollbar: {
      el: ".swiper-scrollbar",
      hide: false,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 4.7,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  /*=============================================
      =        Home 43 Collection Slider       =
  =============================================*/
  var slider = new Swiper('.category-slider-home43', {
    speed: 700,
    spaceBetween: 20,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 1500,
    },
    scrollbar: {
      el: ".home43-style2",
      hide: false,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 5,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });


  /*=============================================
    =        su-collections-5-slider       =
  =============================================*/
  var slider = new Swiper('.five-grid-slider2', {
    speed: 700,
    spaceBetween: 4,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    scrollbar: {
      el: ".swiper-scrollbar-2",
      hide: false,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 5,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });
  
  var slider = new Swiper('.five-grid-slider1', {
    speed: 700,
    spaceBetween:20,
    loop: true,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 5,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  var slider = new Swiper('.five-grid-slider3', {
    speed: 700,
    spaceBetween: 4,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    scrollbar: {
      el: ".swiper-scrollbar-3",
      hide: false,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 5,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  // Shop Item
  var fiveSixSlider = new Swiper('.five-six-grid-slider', {
    loop: true,
    slidesPerView: 3,
    spaceBetween: 10,
    centeredSlides: true,
    breakpoints: {
      0: {
        slidesPerView: 1,
      },
      576: {
        slidesPerView: 2,
      },
      768: {
        slidesPerView: 3,
      },
      992: {
        slidesPerView: 3.5,
      },
      1200: {
        slidesPerView: 4.5,
      },
      1400: {
        slidesPerView: 4.5,
      },
    },
    scrollbar: {
      el: ".swiper-scrollbar",
      hide: false,
    },
    navigation: {
      nextEl: ".slider-nav-area .swiper-next",
      prevEl: ".slider-nav-area .swiper-prev",
    },
  });

  var slider = new Swiper('.six-grid-slider', {
    speed: 700,
    spaceBetween: 24,
    slidesPerView: 6,
    loop: false,
    navigation: {
      nextEl: '.swiper-next',
      prevEl: '.swiper-prev',
    },
    breakpoints: {
      0: {   
        slidesPerView: 1
      },
      480: { 
        slidesPerView: 2
      },
      768: { 
        slidesPerView: 3
      },
      992: { 
        slidesPerView: 4
      },
      1200: {
        slidesPerView: 5
      },
      1400: {
        slidesPerView: 6
      }
    }
  });


  /*=============================================
    =        su-collections-3.5-slider       =
  =============================================*/
  var slider = new Swiper('.threefive-grid-slider', {
    speed: 700,
    loop: true,
    spaceBetween: 5,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    scrollbar: {
      el: ".swiper-scrollbar2",
      hide: false,
    },
    breakpoints: {
      '1200': {
        slidesPerView: 3.8,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });


  /*=============================================
    =        cu-banner-4-zoom      =
  =============================================*/
  var slider = new Swiper('.su-product-4-slide', {
    slidesPerView: 1,
    speed: 500,
    spaceBetween: 0,
    loop: true,
    autoplay: {
      delay: 3500,
    },
    navigation: {
      nextEl: ".product-next",
      prevEl: ".product-prev",
    },
  });

  /*=============================================
    =        su-brands-4-slide       =
  =============================================*/
  var slider = new Swiper('.su-brands-4-slide', {
    loop: true,
    freemode: true,
    slidesPerView: 'auto',
    spaceBetween: 162,
    centeredSlides: true,
    allowTouchMove: false,
    speed: 4000,
    autoplay: {
      delay: 1,
      disableOnInteraction: true,
    },
    breakpoints: {
      '992': {
        spaceBetween: 90,
      },
      '768': {
        spaceBetween: 90,
      },
      '576': {
        spaceBetween: 90,
      },
      '0': {
        spaceBetween: 50,
      },
    }
  });

  /*=============================================
      =        su-brands-5-slide       =
  =============================================*/
  var slider = new Swiper('.su-brands-5-slide', {
    loop: true,
    freemode: true,
    slidesPerView: 'auto',
    spaceBetween: 162,
    centeredSlides: true,
    allowTouchMove: false,
    speed: 6000,
    autoplay: {
      delay: 1,
      disableOnInteraction: true,
    },
    breakpoints: {
      '992': {
        spaceBetween: 90,
      },
      '768': {
        spaceBetween: 90,
      },
      '576': {
        spaceBetween: 90,
      },
      '0': {
        spaceBetween: 50,
      },
    }
  });


  /*=============================================
      =   Three Grid Slider (Top Collection)  =
  =============================================*/
  var slider = new Swiper('.three-grid-slider', {
    speed: 700,
    slidesPerView: 'auto',
    spaceBetween: 5,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1200': {
        slidesPerView: 3,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  // home39-deals-slider
  var home39 = new Swiper(".home39-deal-slider", {
    slidesPerView: 3,
    spaceBetween: 20,
    loop: true,
    slidesPerGroup: 1,
    autoplay: {
      delay: 0,       // No waiting
      disableOnInteraction: false,
    },
    speed: 3000,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    breakpoints: {
      0: { slidesPerView: 1 },
      576: { slidesPerView: 1 },
      1024: { slidesPerView: 2 },
      1400: { slidesPerView: 3 }
    }
  });



  /*=============================================
      =        card-prd-slider Slider     =
  =============================================*/
  var slider = new Swiper('.card-prd-slider', {
    speed: 700,
    slidesPerView: 'auto',
    spaceBetween: 5,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 4,
      },
      '768': {
        slidesPerView: 4,
      },
      '576': {
        slidesPerView: 4,
      },
      '0': {
        slidesPerView: 3,
      },
    },
  });


  /*=============================================
      =        Seven Grid Slider (Instagram)       =
  =============================================*/
  var slider = new Swiper('.seven-grid-slider', {
    speed: 700,
    spaceBetween: 5,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 7,
      },
      '1200': {
        slidesPerView: 4,
      },
      '991': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 2,
      },
    },
  });


  /*=============================================
    =        Shop look product Slider    =
  =============================================*/
  var slider = new Swiper('.shop-look-14-slider', {
    speed: 700,
    spaceBetween: 0,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 1,
      },
    },
  });
  /*=============================================
    =        Shop look product Slider    =
  =============================================*/
  var slider = new Swiper('.shop-look-12-slider', {
    speed: 700,
    spaceBetween: 0,
    loop: true,
    spaceBetween: 20,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1200': {
        slidesPerView: 2,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });


  /*=============================================
    =        su-brands-16-text-slide       =
  =============================================*/
  var textslider = new Swiper('.brands-16-text-slide', {
    slidesPerView: 1,
    centeredSlides: true,
    loop: true,
    loopedSlides: 6,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
  });

  /*=============================================
    =        su-brands-16-img-slide       =
  =============================================*/
  var imgslider = new Swiper('.brands-16-img-slide', {
    slidesPerView: 'auto',
    spaceBetween: 10,
    centeredSlides: true,
    loop: true,
    slideToClickedSlide: true,
    breakpoints: {
      '992': {
        spaceBetween: 90,
      },
      '768': {
        spaceBetween: 90,
      },
      '576': {
        spaceBetween: 90,
      },
      '0': {
        spaceBetween: 50,
      },
    }
  });

  textslider.controller.control = imgslider;
  imgslider.controller.control = textslider;


  

  /*=============================================
      =        su-product-5-slide      =
  =============================================*/
  var slider = new Swiper('.su-product-5-slide', {
    slidesPerView: 1,
    speed:700,
    spaceBetween: 30,
    loop: true,
    autoplay: {
      delay: 4000,
    },
    pagination: {
      el: ".su-product-5-pagination",
      clickable: true,
    },
    breakpoints: {
      '1600': {
        slidesPerView: 3,
      },
      '1200': {
        slidesPerView: 3,
      },
      '768': {
        slidesPerView: 2,
      },
      '576': {
        slidesPerView: 1,
      },
      '0': {
        slidesPerView: 1,
      },
    },
  });

  /*=============================================
    =       su-products-2-slider      =
  =============================================*/
  var slider = new Swiper('.prd-slider-2', {
    speed: 700,
    spaceBetween: 4,
    loop: true,
    slidesPerView: 1,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    scrollbar: {
      el: ".swiper-scrollbar1",
      hide: false,
    },
    autoplay: {
      delay: 4000,
    },
  });

  /*=============================================
    =       su-products-2-slider      =
  =============================================*/
  var slider = new Swiper('.prd-slider-3', {
    speed: 700,
    spaceBetween: 4,
    loop: true,
    slidesPerView: 1,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    scrollbar: {
      el: ".swiper-scrollbar2",
      hide: false,
    },
    autoplay: {
      delay: 4000,
    },
  });

  /*=============================================
    =       su-collections-11-slider      =
  =============================================*/
  var slider = new Swiper('.collections-11-slider', {
    speed: 700,
    spaceBetween: 4,
    loop: true,
    navigation: {
      nextEl: ".next",
      prevEl: ".prev",
    },
    scrollbar: {
      el: ".swiper-scrollbar1",
      hide: false,
    },
    autoplay: {
      delay: 4000,
    },
    breakpoints: {
      '1400': {
        slidesPerView: 5,
      },
      '1200': {
        slidesPerView: 5,
      },
      '991': {
        slidesPerView: 5,
      },
      '768': {
        slidesPerView: 3,
      },
      '576': {
        slidesPerView: 3,
      },
      '0': {
        slidesPerView: 2,
      },
    },
  });

  // Eight Grid Slider
  $(function () {
    var swiper = new Swiper(".eight-grid-slider", {
      slidesPerView: 8,
      spaceBetween: 20,
      loop: true,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      breakpoints: {
        1400: {
          slidesPerView: 8
        },
        1200: {
          slidesPerView: 6
        },
        992: {
          slidesPerView: 5
        },
        768: {
          slidesPerView: 3
        },
        576: {
          slidesPerView: 2
        },
        320: {
          slidesPerView: 2
        }
      }
    });
  });

  // Shop Item
  var faveBrandSlider20 = new Swiper('.four-five-grid-slider', {
    loop: true,
    slidesPerView: 3,
    spaceBetween: 10,
    breakpoints: {
      0: {
        slidesPerView: 1,
        spaceBetween: 13,
      },
      576: {
        slidesPerView: 2,
        spaceBetween: 20,
      },
      768: {
        slidesPerView: 3,
        spaceBetween: 20,
      },
      992: {
        slidesPerView: 3.5,
        spaceBetween: 20,
      },
      1200: {
        slidesPerView: 4.5,
        spaceBetween: 30,
      },
    },
    navigation: {
      nextEl: ".slider-nav-area .swiper-next",
      prevEl: ".slider-nav-area .swiper-prev",
    },

  });

  // Shop Single 4 Swiper Slider Thumb Style  
  var ss4swiperthumbs = new Swiper(".shop-single4-pagi-view", {
    slidesPerView: 6,
    centeredSlides: true,
    freeMode: false,
    watchSlidesVisibility: true,
    watchSlidesProgress: true,
    breakpoints: {
      576: {
        autoplay:true,
        slidesPerView: 2,
        centeredSlides: false,
      },
      768: {
        slidesPerView: 4,
      },
      1024: {
        slidesPerView: 5,
      },
    },
  });
  var swiperTops = new Swiper(".shop-single4-lg-view", {
    slidesPerView: 1,
    spaceBetween: 10,
    loop:true,
    navigation: {
      nextEl: '.next',
      prevEl: '.prev',
    },
    thumbs: {
      swiper: ss4swiperthumbs,
    },
  });

  ////////////////////////////////////////////////////
  // 10. BeforeAfter Js
  if ($(".beforeAfter").length > 0) {
    $('.beforeAfter').beforeAfter({
      movable: true,
      clickMove: true,
      position: 50,
      separatorColor: '#fafafa',
      bulletColor: '#fafafa',
      onMoveStart: function (e) {
        console.log(event.target);
      },
      onMoving: function () {
        console.log(event.target);
      },
      onMoveEnd: function () {
        console.log(event.target);
      },
    });
  }

  /*-----------------------------------
  07. Set Background Image Color & Mask   
  -----------------------------------*/
  if ($("[data-bg-src]").length > 0) {
    $("[data-bg-src]").each(function () {
      var src = $(this).attr("data-bg-src");
      $(this).css("background-image", "url(" + src + ")");
      $(this).removeAttr("data-bg-src").addClass("background-image");
    });
  }

  /*-----------------------------------
   17. Before After Slider   
  -----------------------------------*/
  $("#slider").on("input change", (e) => {
    const a = e.target.value;
    $(".foreground-img").css("width", a + "%");
    $(".slider-button").css("left", `calc(${a}% - 36px)`);
  });


  /*=============================================
      =        jarallax Js       =
  =============================================*/
  if ($('.jarallax').length > 0) {
    $('.jarallax').jarallax({
      speed: 0.2,
      imgWidth: 1200,
      imgHeight: 520,
    });
  };

  /*=============================================
      =        color swatch product    =
  =============================================*/
  var swatchColor = function () {
    if ($(".card-product").length > 0) {
      $(".color-swatch").on("click, mouseover", function () {
        var swatchColor = $(this).find("img").attr("src");
        var imgProduct = $(this).closest(".card-product").find(".img-product");
        imgProduct.attr("src", swatchColor);
        $(this)
        .closest(".card-product")
        .find(".color-swatch.active")
        .removeClass("active");

        $(this).addClass("active");
      });
    }
  };
    // Dom Ready
  $(function () {
    swatchColor();
    hoverPin();
  });

  /*=============================================
      =        mouseenter events     =
  =============================================*/

  $(document).on('click, mouseenter', '.size-list span', function(){
    $(this).siblings().removeClass('active');
    $(this).addClass('active');
  })

  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })

  /*=============================================
    =        countdown     =
  =============================================*/
  function makeTimer() {
    //  var endTime = new Date("20 Dec 2021 9:56:00 GMT+01:00");  
    var endTime = new Date("20 Jun 2026 9:56:00 GMT+01:00");      
    endTime = (Date.parse(endTime) / 1000);
    var now = new Date();
    now = (Date.parse(now) / 1000);
    var timeLeft = endTime - now;
    var days = Math.floor(timeLeft / 86400); 
    var hours = Math.floor((timeLeft - (days * 86400)) / 3600);
    var minutes = Math.floor((timeLeft - (days * 86400) - (hours * 3600 )) / 60);
    var seconds = Math.floor((timeLeft - (days * 86400) - (hours * 3600) - (minutes * 60)));  
    if (hours < "10") { hours = "0" + hours; }
    if (minutes < "10") { minutes = "0" + minutes; }
    if (seconds < "10") { seconds = "0" + seconds; }
    $(".days").html(days + "<span>Days</span>");
    $(".hours").html(hours + "<span>Hours</span>");
    $(".minutes").html(minutes + "<span>Minutes</span>");
    $(".seconds").html(seconds + "<span>Seconds</span>");
  }
  setInterval(function() { makeTimer(); }, 1000);


    /* ----- Swiper Slider ----- */

  var swiper = new Swiper(".one-item-slider", {
    pagination: {
      el: ".swiper-pagination",
      type: "progressbar",
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
  });

  // Video play on hover js
  document.addEventListener("DOMContentLoaded", function () {
    const hoverVideoElements = document.querySelectorAll(".hover-video");

    hoverVideoElements.forEach(element => {
      let video;

      if (element.tagName.toLowerCase() === "video") {
        video = element;
      } else {
        video = element.querySelector("video");
      }

      if (video) {
        element.addEventListener("mouseover", function () {
          video.play();
        });

        element.addEventListener("mouseout", function () {
          video.pause();
        });
      }
    });
  });
  
  // Counter Down js 
  document.addEventListener('DOMContentLoaded', function () {
    // Countdown script here
    var countDownDate = new Date("Jun 5, 2026 15:37:25").getTime();

    var x = setInterval(function () {
      var presentTime = new Date().getTime();
      var distance = countDownDate - presentTime;

      var days = Math.floor(distance / (1000 * 60 * 60 * 24));
      var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);

      // Update the countdown display
      if (document.getElementById("day")) document.getElementById("day").innerHTML = days;
      if (document.getElementById("hrs")) document.getElementById("hrs").innerHTML = hours;
      if (document.getElementById("min")) document.getElementById("min").innerHTML = minutes;
      if (document.getElementById("sec")) document.getElementById("sec").innerHTML = seconds;

      // Handle countdown expiry
      if (distance < 0) {
        clearInterval(x);
        if (document.getElementById("day")) document.getElementById("day").innerHTML = "EXPIRED";
        if (document.getElementById("hrs")) document.getElementById("hrs").innerHTML = "EXPIRED";
        if (document.getElementById("min")) document.getElementById("min").innerHTML = "EXPIRED";
        if (document.getElementById("sec")) document.getElementById("sec").innerHTML = "EXPIRED";
      }
    }, 1000);
  });


    /* ----- MagnificPopup ----- */
  if (($(".popup-img").length > 0) || ($(".popup-video").length > 0) || ($(".popup-iframe").length > 0) || ($(".popup-img-single").length > 0)) {
    $(".popup-img").magnificPopup({
      type:"image",
      gallery: {
        enabled: true,
      }
    });
    $(".popup-img-single").magnificPopup({
      type:"image",
      gallery: {
        enabled: false,
      }
    });
    $('.popup-iframe').magnificPopup({
      disableOn: 700,
      type: 'iframe',
      preloader: false,
      fixedContentPos: false
    });
    $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
      disableOn: 700,
      type: 'iframe',
      mainClass: 'mfp-fade',
      removalDelay: 160,
      preloader: false,
      fixedContentPos: false
    });

    $('.popup-image').magnificPopup({
      type: 'image',
      gallery: {
        enabled: true
      }
    });

        /* magnificPopup video view */
    $('.popup-video').magnificPopup({
      type: 'iframe'
    });
  };

    // Search Right Hidden Sidebar 
  if ($('.search-filter-btn').length) {
        //Show Form
    $('.search-filter-btn').on('click', function (e) {
      e.preventDefault();
      $('body').addClass('search-hidden-sidebar-content');
    });
        //Hide Form
    $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function (e) {
      e.preventDefault();
      $('body').removeClass('search-hidden-sidebar-content');
    });
  }

    /*** ====  Right Side Hidden Sidebar Start ==== ***/
    //Side Content Toggle
  if($('.signin-filter-btn').length){
      //Show Form
    $('.signin-filter-btn').on('click', function(e) {
      e.preventDefault();
      $('body').addClass('signin-hidden-sidebar-content');
    });
      //Hide Form
    $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
      e.preventDefault();
      $('body').removeClass('signin-hidden-sidebar-content');
    });
  } 

  if($('.signup-filter-btn').length){
      //Show Form
    $('.signup-filter-btn').on('click', function(e) {
      e.preventDefault();
      $('body').addClass('singup-hidden-sidebar-content');
    });
      //Hide Form
    $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
      e.preventDefault();
      $('body').removeClass('singup-hidden-sidebar-content');
    });
  }



  if ($('.cart-filter-btn').length) {
      //Show Form
    $('.cart-filter-btn').on('click', function (e) {
      $('.minicart-16').addClass('show');
    });
      //Hide Form
    $('.minicart-close-icon,.minicart-16-overlay').on('click', function (e) {
      e.preventDefault();
      $('.minicart-16').removeClass('show');
    });
  }

  $(function () {
    // Always bind — search buttons may be injected later (account dashboard header)
    $(document).on('click', '.cart-search-btn', function (e) {
      e.preventDefault();
      $('.search-16-wrap').addClass('show');
    });

    $(document).on('click', '.search-close-icon, .open-search-16-overlay', function (e) {
      e.preventDefault();
      $('.search-16-wrap').removeClass('show');
    });
  });

  if ($('.signin-cart-btn').length) {
      // Show login modal only for guests — logged-in users go to account dashboard
    $(document).on('click', '.signin-cart-btn', function (e) {
      if (window.PP_SITE && window.PP_SITE.authenticated) {
        e.preventDefault();
        window.location.href = window.PP_SITE.accountOverview || '/account/overview';
        return;
      }
      // Prefer legacy drawer when present; otherwise site-drawers.js / href handles login
      if ($('.signin-16-wrap').length) {
        e.preventDefault();
        $('.signin-16-wrap').addClass('show');
      }
    });
      //Hide Form
    $(document).on('click', '.singin-close-icon', function (e) {
      e.preventDefault();
      $('.signin-16-wrap').removeClass('show');
    });
  }

    // prouduct sidebar info js
  $(".prd-side-btn").on("click", function () {
    $(".prd-sidebar-overlay,.prd-sidebar-info").addClass("active")
  })
  $(".close-prd-sidebar,.prd-sidebar-overlay").on("click", function () {
    $(".prd-sidebar-overlay,.prd-sidebar-info").removeClass("active")
  })
  $(".cloth-size-btn").on("click", function () {
    $(".cloth-sidebar-overlay,.cloth-size-sidebar").addClass("active")
  })
  $(".close-cloth-sidebar,.cloth-sidebar-overlay").on("click", function () {
    $(".cloth-sidebar-overlay,.cloth-size-sidebar").removeClass("active")
  })

  if($('.cart-filter-btn').length){
      //Show Form
    $('.cart-filter-btn').on('click', function(e) {
      e.preventDefault();
      $('body').addClass('cart-dropdown');
    });
      //Hide Form
    $('.sidebar-close-icon').on('click', function(e) {
      e.preventDefault();
      $('body').removeClass('cart-dropdown');
    });
  }


  if ($('.spece-filter-btn').length) {
    $('.spece-filter-btn, .hiddenbar-body-ovelay').on('click', function (e) {
      e.preventDefault();
      $('body').toggleClass('spece-filter-hidden-sidebar-content');
    });
  }

    //Accordion Box
  if ($('.shop-sidebar').length) {
        $('.filter-box').show(); // initially hidden

        $(".shop-sidebar").on('click', '.filter-button', function () {
          $(this).toggleClass('collapsed');
          let box = $(this).next('.filter-box');    
          if (box.is(':visible')) {
            box.animate({ height: 'toggle', opacity: 'toggle' }, 700);
          } else {
            box.animate({ height: 'toggle', opacity: 'toggle' }, 700);
          }
        });
      }

    //Price Range Slider
      if ($('.price-range-slider').length) {
        $(".price-range-slider").slider({
          range: true,
          min: 10,
          max: 99,
          values: [10, 60],
          slide: function(event, ui) {
                $("input.property-min").val(ui.values[0]); // Min Value
                $("input.property-max").val(ui.values[1]); // Max Value
              }
            });
        // Set initial values
        $("input.property-min").val($(".price-range-slider").slider("values", 0));
        $("input.property-max").val($(".price-range-slider").slider("values", 1));
      }
    //Price Range Slider

      if($('.cart-filter-btn').length){
      //Show Form
        $('.cart-filter-btn').on('click', function(e) {
          e.preventDefault();
          $('body').addClass('cart-hidden-sidebar-content');
        });
      //Hide Form
        $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
          e.preventDefault();
          $('body').removeClass('cart-hidden-sidebar-content');
        });
      }

      if($('.descrip-filter-btn').length){
      //Show Form
        $('.descrip-filter-btn').on('click', function(e) {
          e.preventDefault();
          $('body').addClass('descrip-hidden-sidebar-content');
        });
      //Hide Form
        $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
          e.preventDefault();
          $('body').removeClass('descrip-hidden-sidebar-content');
        });
      }

      if($('.spece-filter-btn').length){
      //Show Form
        $('.spece-filter-btn').on('click', function(e) {
          e.preventDefault();
          $('body').addClass('spcfictn-hidden-sidebar-content');
        });
      //Hide Form
        $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
          e.preventDefault();
          $('body').removeClass('spcfictn-hidden-sidebar-content');
        });
      } 

      if($('.repc-filter-btn').length){
      //Show Form
        $('.repc-filter-btn').on('click', function(e) {
          e.preventDefault();
          $('body').addClass('retrnplc-hidden-sidebar-content');
        });
      //Hide Form
        $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
          e.preventDefault();
          $('body').removeClass('retrnplc-hidden-sidebar-content');
        });
      }

      if($('.comqstn-filter-btn').length){
      //Show Form
        $('.comqstn-filter-btn').on('click', function(e) {
          e.preventDefault();
          $('body').addClass('faq-hidden-sidebar-content');
        });
      //Hide Form
        $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
          e.preventDefault();
          $('body').removeClass('faq-hidden-sidebar-content');
        });
      }

      if($('.review-filter-btn, .department-filter-btn').length){
      //Show Form
        $('.review-filter-btn, .department-filter-btn').on('click', function(e) {
          e.preventDefault();
          $('body').addClass('review-hidden-sidebar-content');
        });
      //Hide Form
        $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
          e.preventDefault();
          $('body').removeClass('review-hidden-sidebar-content');
        });
      }

      if($('.menu-filter-btn, .department-filter-btn').length){
      //Show Form
        $('.menu-filter-btn, .department-filter-btn').on('click', function(e) {
          e.preventDefault();
          $('body').addClass('menu-hidden-sidebar-content');
        });
      //Hide Form
        $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
          e.preventDefault();
          $('body').removeClass('menu-hidden-sidebar-content');
        });
      }

      if($('.department-filter-btn').length){
      //Show Form
        $('.department-filter-btn').on('click', function(e) {
          e.preventDefault();
          $('body').addClass('department-hidden-sidebar-content');
        });
      //Hide Form
        $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
          e.preventDefault();
          $('body').removeClass('department-hidden-sidebar-content');
        });
      }

      if($('.all-filter-btn').length){
      //Show Form
        $('.all-filter-btn').on('click', function(e) {
          e.preventDefault();
          $('body').addClass('allfilter-hidden-sidebar-content');
        });
      //Hide Form
        $('.sidebar-close-icon,.hiddenbar-body-ovelay').on('click', function(e) {
          e.preventDefault();
          $('body').removeClass('allfilter-hidden-sidebar-content');
        });
      }
    /*** ====  Right Side Hidden Sidebar END ==== ***/

    // Custom Search Dropdown Script Start
      var showSuggestions = function () {
        $(".top-search form.form-search .box-search").each(function () {
          $("form.form-search .box-search input").on('focus', (function () {
            $(this).closest('.boxed').children('.overlay').css({
              opacity: '1',
              display: 'block'
            });
            $(this).parent('.box-search').children('.search-suggestions').css({
              opacity: '1',
              visibility: 'visible',
              top: '50px'
            });
          }));
          $("form.form-search .box-search input").on('blur', (function () {
            $(this).closest('.boxed').children('.overlay').css({
              opacity: '0',
              display: 'block'
            });
            $(this).parent('.box-search').children('.search-suggestions').css({
              opacity: '0',
              visibility: 'hidden',
              top: '100px'
            });
          }));
        });

        $(".top-search.style1 form.form-search .box-search").each(function () {
          $("form.form-search .box-search input").on('focus', (function () {
            $(this).closest('.boxed').children('.overlay').css({
              opacity: '1',
              display: 'block'
            });
            $(this).parent('.box-search').children('.search-suggestions').css({
              opacity: '1',
              visibility: 'visible',
              top: '100px'
            });
          }));
        });
    }; // Toggle Location
    $(function () {
      showSuggestions();
    });
    // Custom Search Dropdown Script Start


    // Custom Shop item add Option increase decrease home 3
    $(function() {
      // Scope to the clicked row only — never update every .quantity-num on the page
      $(document).on('click', '.quantity-arrow-minus, .quantity-arrow-plus, .quantity-arrow-minus2, .quantity-arrow-plus2', function (e) {
        var $btn = $(this);
        // Real cart rows are handled by cart.js
        if ($btn.closest('[data-cart-row], .pp-cart-section, .pp-minicart-live').length) {
          return;
        }
        e.preventDefault();
        var $block = $btn.closest('.quantity-block');
        var $input = $block.find('.quantity-num, .quantity-num2').first();
        if (!$input.length) return;
        var currentValue = parseInt($input.val(), 10) || 1;
        if ($btn.is('.quantity-arrow-plus, .quantity-arrow-plus2')) {
          $input.val(currentValue + 1);
        } else if (currentValue > 1) {
          $input.val(currentValue - 1);
        }
      });
    });

    // review open hide js
    $(function() { 
      // Event delegation for dynamically loaded elements (if any)
      $(document).on('click', '.review-details-btn', function() {
        var $btn = $(this);
        var $info = $btn.next('.review-details-info');
        var isVisible = $info.is(':visible');
        
        // Close all other open sections first
        $('.review-details-info').not($info).slideUp();
        $('.review-details-btn').not($btn)
        .html('More Detail <span class="icon ms-2"><i class="fa-solid fa-chevron-down"></i></span>');
        
        // Toggle current section
        $info.stop().slideToggle(function() {
          $btn.html(
            $(this).is(':visible') 
            ? 'Less Detail <span class="icon ms-2"><i class="fa-solid fa-chevron-up"></i></span>'
            : 'More Detail <span class="icon ms-2"><i class="fa-solid fa-chevron-down"></i></span>'
            );
        });
      });
    });

    //Event Countdown Timer
    if($('.time-countdown').length){  
      $('.time-countdown').each(function() {
        var $this = $(this), finalDate = $(this).data('countdown');
        $this.countdown(finalDate, function(event) {
          var $this = $(this).html(event.strftime('' + '<div class="counter-column"><span class="count">%D</span><sub>Days</sub></div> ' + '<div class="counter-column"><span class="count">%H</span><sub>Hours</sub></div>  ' + '<div class="counter-column"><span class="count">%M</span><sub>Minutes</sub></div>  ' + '<div class="counter-column"><span class="count">%S</span><sub>Seconds</sub></div>'));
        });
      });
    }

  // review open hide js
    $(document).on("ready",function () {
      $(".review-details-btn").on("click",function () {
        var $btn = $(this);
        var $info = $btn.next(".review-details-info");

      // Close all other details and reset buttons
        $(".review-details-info").not($info).slideUp();
        $(".review-details-btn").not($btn).html('More Detail <span class="icon ms-2"><i class="fa-solid fa-chevron-down"></i></span>');

      // Toggle current section
        if ($info.is(":visible")) {
          $info.slideUp();
          $btn.html('More Detail <span class="icon ms-2"><i class="fa-solid fa-chevron-down"></i></span>');
        } else {
          $info.slideDown();
          $btn.html('Less Detail <span class="icon ms-2"><i class="fa-solid fa-chevron-up"></i></span>');
        }
      });
    });


  /* ----- Scroll To top ----- */
    function scrollToTop() {
      var btn = $('.scrollToHome');
      $(window).on('scroll', function () {
        if ($(window).scrollTop() > 300) {
          btn.addClass('show');
        } else {
          btn.removeClass('show');
        }
      });
      btn.on('click', function (e) {
        e.preventDefault();
        $('html, body').animate({
          scrollTop: 0
        }, '300');
      });
    }

    function productDesignInteractions() {
      var root = $('.product-design');
      if (!root.length) return;
      var sizeGuideNodes = $('.product-design .cloth-sidebar-overlay, .product-design .cloth-size-sidebar');
      if (!sizeGuideNodes.length) {
        sizeGuideNodes = $('.legacy-product-section .cloth-sidebar-overlay, .legacy-product-section .cloth-size-sidebar');
      }
      sizeGuideNodes.appendTo('body');
      $('body > .cloth-sidebar-overlay, body > .cloth-size-sidebar').removeClass('active');
      $('body').removeClass('header-active').addClass('product-size-closed');

      function selectedStockLimit() {
        var limits = [Number(root.data('max-unit-buy')) || 99];
        var selectedColour = root.find('[data-colour].is-active');
        var selectedSize = root.find('[data-size].is-active');
        if (selectedColour.length) limits.push(Number(selectedColour.data('stock')) || 0);
        if (selectedSize.length) limits.push(Number(selectedSize.data('stock')) || 0);
        return Math.min.apply(Math, limits);
      }

      function applyStockLimit() {
        var max = selectedStockLimit();
        var value = root.find('[data-product-qty-value]');
        var amount = Number(value.text()) || 1;
        value.text(max > 0 ? Math.min(amount, max) : 0);
        root.find('[data-product-qty="plus"]').prop('disabled', max < 1 || amount >= max);
        root.find('[data-add-to-cart], [data-buy-now]').toggleClass('is-disabled', max < 1).attr('aria-disabled', max < 1 ? 'true' : 'false');
      }
      document.addEventListener('click', function (event) {
        if (event.target.closest('.close-cloth-sidebar') || event.target.closest('body > .cloth-sidebar-overlay')) {
          window.setTimeout(function () {
            document.body.classList.add('product-size-closed');
            document.body.classList.remove('header-active');
          }, 0);
        }
      }, true);
      root.on('click', '.cloth-size-btn', function () {
        $('body').removeClass('product-size-closed');
        $('body > .cloth-sidebar-overlay, body > .cloth-size-sidebar').addClass('active');
        $('body').addClass('header-active');
      });
      $(document).off('click.productDesignSizeGuide').on('click.productDesignSizeGuide', 'body > .cloth-sidebar-overlay, body > .cloth-size-sidebar .close-cloth-sidebar', function () {
        window.setTimeout(function () {
          $('body > .cloth-sidebar-overlay, body > .cloth-size-sidebar').removeClass('active');
          $('body').removeClass('header-active').addClass('product-size-closed');
        }, 0);
      });
      $('body > .cloth-sidebar-overlay, body > .cloth-size-sidebar .close-cloth-sidebar').off('click.productDesignClose').on('click.productDesignClose', function () {
        window.setTimeout(function () {
          $('body > .cloth-sidebar-overlay, body > .cloth-size-sidebar').removeClass('active');
          $('body').removeClass('header-active').addClass('product-size-closed');
        }, 0);
      });
      root.on('click', '[data-size]', function () {
        root.find('[data-size]').removeClass('is-active').attr('aria-pressed', 'false');
        $(this).addClass('is-active').attr('aria-pressed', 'true');
        root.find('[data-selected-size]').text($(this).data('size'));
        applyStockLimit();
      });
      var colourGalleries = {};
      var galleriesJson = root.find('[data-colour-galleries]').text();
      if (galleriesJson) {
        try {
          colourGalleries = JSON.parse(galleriesJson);
        } catch (error) {
          colourGalleries = {};
        }
      }
      function showColourGallery(colour) {
        var images = colourGalleries[String(colour || '').toUpperCase()];
        if (!Array.isArray(images) || !images.length) return;
        var gallery = root.find('.product-design__gallery');
        gallery.empty();
        while (images.length < 4) images.push(images[0]);
        images.slice(0, 8).forEach(function (imageUrl, index) {
          var button = $('<button>', {
            type: 'button',
            'class': 'product-design__image' + (index < 2 ? ' product-design__image--half' : '') + (index === 0 ? ' is-active' : '')
          }).attr('data-product-image', imageUrl);
          button.append($('<img>', {
            src: imageUrl,
            alt: root.find('#product-design-title').text() || 'Product image'
          }));
          gallery.append(button);
        });
      }
      root.off('click.productDesignColour', '[data-colour]').on('click.productDesignColour', '[data-colour]', function () {
        root.find('[data-colour]').removeClass('is-active').attr('aria-pressed', 'false');
        $(this).addClass('is-active').attr('aria-pressed', 'true');
        var colour = $(this).data('colour');
        root.find('[data-selected-colour]').text(colour);
        showColourGallery(colour);
        applyStockLimit();
      });
      root.off('click.productDesignPackage', '[data-accessory-package]').on('click.productDesignPackage', '[data-accessory-package]', function () {
        root.find('[data-accessory-package]').removeClass('is-active').attr('aria-pressed', 'false');
        $(this).addClass('is-active').attr('aria-pressed', 'true');
        var packageKey = $(this).attr('data-accessory-package');
        var packageLabel = $(this).attr('data-package-label');
        var packageMrp = Number($(this).attr('data-package-mrp'));
        var packagePrice = Number($(this).attr('data-package-price'));
        var packageDiscount = packageMrp > packagePrice && packageMrp > 0 ? Math.round(((packageMrp - packagePrice) / packageMrp) * 100) : 0;
        root.find('[data-selected-package]').text(packageLabel);
        root.find('[data-product-price]').html('<span class="pp-price pp-price--has-selling-price"><span class="pp-price__mrp">' + (packageDiscount ? '<s>₹ ' + packageMrp.toFixed(2) + '</s>' : '₹ ' + packageMrp.toFixed(2)) + '</span>' + (packageDiscount ? '<span class="pp-price__discount">-' + packageDiscount + '%</span>' : '') + '<span class="pp-price__selling">₹ ' + packagePrice.toFixed(2) + '</span></span>');
        root.find('[data-add-to-cart], .product-design__cart, [data-buy-now], .product-design__buy').attr('data-default-package', packageKey);
      });
      root.on('click', '[data-product-qty]', function () {
        var value = root.find('[data-product-qty-value]');
        var max = selectedStockLimit();
        var amount = Number(value.text()) + ($(this).data('product-qty') === 'plus' ? 1 : -1);
        value.text(Math.max(max > 0 ? 1 : 0, Math.min(max, amount)));
        applyStockLimit();
      });
      applyStockLimit();
      root.on('click', '.product-design__wish', function () {
        $(this).toggleClass('is-active').attr('aria-pressed', $(this).hasClass('is-active'));
      });
      root.on('click', '.product-design__cart', function (e) {
        // Real cart handled by cart.js — keep qty UI only here
        if (typeof window.PPCart !== 'undefined') {
          return;
        }
        e.preventDefault();
        $(this).text('ADDED TO CART').addClass('is-added');
      });
      root.on('click', '.product-design__image', function () {
        root.find('.product-design__image').removeClass('is-active');
        $(this).addClass('is-active');
      });
    }

    $(productDesignInteractions);

    $(document).on('click', '.product-recommendation-card__wish', function () {
      var button = $(this);
      var active = !button.hasClass('is-active');
      button.toggleClass('is-active', active).attr('aria-pressed', active);
      button.text(active ? '♥' : '♡');
    });
    

/* ======
   When document is ready, do
   ====== */
    $(document).on('ready', function() {
      // add your functions
      navbarScrollfixed();
      scrollToTop();
      mobileNavToggle();
      productDesignInteractions();
    });
    
/* ======
   When document is loading, do
   ====== */
    // window on Load function
    $(window).on('load', function() {
      // add your functions
      preloaderLoad();
    });
    // window on Scroll function
    $(window).on('scroll', function() {
      // add your functions
    });


  })(window.jQuery);
