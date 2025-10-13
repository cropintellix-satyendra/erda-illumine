<?php if(!empty(config('dz.public.global.js'))): ?>
	<?php $__currentLoopData = config('dz.public.global.js'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $script): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
			<script src="<?php echo e(asset($script)); ?>" type="text/javascript"></script>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php if(!empty(config('dz.public.pagelevel.js.'.$action))): ?>
	<?php $__currentLoopData = config('dz.public.pagelevel.js.'.$action); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $script): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
			<script src="<?php echo e(asset($script)); ?>" type="text/javascript"></script>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
	<!--		<script src="<?php echo e(asset('js/custom.min.js')); ?>" type="text/javascript"></script>
			<script src="<?php echo e(asset('js/deznav-init.js')); ?>" type="text/javascript"></script> -->
<!--	
 <?php if(!empty(config('dz.public.education.pagelevel.js.'.$action))): ?>
	<?php $__currentLoopData = config('dz.public.education.pagelevel.js.'.$action); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $script): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
			<script src="<?php echo e(asset($script)); ?>" type="text/javascript"></script>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>	--><?php /**PATH C:\xampp\htdocs\erda-illumine\resources\views/elements/footer-scripts.blade.php ENDPATH**/ ?>