<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/c_quest_logo_icon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <div class="doctor-info-content">
  	  {!! $data['carbon_credit'] !!}
  </div>
    <div class="cs-invoice_head cs-mb10"  style="diplay:block;width:100%;">
        <div class="cs-invoice_left" style="float:left;width:50%">
            <b class="cs-primary_color">Farmer Detail:</b>
            <p>
            {{$data['Farmer']['farmer_name']}}
            <br>{{$data['Farmer']['mobile']}}
            </p>
        </div>
        <div class="cs-invoice_right cs-text_right" style="float:right;width:50%">
            @if($data['Farmer']['signature'])
                <p> <img class="float-right mb-3" style="float:right;width: 100px; height: 100px" src="{{Storage::disk('s3')->url($data['Farmer']['signature'])}}"> </p><br>&nbsp;
            @endif
            
        </div>
        <div style="display:block;width:100%;height:1px;clear:both;"></div>
    </div>
  <div class="float-right">
    <span class="mb-5" style="margin-left:79%;">{{$data['Farmer']['sign_carbon_date']}}</span>
  </div>
</body>
</html>
