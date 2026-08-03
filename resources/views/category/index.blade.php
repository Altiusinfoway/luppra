@extends('layouts.app')

@section('page-css')
    <style>
        .category-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .category-suite .hero-shell,
        .category-suite .shell-card {
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .category-suite .hero-shell {
            background:
                radial-gradient(circle at top right, rgba(251, 191, 36, 0.18), transparent 30%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .category-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid #fde68a;
            background: rgba(255, 255, 255, 0.86);
            color: #b45309;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .category-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .category-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .category-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .category-suite .toolbar-shell {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
            padding: 14px;
        }

        .category-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .category-suite .table-wrap table {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="page-content category-suite">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card hero-shell mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-7">
                                    <span class="hero-eyebrow">Catalog Structure</span>
                                    <h2 class="mt-3 mb-2">Categories</h2>
                                    <p class="text-muted mb-0">Organize your catalog hierarchy with a lighter list layout that matches the refreshed product and admin experience.</p>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Categories</a></li>
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
                            <span class="label">Taxonomy</span>
                            <h3>Categories</h3>
                            <p class="text-muted mb-0 mt-2">Maintain your catalog hierarchy from the same modern admin surface.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="card summary-card h-100">
                        <div class="card-body">
                            <span class="label">Structure</span>
                            <h3>Parent + Child</h3>
                            <p class="text-muted mb-0 mt-2">Track nested category relationships in one cleaner list workspace.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shell-card">

                        <div class="card-header">
                            <div class="toolbar-shell d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <h5 class="card-title mb-1">Category List</h5>
                                    <p class="text-muted mb-0">Manage product grouping and parent-child structure from the same polished catalog shell.</p>
                                </div>

                                @can('create category')
                                    <div>
                                        <a href="javascript:void(0);" class="btn btn-primary" data-size="lg"
                                            data-url="{{ route('category.create') }}" data-ajax-popup="true"
                                            data-bs-original-title="{{ __('Add Category') }}"><i
                                                class="ri-add-line align-bottom me-1"></i> Add Category</a>
                                    </div>
                                @endcan
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive table-wrap">

                            <table id="categoryList"
                                class="table table-bordered dt-responsive nowrap table-striped align-middle"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Category Name</th>
                                        <th>Parent Category</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div><!--end col-->
            </div>
        </div>
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
        $(document).ready(function() {
            $('#categoryList').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('category.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'parent_name',
                        name: 'parent.name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
    </script>
@endsection
