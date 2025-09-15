<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Erda Illumine | @yield('title', $page_title ?? '')</title>

	<meta name="description" content="@yield('page_description', $page_description ?? '')"/>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.ico')}}">



	@if(!empty(config('dz.public.pagelevel.css.'.$action)))
		@foreach(config('dz.public.pagelevel.css.'.$action) as $style)
				<link href="{{ asset($style) }}" rel="stylesheet" type="text/css"/>
		@endforeach
	@endif

	{{-- Global Theme Styles (used by all pages) --}}
	@if(!empty(config('dz.public.global.css')))
		@foreach(config('dz.public.global.css') as $style)
			<link href="{{ asset($style) }}?t={{time()}}" rel="stylesheet" type="text/css"/>
		@endforeach
	@endif
    <style>
        @media (max-width: 1200px) {
                .small-logo {
                  display: none;
                }
        }
    </style>
    @yield('styles')
</head>

<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="{!! url('admin/dashboard'); !!}" class="brand-logo">
                <img class="logo-abbr biglogo d-md-block" style="max-width: 182px;" src="{{ asset('images/erda-logo.svg') }}" alt="logo">
                <!-- <img class="small-logo" src="{{ asset('images/smalllogo.jpeg') }}" alt=""> -->
			{{-- @if(!empty($logo))
				<img class="logo-abbr" src="{{ asset($logo) }}" alt="">
			@else
                <img class="logo-abbr" src="{{ asset('images/logo.png') }}" alt="">
			@endif
			@if(!empty($logoText))
                <!-- <img class="logo-compact" src="{{ asset($logoText) }}" alt=""> -->
                <!-- <img class="brand-title" src="{{ asset($logoText) }}" alt=""> -->
			@else
                <!-- <img class="logo-compact" src="{{ asset('images/logo-text.png') }}" alt=""> -->
                <!-- <img class="brand-title" src="{{ asset('images/logo-text.png') }}" alt=""> -->
			@endif	 --}}
            </a>
          	{{-- <a href="{!! url('/dashboard'); !!}" class="brand-logo">
          	    <!--new-->
                @if(!empty($logo))
    				<img class="logo-abbr" src="{{ asset($logo) }}" alt="">
    			@else
                    <!--<img class="logo-abbr" src="{{ asset('images/logo.png') }}" alt="else">--> 
                    <!--<img class="logo-abbr" src="{{ asset('images/c_quest_logo_icon.png') }}" alt="">-->
                    
    			@endif
    			@if(!empty($logoText))
                    <!--<img class="logo-compact" src="{{ asset($logoText) }}" alt="1">-->
                    <!--<img class="brand-title" src="{{ asset($logoText) }}" alt="2">-->
    			@else
                    <!--<img class="logo-compact" src="{{ asset('images/logo-text.png') }}" alt="3">-->
                    <!--<img class="brand-title" src="{{ asset('images/logo-text.png') }}" alt="4">-->
                    <img class="brand-title" src="{{ asset('images/brand.png') }}" alt="4">
    			@endif	
            </a>  --}}
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Header start
        ***********************************-->

		@include('elements.header')


        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        @include('elements.sidebar')
        <!--**********************************
            Sidebar end
        ***********************************-->



        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
            @yield('content')
        </div>
        <!--**********************************
            Content body end
        ***********************************-->


        <!--**********************************
            Footer start
        ***********************************-->

		@include('elements.footer')

        <!--**********************************
            Footer end
        ***********************************-->

		<!--**********************************
           Support ticket button start
        ***********************************-->

        <!--**********************************
           Support ticket button end
        ***********************************-->


    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
	@include('elements.footer-scripts')
  @yield('scripts')


<link href="{{ asset('vendor/toastr/css/toastr.min.css') }}" rel="stylesheet" type="text/css"/>

<script src="{{ asset('/vendor/toastr/js/toastr.min.js') }}" type="text/javascript"></script>
  <script>
    @if(session()->has('error'))
     toastr.error("", "{{ session()->get('error')}}", {
                    positionClass: "toast-top-right",
                    timeOut: 5000,
                    closeButton: !0,
                    debug: !1,
                    newestOnTop: !0,
                    progressBar: !0,
                    preventDuplicates: !0,
                    onclick: null,
                    showDuration: "300",
                    hideDuration: "1000",
                    extendedTimeOut: "1000",
                    showEasing: "swing",
                    hideEasing: "linear",
                    showMethod: "fadeIn",
                    hideMethod: "fadeOut",
                    tapToDismiss: !1
                })
    @endif
    @if(session()->has('success'))
       toastr.success("", "{{ session()->get('success')}}", {
                    timeOut: 5000,
                    closeButton: !0,
                    debug: !1,
                    newestOnTop: !0,
                    progressBar: !0,
                    positionClass: "toast-top-right",
                    preventDuplicates: !0,
                    onclick: null,
                    showDuration: "300",
                    hideDuration: "1000",
                    extendedTimeOut: "1000",
                    showEasing: "swing",
                    hideEasing: "linear",
                    showMethod: "fadeIn",
                    hideMethod: "fadeOut",
                    tapToDismiss: !1
                })
    @endif
  </script>
</body>
</html>
