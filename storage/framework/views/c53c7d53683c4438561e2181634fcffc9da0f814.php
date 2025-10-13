<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>404 Not Found</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(asset('images/favicon.png')); ?>">
    <link href="<?php echo e(asset('css/style.css')); ?>" rel="stylesheet">
    
</head>

<body class="h-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
            <div class="col-md-5">
                <div class="form-input-content text-center error-page">
                    <h1 class="error-text font-weight-bold">404</h1>
                    <h4><i class="fa fa-exclamation-triangle text-warning"></i> The page you were looking for is not found!</h4>
                    <p>You may have mistyped the address or the page may have moved.</p>
                    <div>
                        <a class="btn btn-primary" href="<?php echo url()->previous(); ?>">Back to Home</a>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
<script src="<?php echo e(asset('vendor/global/global.min.js')); ?>" type="text/javascript"></script>
<script src="<?php echo e(asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js')); ?>" type="text/javascript"></script>
<script src="<?php echo e(asset('js/deznav-init.js')); ?>" type="text/javascript"></script>
</body>

</html><?php /**PATH C:\xampp\htdocs\erda-illumine\resources\views/errors/404.blade.php ENDPATH**/ ?>