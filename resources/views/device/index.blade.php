@extends('layouts.app')

@section('page-css')
    <style>
        .device-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .device-suite .hero-shell,
        .device-suite .shell-card,
        .device-suite .device-card {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .device-suite .hero-shell {
            background:
                radial-gradient(circle at top right, rgba(34, 197, 94, 0.14), transparent 30%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .device-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid #bbf7d0;
            background: rgba(255, 255, 255, 0.86);
            color: #15803d;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .device-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .device-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .device-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .device-suite .toolbar-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 14px 16px;
        }

        .device-suite .search-shell {
            position: relative;
            min-width: min(100%, 300px);
        }

        .device-suite .search-shell .form-control {
            min-height: 44px;
            padding-left: 2.7rem;
            border-radius: 14px;
            border-color: #cbd5e1;
            background: #fff;
        }

        .device-suite .search-shell .search-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
        }

        .device-suite .device-card {
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .device-suite .device-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 42px rgba(15, 23, 42, 0.08);
        }

        .device-suite .device-grid {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #fff;
            padding: 1rem;
        }

        .device-suite .device-card table {
            margin-bottom: 0;
        }

        .device-suite .device-card thead th {
            border-bottom-color: #e2e8f0;
        }

        .device-suite .empty-state {
            border: 1px dashed #cbd5e1;
            border-radius: 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            padding: 2rem;
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="page-content device-suite">
        <div class="container-fluid">
            @php
                $deviceCollection = collect($devices ?? []);
                $activeDeviceCount = $deviceCollection->where('status', 1)->count();
                $totalMessageCount = $deviceCollection->sum('smstransaction_count');
            @endphp
            <div class="row">
                <div class="col-12">
                    <div class="card hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Whatsapp Devices</span>
                                    <h2 class="mt-3 mb-2">Device Section</h2>
                                    <p class="text-muted mb-0">See connected devices, monitor message volume, and jump into QR pairing or chats from a cleaner communication dashboard.</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="{{ route('device.index') }}">Device</a></li>
                                            <li class="breadcrumb-item active">List</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Communication</span>
                            <h3>{{ number_format($deviceCollection->count()) }}</h3>
                            <p class="text-muted mb-0 mt-2">Connected WhatsApp devices currently available for chat and message flows.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Workflow</span>
                            <h3>QR + Chat</h3>
                            <p class="text-muted mb-0 mt-2">Pair new devices and jump into chat from a cleaner communications workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Active Devices</span>
                            <h3>{{ number_format($activeDeviceCount) }}</h3>
                            <p class="text-muted mb-0 mt-2">Live devices currently ready for QR sync, message delivery, and chat continuity.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Messages</span>
                            <h3>{{ number_format($totalMessageCount) }}</h3>
                            <p class="text-muted mb-0 mt-2">Quick volume snapshot across all configured devices in the communication workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shell-card">
                        <div class="card-header">
                            <div class="toolbar-shell d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <h5 class="card-title mb-1">Device List</h5>
                                    <p class="text-muted mb-0">Search device cards, inspect live status, and jump into QR or chat actions from one compact communications control bar.</p>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="search-shell">
                                        <i class="ri-search-line search-icon"></i>
                                        <input type="text" id="device-search" class="form-control" placeholder="Search device name or phone">
                                    </div>
                                    @if ($deviceCollection->count() <= 0)
                                        <a href="{{ route('device.create') }}" class="btn btn-primary" id="addproduct-btn">
                                            <i class="ri-add-line align-bottom me-1"></i> Add Device
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            @if ($deviceCollection->count() > 0)
                                <div class="device-grid">
                                    <div class="row" id="device-card-grid">
                                        @foreach ($deviceCollection as $device)
                                            <div class="col-xl-3 col-md-6 device-search-item mb-4"
                                                data-search="{{ strtolower(trim(($device->name ?? '').' '.($device->phone ?? ''))) }}">
                                            <div class="card device-card h-100">
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
                                    </div>
                                </div>
                            @else
                                <div class="empty-state">
                                    <h5 class="mb-2">No Devices Added Yet</h5>
                                    <p class="text-muted mb-3">Add your first WhatsApp device to start QR pairing, messaging, and chat operations from this dashboard.</p>
                                    <a href="{{ route('device.create') }}" class="btn btn-primary">
                                        <i class="ri-add-line align-bottom me-1"></i> Add Device
                                    </a>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
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
        $(document).ready(function () {
            $('#device-search').on('keyup change', function () {
                const keyword = ($(this).val() || '').toLowerCase().trim();

                $('.device-search-item').each(function () {
                    const haystack = ($(this).data('search') || '').toString();
                    $(this).toggle(haystack.indexOf(keyword) !== -1);
                });
            });
        });

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
