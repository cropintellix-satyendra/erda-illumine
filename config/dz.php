<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Carbon Intellix'),


    'public' => [
        'favicon' => 'images/favicon.ico',
        'fonts' => [
            'google' => [
                'families' => [
                    'Poppins:300,400,500,600,700'
                ]
            ]
        ],
		'global' => [
			'css' => [
				'vendor/bootstrap-select/dist/css/bootstrap-select.min.css',
				'css/style.css',
				'css/custom.css',
			],
			'js' => [
				'vendor/global/global.min.js',
				'vendor/bootstrap-select/dist/js/bootstrap-select.min.js',
				// 'js/custom.min.js',


				// 'js/deznav-init.js',

			],
		],
		'pagelevel' => [
			'css' => [
				'dashboard_1' => [
							'vendor/jqvmap/css/jqvmap.min.css',
				// 			'vendor/chartist/css/chartist.min.css',
							'https://cdn.lineicons.com/2.0/LineIcons.css',
				],
				'doctor_index' => [
							'vendor/jqvmap/css/jqvmap.min.css',
							'vendor/chartist/css/chartist.min.css',
							'https://cdn.lineicons.com/2.0/LineIcons.css',
				],
				'doctors_details' => [
							'vendor/chartist/css/chartist.min.css',
							'vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',
							// 'https://cdn.lineicons.com/2.0/LineIcons.css',
				],
				'doctors_review' => [
							'vendor/jqvmap/css/jqvmap.min.css',
							'vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',
				],
				'patient_details' => [
							'vendor/chartist/css/chartist.min.css',
							'vendor/owl-carousel/owl.carousel.css',
				],
				'app_calender' => [
							'vendor/fullcalendar/css/fullcalendar.min.css',
							'css/icomoon.css',
				],
				'chart_chartist' => [
							'vendor/chartist/css/chartist.min.css',
				],				
				'table_farmer' => [
				            'vendor/sweetalert2/dist/sweetalert2.min.css',
				            'vendor/select2/css/select2.min.css',
				            'js/bootstrap-datepicker.min.js',
				],
				'table_landownerships' => [
							'vendor/datatables/css/jquery.dataTables.min.css',
				            'vendor/sweetalert2/dist/sweetalert2.min.css',
				            'vendor/select2/css/select2.min.css',
				],
				'chart_chartjs' => [
				],
				'chart_flot' => [
							'',
				],
				'chart_morris' => [
							'',
				],
				'chart_peity' => [
							'',
				],
				'chart_sparkline' => [
							'',
				],
				'ecom_checkout' => [
							'',
				],
				'ecom_customers' => [
							'',
				],
				'ecom_invoice' => [
							'',
				],
				'ecom_product_detail' => [
							'',
				],
				'ecom_product_grid' => [
							'',
				],
				'ecom_product_list' => [
							'',
				],
				'ecom_product_order' => [
							'',
				],
				'email_compose' => [
							'vendor/dropzone/dist/dropzone.css',
				],
				'email_inbox' => [
							'',
				],
				'email_read' => [
							'',
				],
				'form_editor_summernote' => [
							'vendor/summernote/summernote.css',
				],
				'form_element' => [
							'',
				],
				'form_pickers' => [
							'vendor/bootstrap-daterangepicker/daterangepicker.css',
							'vendor/clockpicker/css/bootstrap-clockpicker.min.css',
							'vendor/jquery-asColorPicker/css/asColorPicker.min.css',
							'vendor/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css',
							'vendor/pickadate/themes/default.css',
							'vendor/pickadate/themes/default.date.css',
							'vendor/select2/css/select2.min.css',
							'vendor/sweetalert2/dist/sweetalert2.min.css',
							'public/css/default-skin.css',
							'public/css/photoswipe.css',
							'vendor/summernote/summernote.css',

				],
				'form_validation_jquery' => [
							'',
				],
				'form_wizard' => [
							'vendor/jquery-steps/css/jquery.steps.css',
				],
				'map_jqvmap' => [
							'vendor/jqvmap/css/jqvmap.min.css',
				],
				'table_bootstrap_basic' => [
							'',
				],
				'table_datatable_basic' => [
							'vendor/datatables/css/jquery.dataTables.min.css',
							'vendor/sweetalert2/dist/sweetalert2.min.css',
							'vendor/sweetalert2/dist/sweetalert2.min.js',
				],
				'uc_nestable' => [
							'vendor/nestable2/css/jquery.nestable.min.css',
				],
				'uc_noui_slider' => [
							'vendor/nouislider/nouislider.min.css',
				],
				'uc_select2' => [
							'vendor/select2/css/select2.min.css',
				],
				'uc_sweetalert' => [
							'vendor/sweetalert2/dist/sweetalert2.min.css',
				],
				'uc_toastr' => [
							'vendor/toastr/css/toastr.min.css',
				],
				'ui_accordion' => [
							'',
				],
				'ui_alert' => [
							'',
				],
				'ui_badge' => [
							'',
				],
				'ui_button' => [
							'',
				],
				'ui_button_group' => [
							'',
				],
				'ui_card' => [
							'',
				],
				'ui_carousel' => [
							'',
				],
				'ui_dropdown' => [
							'',
				],
				'ui_grid' => [
							'',
				],
				'ui_list_group' => [
							'',
				],
				'ui_media_object' => [
							'',
				],
				'ui_modal' => [
							'',
				],
				'ui_pagination' => [
							'',
				],
				'ui_popover' => [
							'',
				],
				'ui_progressbar' => [
							'',
				],
				'ui_tab' => [
							'',
				],
				'ui_typography' => [
							'',
				],
				'widget_basic' => [
							'vendor/chartist/css/chartist.min.css',
				],
			],
			'js' => [
				'dashboard_1' => [
				// 			'vendor/apexchart/apexchart.js',
				// 			'js/dashboard/dashboard-1.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'table_farmer' => [
				            'vendor/chart.js/Chart.bundle.min.js',
				            'vendor/apexchart/apexchart.js',
				            'js/custom.min.js',
				            'js/deznav-init.js',
				            'vendor/sweetalert2/dist/sweetalert2.min.js',
				            'vendor/select2/js/select2.full.min.js',
				            'js/plugins-init/select2-init.js',
				],
				'table_landownerships' => [
				            'vendor/chart.js/Chart.bundle.min.js',
				            'vendor/apexchart/apexchart.js',
				            'vendor/datatables/js/jquery.dataTables.min.js',
				            'js/plugins-init/datatables.init.js',
				            'js/custom.min.js',
				            'js/deznav-init.js',
				            'vendor/sweetalert2/dist/sweetalert2.min.js',
				            'vendor/select2/js/select2.full.min.js',
				            'js/plugins-init/select2-init.js',
				],
				'doctor_index' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/peity/jquery.peity.min.js',
							'vendor/chartist/js/chartist.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'doctors_details' => [
							'vendor/chartist/js/chartist.min.js',
							'vendor/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/bootstrap-datetimepicker/js/moment.js',
							'vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'doctors_review' => [
							'vendor/apexchart/apexchart.js',
							'vendor/bootstrap-datetimepicker/js/moment.js',
							'vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'patient_details' => [
							'vendor/owl-carousel/owl.carousel.js',
							'vendor/peity/jquery.peity.min.js',
							'js/plugins-init/piety-init.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'app_calender' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/jqueryui/js/jquery-ui.min.js',
							'vendor/moment/moment.min.js',
							'vendor/fullcalendar/js/fullcalendar.min.js',
							'js/plugins-init/fullcalendar-init.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'app_profile' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'chart_chartist' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/chartist/js/chartist.min.js',
							'vendor/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js',
							'js/plugins-init/chartist-init.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'chart_chartjs' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/chart.js/Chart.bundle.min.js',
							'js/plugins-init/chartjs-init.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'chart_flot' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/flot/jquery.flot.js',
							'vendor/flot/jquery.flot.pie.js',
							'vendor/flot/jquery.flot.resize.js',
							'vendor/flot-spline/jquery.flot.spline.min.js',
							'js/plugins-init/flot-init.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'chart_morris' => [
							'vendor/chart.js/Chart.bundle.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
							'vendor/apexchart/apexchart.js',
							'vendor/raphael/raphael.min.js',
							'vendor/morris/morris.min.js',
							'js/plugins-init/morris-init.js',
				],
				'chart_peity' => [
							'vendor/chart.js/Chart.bundle.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
							'vendor/apexchart/apexchart.js',
							'vendor/peity/jquery.peity.min.js',
							'js/plugins-init/piety-init.js',
				],
				'chart_sparkline' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/jquery-sparkline/jquery.sparkline.min.js',
							'js/plugins-init/sparkline-init.js',
							'vendor/svganimation/vivus.min.js',
							'vendor/svganimation/svg.animation.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'ecom_checkout' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/highlightjs/highlight.pack.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'ecom_customers' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/highlightjs/highlight.pack.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'ecom_invoice' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/highlightjs/highlight.pack.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'ecom_product_detail' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/highlightjs/highlight.pack.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'ecom_product_grid' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/highlightjs/highlight.pack.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'ecom_product_list' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/highlightjs/highlight.pack.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'ecom_product_order' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/highlightjs/highlight.pack.min.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'email_compose' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/dropzone/dist/dropzone.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'email_inbox' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'email_read' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'form_editor_summernote' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/summernote/js/summernote.min.js',
							'js/plugins-init/summernote-init.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'form_element' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'form_pickers' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/moment/moment.min.js',
							'vendor/bootstrap-daterangepicker/daterangepicker.js',
							'vendor/clockpicker/js/bootstrap-clockpicker.min.js',
							'vendor/jquery-asColor/jquery-asColor.min.js',
							'vendor/jquery-asGradient/jquery-asGradient.min.js',
							'vendor/jquery-asColorPicker/js/jquery-asColorPicker.min.js',
							'vendor/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js',
							'vendor/pickadate/picker.js',
							'vendor/pickadate/picker.time.js',
							'vendor/pickadate/picker.date.js',
							'js/plugins-init/bs-daterange-picker-init.js',
							'js/plugins-init/clock-picker-init.js',
							'js/plugins-init/jquery-asColorPicker.init.js',
							'js/plugins-init/material-date-picker-init.js',
							'js/plugins-init/pickadate-init.js',
							'vendor/select2/js/select2.full.min.js',
				            'js/plugins-init/select2-init.js',
				            'vendor/sweetalert2/dist/sweetalert2.min.js',
				            'js/plugins-init/sweetalert.init.js',
				            'js/custom.min.js',
				            'js/deznav-init.js',
				            'public/js/photoswipe.min.js',
							'public/js/photoswipe-ui-default.min.js',
							'vendor/summernote/js/summernote.min.js',
							'js/plugins-init/summernote-init.js',
				],
				'form_validation_jquery' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/jquery-validation/jquery.validate.min.js',
							'js/plugins-init/jquery.validate-init.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'form_wizard' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/jquery-steps/build/jquery.steps.min.js',
							'vendor/jquery-validation/jquery.validate.min.js',
							'js/plugins-init/jquery.validate-init.js',
							'js/plugins-init/jquery-steps-init.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'map_jqvmap' => [
							'vendor/chart.js/Chart.bundle.min.js',
							'vendor/apexchart/apexchart.js',
							'vendor/jqvmap/js/jquery.vmap.min.js',
							'vendor/jqvmap/js/jquery.vmap.world.js',
							'vendor/jqvmap/js/jquery.vmap.usa.js',
							'js/plugins-init/jqvmap-init.js',
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'page_error_400' => [
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'page_error_400' => [
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'page_error_404' => [
              'js/custom.min.js',
              'js/deznav-init.js',
				],
				'page_error_500' => [
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'page_error_503' => [
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'page_forgot_password' => [
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'page_lock_screen' => [
            'vendor/deznav/deznav.min.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'page_login' => [
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'page_register' => [
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'table_bootstrap_basic' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'table_datatable_basic' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'vendor/datatables/js/jquery.dataTables.min.js',
            'js/plugins-init/datatables.init.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'uc_nestable' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'vendor/nestable2/js/jquery.nestable.min.js',
            'js/plugins-init/nestable-init.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'uc_noui_slider' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'vendor/nouislider/nouislider.min.js',
            'vendor/wnumb/wNumb.js',
            'js/plugins-init/nouislider-init.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'uc_select2' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'vendor/select2/js/select2.full.min.js',
            'js/plugins-init/select2-init.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'uc_sweetalert' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'vendor/sweetalert2/dist/sweetalert2.min.js',
            'js/plugins-init/sweetalert.init.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'uc_toastr' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'vendor/toastr/js/toastr.min.js',
            'js/plugins-init/toastr-init.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_accordion' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_alert' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_badge' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_button' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_button_group' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_card' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_carousel' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_dropdown' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_grid' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_list_group' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_media_object' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_modal' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_pagination' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_popover' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_progressbar' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_tab' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'ui_typography' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				],
				'widget_basic' => [
            'vendor/chart.js/Chart.bundle.min.js',
            'vendor/apexchart/apexchart.js',
            'vendor/chartist/js/chartist.min.js',
            'vendor/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js',
            'vendor/flot/jquery.flot.js',
            'vendor/flot/jquery.flot.pie.js',
            'vendor/flot/jquery.flot.resize.js',
            'vendor/flot-spline/jquery.flot.spline.min.js',
            'vendor/jquery-sparkline/jquery.sparkline.min.js',
            'js/plugins-init/sparkline-init.js',
            'vendor/peity/jquery.peity.min.js',
            'js/plugins-init/piety-init.js',
            'js/plugins-init/widgets-script-init.js',
            'js/custom.min.js',
            'js/deznav-init.js',
				]

			]
		],
	]
];
