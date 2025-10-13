<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Erda Illumine | <?php echo $__env->yieldContent('title', $page_title ?? ''); ?></title>

	<meta name="description" content="<?php echo $__env->yieldContent('page_description', $page_description ?? ''); ?>"/>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/favicon.ico')); ?>">



	<?php if(!empty(config('dz.public.pagelevel.css.'.$action))): ?>
		<?php $__currentLoopData = config('dz.public.pagelevel.css.'.$action); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $style): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				<link href="<?php echo e(asset($style)); ?>" rel="stylesheet" type="text/css"/>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	<?php endif; ?>

	
	<?php if(!empty(config('dz.public.global.css'))): ?>
		<?php $__currentLoopData = config('dz.public.global.css'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $style): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
			<link href="<?php echo e(asset($style)); ?>?t=<?php echo e(time()); ?>" rel="stylesheet" type="text/css"/>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	<?php endif; ?>
    <style>
        @media (max-width: 1200px) {
                .small-logo {
                  display: none;
                }
        }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
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
            <a href="<?php echo url('admin/dashboard'); ?>" class="brand-logo">
                <img class="logo-abbr biglogo d-md-block" style="max-width: 182px;" src="<?php echo e(asset('images/erda-logo.svg')); ?>" alt="logo">
                <!-- <img class="small-logo" src="<?php echo e(asset('images/smalllogo.jpeg')); ?>" alt=""> -->
			
            </a>
          	
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

		<?php echo $__env->make('elements.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        <?php echo $__env->make('elements.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!--**********************************
            Sidebar end
        ***********************************-->



        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
            <?php echo $__env->yieldContent('content'); ?>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->


        <!--**********************************
            Footer start
        ***********************************-->

		<?php echo $__env->make('elements.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

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
	<?php echo $__env->make('elements.footer-scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <?php echo $__env->yieldContent('scripts'); ?>


<link href="<?php echo e(asset('vendor/toastr/css/toastr.min.css')); ?>" rel="stylesheet" type="text/css"/>

<script src="<?php echo e(asset('/vendor/toastr/js/toastr.min.js')); ?>" type="text/javascript"></script>
  <script>
    <?php if(session()->has('error')): ?>
     toastr.error("", "<?php echo e(session()->get('error')); ?>", {
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
    <?php endif; ?>
    <?php if(session()->has('success')): ?>
       toastr.success("", "<?php echo e(session()->get('success')); ?>", {
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
    <?php endif; ?>
  </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\erda-illumine\resources\views/layout/default.blade.php ENDPATH**/ ?>