<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRF Token -->

    @php
        $website_nm = \App\Models\Utility::getWebsiteName();
        $website_img = asset('public/build/assets/images/engage-logo.png');
         $default_img = \App\Models\Utility::defaultImage();
    @endphp

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>  {{ $website_nm ?? '' }}</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />



    <script src="//cdn.jsdelivr.net/gh/alpinejs/alpine@v2.3.5/dist/alpine.min.js" defer></script>


    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ !empty($website_img) ? $website_img : $default_img }}">
    <!-- jsvectormap css -->
    <link href="{{ asset('public/build/assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <!--Swiper slider css-->
    <link href="{{ asset('public/build/assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('public/build/assets/libs/dragula/dragula.min.css') }}" />
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <!-- Layout config Js -->
    <script src="{{ asset('public/build/assets/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    {{-- <link href="{{ asset('public/build/assets/css/bootstrap-rtl.min.css') }}" rel="stylesheet" type="text/css" /> --}}
     <link href="{{ asset('public/build/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Icons Css -->
    <link href="{{ asset('public/build/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    {{-- <link href="{{ asset('public/build/assets/css/app-rtl.min.css') }}" rel="stylesheet" type="text/css" />--}}
    <link href="{{ asset('public/build/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- custom Css-->
    {{-- <link href="{{ asset('public/build/assets/css/custom-rtl.min.css') }}" rel="stylesheet" type="text/css" /> --}}
    <link href="{{ asset('public/build/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />

    <link rel='stylesheet' href='//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css'>
    <link rel="stylesheet" href="{{ asset('public/build/assets/css/sticky-notes.css') }}">
    <!-- Jquery cdn -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


    <style>
    .bg-gradient-red
    {
        background: linear-gradient(87deg, #f5365c 0, #f56036 100%) !important;
    }
    .bg-gradient-green
    {
        background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important;
    }
   </style>


    @yield('page-css')
</head>
