<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

    @include('layouts.inc.head')

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.inc.header')

        <div class="main-content">
            @yield('content')

            @include('layouts.inc.footer')
        </div>

        @include('common.modal')

        <!-- removeNotificationModal -->
        <div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="NotificationModalbtn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mt-2 text-center">
                            <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                            <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                                <h4>Are you sure ?</h4>
                                <p class="text-muted mx-4 mb-0">Are you sure you want to remove this Notification ?</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                            <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete It!</button>
                        </div>
                    </div>

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </div>

    <!--start back-to-top-->
    <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
        <i class="ri-arrow-up-line"></i>
    </button>
    <!--end back-to-top-->

    <!--preloader-->
    <div id="preloader">
        <div id="status">
            <div class="spinner-border text-primary avatar-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
        <div id="liveToast" class="toast text-white fade" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- whatsapp add -->
    @include('chat.comman_chat_model')


    <script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
   <script src='//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js'></script>
   <script src="{{ asset('public/build/assets/js/sticky-notes.js') }}"></script>

    <!-- JAVASCRIPT -->
   <script src="{{ asset('public/build/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
   <script src="{{ asset('public/build/assets/libs/simplebar/simplebar.min.js') }}"></script>
   <script src="{{ asset('public/build/assets/libs/node-waves/waves.min.js') }}"></script>
   <script src="{{ asset('public/build/assets/libs/feather-icons/feather.min.js') }}"></script>
   <script src="{{ asset('public/build/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>

    <!-- Vector map-->
   <script src="{{ asset('public/build/assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
   <script src="{{ asset('public/build/assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

   <!--Swiper slider js-->
   <script src="{{ asset('public/build/assets/libs/swiper/swiper-bundle.min.js') }}"></script>



   <!-- list.js min js -->
   <script src="{{ asset('public/build/assets/libs/list.js/list.min.js') }}"></script>
   <script src="{{ asset('public/build/assets/libs/list.pagination.js/list.pagination.min.js') }}"></script>



   <!-- dom autoscroll -->
   <script src="{{ asset('public/build/assets/libs/dom-autoscroller/dom-autoscroller.min.js') }}"></script>



   <!-- Sweet Alerts js -->
   <script src="{{ asset('public/build/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

   <!-- dragula init js -->
   <script src="{{ asset('public/build/assets/libs/dragula/dragula.min.js') }}"></script>

   <!-- plugin js -->
   <script src="{{ asset('public/build/assets/js/plugins.js') }}"></script>


   <!-- plugin js -->
   <script src="{{ asset('public/build/assets/libs/prismjs/prism.js') }}"></script>

   <!-- App js -->
   <script src="{{ asset('public/build/assets/js/app.js') }}"></script>

   <!-- Custom js -->
   <script src="{{ asset('public/build/assets/js/custom.js') }}"></script>

    <!-- whatsapp -->
     <script src="{{ asset('public/build/assets/js/pages/user/sweetalert2.all.min.js') }}"></script>
      <script src="{{ asset('public/build/assets/js/pages/user/toastify.js') }}"></script>
      <script src="{{ asset('public/build/assets/js/pages/user/jquery.validate.js') }}"></script>
       <script src="{{ asset('public/build/assets/js/pages/user/form.js?v=2') }}"></script>


   @yield('scripts')

   @if($message = Session::get('success'))
        <script>
            show_toastr('success', '{!! $message !!}');
        </script>
    @endif
    @if($message = Session::get('error'))
        <script>
            show_toastr('error', '{!! $message !!}');
        </script>
    @endif

       <script>
    window.IS_DISCOUNT_ALLOWED = {{ \App\Models\Utility::isDiscountAllowed() }};
</script>
        <script>
    function applyDiscountVisibility()
    {
        console.log('-----applyDiscountVisibility call ----');
        if (window.IS_DISCOUNT_ALLOWED === 0)
        {
            console.log('if ');
            document.querySelectorAll('.hide_discount').forEach(el => {
                console.log('el ',el);
                el.classList.add('d-none');
            });
        } else {
            console.log('else');
            document.querySelectorAll('.hide_discount').forEach(el => {
                el.classList.remove('d-none');
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyDiscountVisibility();
    });
</script>

    @yield('page-script')



</body>
</html>


