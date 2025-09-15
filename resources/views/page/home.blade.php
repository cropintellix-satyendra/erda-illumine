<!DOCTYPE html>
<html
  data-wf-site="5437f25bb554f2e70fc83848"
  data-wf-page="5437f25bb554f2e70fc83849"
>
  <head>
    <meta charset="utf-8" />
    <title>{{ config('dz.name') }} | @yield('title', $page_title ?? '')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="generator" content="Webflow" />
    <link
      rel="stylesheet"
      type="text/css"
      href="{{asset('homepage/daks2k3a4ib2z.cloudfront.net/5437f25bb554f2e70fc83848/css/geospace.webflow.e37f40551.css')}}"
    />
    <link rel="stylesheet" href="{{asset('homepage/home.css')}}"/>
    <script src="{{asset('homepage/ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js')}}"></script>
    <script>
      WebFont.load({
        google: {
          families: [
            "Merriweather:300,400,700,900",
            "Montserrat:400,700",
            "Lato:100,100italic,300,300italic,400,400italic,700,700italic,900,900italic",
            "Droid Serif:400,700",
            "Fjalla One:regular:latin,latin-ext",
            "Source Sans Pro:300,regular,600,700:latin-ext,latin",
            "Noticia Text:regular,italic,700:latin-ext,latin",
            "Domine:regular,700",
          ],
        },
      });
    </script>
    <script
      type="text/javascript"
      src="{{asset('homepage/daks2k3a4ib2z.cloudfront.net/0globals/modernizr-2.7.1.js')}}"
    ></script>
    <link
      rel="shortcut icon"
      type="image/x-icon"
      href="{{ asset('images/c_quest_logo_icon.png') }}"
    />
    <link
      rel="apple-touch-icon"
      href="{{asset('homepage/daks2k3a4ib2z.cloudfront.net/img/webclip.png')}}"
    />
    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(["_setAccount", "UA-59914252-1"], ["_trackPageview"]);

      (function () {
        var ga = document.createElement("script");
        ga.src =
          ("https:" == document.location.protocol
            ? "https://ssl"
            : "http://www") + ".google-analytics.com/ga.js";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(ga, s);
      })();
    </script>
    <style>
      .news,
      .news-2,
      .news-3 {
        cursor: pointer;
      }
    </style>
  </head>
  <body>
    <div class="w-section w-hidden-main view-mob">
      <div id="01" class="w-clearfix content-main-mob">
        <!-- <a href="#" data-ix="mob-1" class="w-inline-block news mob">
          <div class="alt-1 mobile">
            <h5 class="h5-mobo">CLEANER COOCKING PROJECTS</h5>
          </div>
        </a> -->
      </div>
      <div id="02" class="content-main-mob">
        <a href="#02" data-ix="mob-2" class="w-inline-block news-2 mob">
          <div class="alt-1 mobile">
            <h5>SRCWM</h5>
          </div>
        </a>
        <div id="content-2" class="w-section content-mob-2">
          <div data-ix="interna-indian" class="inside _03">
            <div class="scrwm-form">
              <h1>
                SUSTAINABLE RICE CULTIVATION <br />
                & <br />
                WATER MANAGEMENT <br />
                (SRCWM)
              </h1>
              <img src="{{asset('images/brand.jpeg')}}" alt="" />
              <div class="login-form">
                  @if($errors->any())
                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger mt-1 mb-1">{{ $error }}</div>
                        @endforeach
                    @endif
                <form action="{!! url('/signin'); !!}" method="post">
                  @csrf
                  <input
                    type="email"
                    name="email"
                    id=""
                    placeholder="Enter Email"
                    class="userName form-control"
                  />
                  @error('email')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                  @enderror
                  <input
                    type="password"
                    name="password"
                    id=""
                    placeholder="Password"
                    class="password form-control"
                  />
                  @error('password')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                  @enderror
                  <!--<a href="#" class="forget-password"> Forget Password ?? </a>-->
                  <button class="login-btn">Login</button>
                  <p>Don't have an account ? <a href="#">Creat Account.</a></p>
                </form>
              </div>
              <p class="credit">
                Developed by: <br />
                {{Carbon\Carbon::now()->format('Y')}} KS , <br />
                Legal Center | Trust Center | Privacy | Site Map | Accessibility
              </p>
            </div>
          </div>
        </div>
      </div>
      <div id="03" class="w-clearfix content-main-mob">
        <a href="#03" data-ix="mob-3" class="w-inline-block news-3 mob">
          <div class="alt-1 mobile">
            <h5>AGROFORESTRY</h5>
          </div>
        </a>
        <div class="w-section content-mob-3">
          <div data-ix="hidden" class="w-clearfix inside _02">
            <div class="agro-form">
              <h1>AGROFORESTRY</h1>
              <img src="{{asset('images/brand.jpeg')}}" alt="" />
              <div class="login-form">
                <form action="">
                  <input
                    type="text"
                    name="userName"
                    id=""
                    placeholder="User Name"
                    class="userName"
                  />
                  <input
                    type="password"
                    name="password"
                    id=""
                    placeholder="Password"
                    class="password"
                  />
                  <!--<a href="#" class="forget-password"> Forget Password ?? </a>-->
                  <button class="login-btn">Login</button>
                  <p>Don't have an account ? <a href="#">Creat Account.</a></p>
                </form>
              </div>
            </div>
            <p class="credit">
              Developed by: <br />
              {{Carbon\Carbon::now()->format('Y')}} Crop Intellix Pvt Ltd, <br />
              Legal Center | Trust Center | Privacy | Site Map | Accessibility
            </p>
          </div>
        </div>
      </div>
    </div>
    <div class="w-section w-clearfix grid">
      <div class="cel-grid"></div>
      <div class="cel-grid"></div>
      <div class="cel-grid"></div>
    </div>
    <div class="w-section w-clearfix nav-header">
      <div class="box-logo"></div>
    </div>
    <div data-ix="display-menu" class="w-section top-menu">
      <div class="shape-menu"></div>
      <div class="btn-menu"></div>
    </div>
    <div
      data-ix="news-1"
      class="w-hidden-medium w-hidden-small w-hidden-tiny w-clearfix news"
    >
      <!-- <div data-ix="alpha-alts" class="w-clearfix alt-1">
        <h5>CLEANER COOCKING PROJECTS</h5>
      </div> -->
    </div>
    <div
      data-ix="news-2"
      class="w-hidden-medium w-hidden-small w-hidden-tiny w-clearfix news-2"
    >
      <div data-ix="alpha-alts" class="w-clearfix alt-2">
        <h5>SRCWM</h5>
      </div>
      <div data-ix="sidebar-2" class="w-section w-clearfix side-indian">
        <div class="content-side indian"></div>
      </div>
    </div>
    <div
      data-ix="news-3"
      class="w-section w-hidden-medium w-hidden-tiny w-clearfix news-3"
    >
      <div data-ix="alpha-alts" class="alt">
        <h5>AGROFORESTRY</h5>
      </div>
    </div>

    <div
      data-ix="display-news"
      class="w-section w-hidden-medium w-hidden-small w-hidden-tiny full-03"
    >
      <div data-ix="hidden" class="w-clearfix inside _02">
        <div class="w-clearfix content-inside">
          <div class="agro-form">
            <h1>AGROFORESTRY</h1>
            <img src="{{asset('images/brand.jpeg')}}" alt="" />
            <div class="login-form">
              <form action="">
                <input
                  type="text"
                  name="userName"
                  id=""
                  placeholder="User Name"
                  class="userName"
                />
                <input
                  type="password"
                  name="password"
                  id=""
                  placeholder="Password"
                  class="password"
                />
                <!--<a href="#" class="forget-password"> Forget Password ?? </a>-->
                <button class="login-btn">Login</button>
                <p>Don't have an account ? <a href="#">Creat Account.</a></p>
              </form>
            </div>
          </div>
          <p class="credit">
            Developed by: <br />
            {{Carbon\Carbon::now()->format('Y')}} Crop Intellix Pvt Ltd, <br />
            Legal Center | Trust Center | Privacy | Site Map | Accessibility
          </p>
        </div>
      </div>
    </div>
    <div
      data-ix="interna-indian"
      class="w-section w-hidden-medium w-hidden-small w-hidden-tiny full-02"
    >
      <div data-ix="interna-indian" class="inside _03">
        <div class="scrwm-form">
          <h1>
            SUSTAINABLE RICE CULTIVATION <br />
            & <br />
            WATER MANAGEMENT <br />
            (SRCWM)
          </h1>
          <img src="{{asset('images/brand.jpeg')}}" alt="" />
          <div class="login-form">
            @if($errors->any())
                @foreach($errors->all() as $error)
                    <div class="alert alert-danger mt-1 mb-1"><span style="color:red">{{ $error }}</span></div>
                @endforeach
            @endif
            <form action="{!! url('/signin'); !!}" method="post">
              @csrf
              <input
                type="email"
                name="email"
                id=""
                placeholder="Enter Email"
                class="userName form-control"
              />
              @error('email')
                <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
              @enderror
              <input
                type="password"
                name="password"
                id=""
                placeholder="Password"
                class="password form-control"
              />
              @error('password')
                <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
              @enderror
              <!--<a href="#" class="forget-password"> Forget Password ?? </a>-->
              <button class="login-btn">Login</button>
              <!-- <p>Don't have an account ? <a href="#">Create Account.</a></p> -->
            </form>
          </div>
          <p class="credit">
            Developed by: <br />
            {{Carbon\Carbon::now()->format('Y')}} Crop Intellix Pvt Ltd, <br />
            Legal Center | Trust Center | Privacy | Site Map | Accessibility
          </p>
        </div>
      </div>
    </div>
    <script
      type="text/javascript"
      src="{{asset('homepage/ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js')}}"
    ></script>
    <script
      type="text/javascript"
      src="{{asset('homepage/daks2k3a4ib2z.cloudfront.net/5437f25bb554f2e70fc83848/js/webflow.1f1247a5c.js')}}"
    ></script>
    <!--[if lte IE 9
      ]><script src="//cdnjs.cloudflare.com/ajax/libs/placeholders/3.0.2/placeholders.min.js"></script
    ><![endif]-->
    <script type="text/javascript">

      $(".imagezoom").hover(
        function () {
          $(this).animate({
            "background-size": "110%",
          });
        },
        function () {
          $(this).animate({
            "background-size": "100%",
          });
        }
      );
    </script>
    <script type="text/javascript">
            function noBack(){window.history.forward()}
            noBack();
            window.onload=noBack;
            window.onpageshow=function(evt){if(evt.persisted)noBack()}
            window.onunload=function(){void(0)}
    </script>
  </body>
</html>
