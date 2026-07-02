@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Device Section</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('device.index') }}">Device</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">


                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Device List</h5>
                            @if (count($devices) <= 0)
                                <a href="{{ route('device.create') }}" class="btn btn-success" id="addproduct-btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Add Device
                                </a>
                            @endif
                        </div>
                        <div class="card-body">

                            @if (count($devices ?? []) > 0)
                                <div class="row">
                                    @foreach ($devices ?? [] as $device)
                                        <div class="col-xl-3">
                                            <div class="card  border">
                                                <!-- Card body -->
                                                <div class="card-body">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="2" class="text-center">
                                                                    <h3 class=" text-uppercase text-muted">
                                                                        <i class="ri-whatsapp-line"></i> {{ $device->name }}
                                                                    </h3>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <th>Phone</th>
                                                                <td>
                                                                    @if (!empty($device->phone))
                                                                        <a href="{{ route('device.scan', $device->uuid) }}">
                                                                            {{ $device->phone }}
                                                                        </a>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Total Messages</th>
                                                                <td>{{ number_format($device->smstransaction_count) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Status</th>
                                                                <td>
                                                                    <a class="text-nowrap  font-weight-600"
                                                                        href="{{ route('device.scan', $device->uuid) }}">
                                                                        @if ($device->status == 1)
                                                                            <span class="badge badge-sm bg-success">
                                                                                {{-- {{ badge($device->status)['class'] }} --}}
                                                                                Active
                                                                            </span>
                                                                        @else
                                                                            <span class="badge badge-sm bg-danger">
                                                                                InActive
                                                                            </span>
                                                                        @endif

                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Actions</th>
                                                                <td>
                                                                    <div class="d-flex gap-2">
                                                                        <a class="btn btn-sm btn-outline-primary"
                                                                            href="{{ route('device.scan', $device->uuid) }}">
                                                                            QR
                                                                        </a>
                                                                        <a class="btn btn-sm btn-success js-wa-chat-entry"
                                                                            href="{{ url('device/chats/'.$device->uuid) }}"
                                                                            data-chat-url="{{ url('device/chats/'.$device->uuid) }}"
                                                                            data-qr-url="{{ route('device.scan', $device->uuid) }}"
                                                                            data-device-uuid="{{ $device->uuid }}">
                                                                            Open Chat
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                            @endif

                        </div>
                    </div>
                </div>

                <!-- Varying Modal Content -->
                {{-- <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Device List</h5>
                        <a href="{{ route('device.create') }}" class="btn btn-success" id="addproduct-btn">
                            <i class="ri-add-line align-bottom me-1"></i> Add Device
                        </a>
                    </div>
                    <div class="card-body">
                        <table id="departmentList" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-ordering="false">Sr No</th>
                                    <th data-ordering="false">Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                    </div>
                </div>
                </div><!--end col--> --}}
            </div>

        </div>
        <!-- container-fluid -->
    </div>

    <div class="">
        <input type="hidden" id="base_url" value="{{ url('/') }}" class="col-md-12">
    </div>
@endsection

@section('scripts')
    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="{{ asset('public/build/assets/js/pages/user/device.js') }}"></script>
    <script src="{{ asset('public/build/assets/js/pages/user/whatsapp-chat-entry.js') }}"></script>
@endsection

@section('page-script')
    <script>
        /*
                                                                            $(document).ready(function ()
                                                                            {
                                                                                var table = $('#departmentList').DataTable({
                                                                                    processing: true,
                                                                                    serverSide: true,
                                                                                    ajax: {
                                                                                        url: "{{ route('departments.index') }}",
                                                                                        data: function (d) {
                                                                                        }
                                                                                    },
                                                                                    columns: [
                                                                                        { data: 'id', name: 'id' },
                                                                                        { data: 'name', name: 'name' },
                                                                                        {
                                                                                            data: 'action',
                                                                                            name: 'action',
                                                                                            orderable: false,
                                                                                            searchable: false
                                                                                        }
                                                                                    ]
                                                                                });
                                                                            });
                                                                            */
    </script>
@endsection
