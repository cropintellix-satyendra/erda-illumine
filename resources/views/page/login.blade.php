<!doctype html>
<html lang="en">
  <head>
    <title>Erda Illumine - Login</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.ico')}}">

    <link rel="stylesheet" href="{{ asset('loginpage/css/custom1.css')}}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  
  <style>
    html, body {
      height: 100%;
      margin: 0;
    }
    
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    
    .main-content {
      flex: 1;
    }
    
    .AwdNew {
      width: 100%;
      text-align: center;
      padding-left: 20px;
      padding-right: 20px;
      height: 90px;
    }
    
    .Awd {
      margin-top: 40px;
      background: rgb(30 113 183);
    }
    
    .alternate {
      padding-top: 27px;
    }
    
    .card-body {
      padding: 2.25rem;
    }
    
    .footer-section {
      margin-top: auto;
      background-color: rgba(255, 255, 255, 0.9);
      padding: 5px 0;
    }
    
    .footer-container {
      align-items: center;
      margin: 0 auto;
      padding: 0 5px;
    }
    
    .grid-item {
      flex: 1;
    }
    
    .footer-item {
      text-align: center;
    }
    
    .foot1 {
      margin-bottom: 5px;
      color:rgb(11, 11, 11);
    }
    
    .foot1 h6 {
      margin: 0;
      font-size: 14px;
    }
    
    .foot1 a {
      color: #007bff;
      text-decoration: none;
    }
    
    .foot1 a:hover {
      text-decoration: underline;
    }
    
    @media (max-width: 768px) {
      .footer-container {
        flex-direction: column;
        text-align: center;
      }
      
      .float-right {
        margin-top: 10px;
      }
      
      .float-right img {
        width: 150px !important;
        height: 75px !important;
      }
    }
    .biglogo{
      padding-top: 25px;
    }
  </style>
  <body style="background:url({{('public/loginpage/images/awdbackground.jpg')}});background-position: top center;background-size: cover;background-repeat: no-repeat;">
    <div class="main-content">
      <div class="AwdNew text-center">
        <img class="logo-abbr biglogo d-md-block" src="{{ asset('images/erda-logo.svg') }}" alt="logo">
      </div>
      <div class="Awd text-center">
        <h6 class="alternate">Alternate Wetting & Drying Platform</h6>
      </div>

      <section class="my-2">
      <div class="container">
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card  text-white" style="border-radius: 1rem;background: rgb(30 113 183);">
              <div class="card-body text-center">
                   @if($errors->any())
                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger mt-1 mb-1">{{ $error }}</div>
                        @endforeach
                    @endif
            <form action="{!! url('/signin'); !!}" method="post">
                @csrf
                <div class="">
                   <div class="form-outline mb-4" style="border-radius:25px;">
                    <input type="email" id="typeEmailX" class="form-control form-control-lg" name="email" style="border-radius:25px;border: 3px solid #3aaa35;" placeholder="Username" />
                     @error('email')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                  @enderror
                  </div>
                  <div class="form-outline mb-4" style="border-radius:25px;">
                    <input type="password" id="typePasswordX" class="form-control form-control-lg" name="password" style="border-radius:25px;border: 3px solid #3aaa35;" placeholder="Password"/>
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
    </div>
     <section class="footer-section">
      <footer class="footer-container my-5">
        <div class="grid-item"></div>
        <div class="grid-item">
          <footer class="text-dark  footer-item">
        
          <div class="foot1"><h6>Developed by:</h6></div>
        <div class="foot1"> <h6>{{Carbon\Carbon::now()->format('Y')}} Crop Intellix Pvt. Ltd.</h6></div>
        <div class="foot1"><h6><a href="{{url('privacy-policy')}}" target="_blank">Privacy & Policy</a> | <a href="{{url('terms-and-condition')}}" target="_blank">Terms & conditions</a> | <a href="mailto:sales@cropintellix.com">sales@cropintellix.com</a><h6></div>
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