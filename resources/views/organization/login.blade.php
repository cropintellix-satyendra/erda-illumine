<!doctype html>
<html lang="en">
  <head>
    <title>KS - Login</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="{{ asset('loginpage/css/custom1.css')}}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  <body style="background:url({{('public/loginpage/images/awdbackground.jpg')}});background-position: top center;background-size: cover;background-repeat: no-repeat;">
    <div class="Awd text-center">
      <h6 class="awdfont">AWD</h6>
      <h6 class="alternate">Alternate Wetting & Drying Platform</h6>
  </div>

    <section class="my-2">
      <div class="container">
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card  text-white" style="border-radius: 1rem;background:rgba(0, 10, 19, 0.1);">
              <div class="card-body text-center" style="background:rgba(0, 10, 19, 0.1);">
                   @if($errors->any())
                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger mt-1 mb-1">{{ $error }}</div>
                        @endforeach
                    @endif
            <form action="{!! url('company/signin'); !!}" method="post">
                @csrf
                <div class="">
                   <div class="form-outline mb-4" style="border-radius:25px;">
                    <input type="email" id="typeEmailX" class="form-control form-control-lg" name="email" style="border-radius:25px;" placeholder="Username" />
                     @error('email')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                  @enderror
                  </div>
                  <div class="form-outline mb-4" style="border-radius:25px;">
                    <input type="password" id="typePasswordX" class="form-control form-control-lg" name="password" style="border-radius:25px;" placeholder="Password"/>
                    @error('password')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                  @enderror
                  </div>
                  <button class="btn btn-success btn1 btn-lg px-5" type="submit">Login</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
     <section class="footer-section">
      <footer class="footer-container my-5">
        <div class="grid-item"></div>
        <div class="grid-item">
          <footer class="text-dark  footer-item">

          <div class="foot1"><h6>Developed by:</h6></div>
        <div class="foot1"> <h6>{{Carbon\Carbon::now()->format('Y')}} Crop Intellix Pvt. Ltd.</h6></div>
        <div class="foot1"><h6>Legal Center | Trust Centre | Site Map | Accessibility<h6></div>
         </footer></div>
        <div class="float-right"><img class="float-right" src="{{ asset('images/brand.png')}}" alt="logo" style="width:200px;height:100px;"></div>
      </footer>
      </section>
  </body>
  <script type="text/javascript">
            function noBack(){window.history.forward()}
            noBack();
            window.onload=noBack;
            window.onpageshow=function(evt){if(evt.persisted)noBack()}
            window.onunload=function(){void(0)}
    </script>
  </html>
