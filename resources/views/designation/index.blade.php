@extends('layouts.app')

@section('page-css')
    <style>
        .hr-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .hr-suite .hero-shell,
        .hr-suite .table-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .hr-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid #dbeafe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .hr-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .hr-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .hr-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .hr-suite .toolbar-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 14px;
        }

        .hr-suite .filter-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1rem;
        }

        .hr-suite .filter-label {
            display: block;
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .hr-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .hr-suite .table-wrap table {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
<div class="page-content hr-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Organization Setup</span>
                                <h1 class="mb-3">Designations</h1>
                                <p class="text-muted mb-0">Keep job titles and department mapping organized in the same refined list shell as the rest of the admin area.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('designations.index') }}">Designation</a></li>
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
                        <span class="label">Roles</span>
                        <h3>{{ number_format($designation_list->count() ?? 0) }}</h3>
                        <p class="text-muted mb-0 mt-2">Designation records currently mapped to your department structure.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Module</span>
                        <h3>Designations</h3>
                        <p class="text-muted mb-0 mt-2">Maintain role titles and their department relationships from one list view.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">View</span>
                        <h3>Searchable</h3>
                        <p class="text-muted mb-0 mt-2">Find role titles faster from the same structured HR setup shell.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Mapping</span>
                        <h3>Department Linked</h3>
                        <p class="text-muted mb-0 mt-2">Keep titles and department hierarchy visually grouped in one workspace.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card table-shell">
                    <div class="card-header">
                        <div class="toolbar-shell d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h5 class="card-title mb-1">Designation List</h5>
                                <p class="text-muted mb-0">Manage role titles and their department relationships from the same polished HR shell.</p>
                            </div>
                            @can('create designation')
                            <a href="{{ route('designations.create') }}" class="btn btn-sm btn-primary" id="addproduct-btn">
                                <i class="ri-add-line align-bottom me-1"></i> Add Designation
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="filter-shell mb-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6 col-xl-4">
                                    <label class="filter-label" for="designation-search">Search</label>
                                    <input type="text" class="form-control" id="designation-search" placeholder="Search designation or department...">
                                </div>
                            </div>
                        </div>
                        {{-- <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Sr No.</th>
                                    <th data-ordering="false">Name</th>
                                    <th data-ordering="false">Department Name</th>
                                    <th style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($designation_list as $list)
                                <tr class="main-row">
                                    <td>{{ $list['id'] }}</td>
                                    <td>
                                        <h6>{{ $list['name'] }}</h6>
                                    </td>
                                    <td>{{ $list->departments->name ?? '' }}</td>
                                    <td>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a href="{{ route('designations.edit',$list['id']) }}" class="dropdown-item edit-item-btn"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>
                                                <li>
                                                    <a class="dropdown-item remove-item-btn"
                                                    data-delete-popup="true"
                                                    data-bs-original-title="You are about to delete a Designation ?"  data-bs-original-description="Deleting your Designation will remove all of your information from our database."
                                                    data-original-title=""
                                                    data-url="{{ route('designations.delete',[$list['id']]) }}"
                                                    data-method="DELETE"
                                                    data-cb="afterDelete"
                                                    href="javascript:void(0)">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table> --}}

                        <div class="table-responsive table-wrap">
                        <table id="designationList" class="table table-bordered dt-responsive nowrap table-striped align-middle mb-0" style="width:100%">
                            <thead>

                                <tr>
                                    <th data-ordering="false" style="width: 50px">Sr No</th>
                                    <th data-ordering="false" style="width: 100px">Name</th>
                                    <th data-ordering="false">Department Name</th>
                                    <th style="width: 50px">Action</th>
                                </tr>

                            </thead>
                            <tbody></tbody>
                        </table>
                        </div>

                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

    </div>
    <!-- container-fluid -->
</div>
@endsection

@section('scripts')
     <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>

@endsection

@section('page-script')
<script>
$(document).ready(function ()
{

    var table = $('#designationList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('designations.index') }}",
            data: function (d) {
            }
        },
        columns: [
            {  data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false },
            { data: 'name', name: 'name' },
            { data: 'department_id', name: 'department_id' },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });

    $('#designation-search').on('keyup change', function () {
        table.search($(this).val()).draw();
    });
});
</script>
@endsection
