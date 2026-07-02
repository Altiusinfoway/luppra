@extends('layouts.app')

@section('content')

    <div class="page-content">

        

    </div>

@endsection

@section('scripts')
    <!-- apexcharts -->
   <script src="{{ asset('public/build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

   <!-- Dashboard init -->
   <script src="{{ asset('public/build/assets/js/pages/dashboard-ecommerce.init.js') }}"></script>

   <!-- apexcharts -->
   <script src="{{ asset('public/build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
   <script src="//cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.0/dayjs.min.js') }}"></script>
   <script src="//cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.0/plugin/quarterOfYear.min.js') }}"></script>

   <!-- mixed charts init -->
   <script src="{{ asset('public/build/assets/js/pages/apexcharts-mixed.init.js') }}"></script>

   <!-- apexcharts init -->
   <script src="{{ asset('public/build/assets/js/pages/apexcharts-column.init.js') }}"></script>
@endsection
