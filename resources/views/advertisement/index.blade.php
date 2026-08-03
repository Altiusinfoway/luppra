@extends('layouts.app')

@section('page-css')
<style>
.advertisement-suite {
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
}

.advertisement-suite .hero-shell,
.advertisement-suite .shell-card {
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.advertisement-suite .hero-shell {
    background:
        radial-gradient(circle at top right, rgba(251, 191, 36, 0.18), transparent 30%),
        radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
        linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.advertisement-suite .hero-eyebrow {
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

.advertisement-suite .summary-card {
    border: 1px solid rgba(255, 255, 255, 0.78);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.84);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.advertisement-suite .summary-card .label {
    display: block;
    margin-bottom: 8px;
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.advertisement-suite .summary-card h3 {
    margin: 0;
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #0f172a;
}

.advertisement-suite .toolbar-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #f8fafc;
    padding: 14px 16px;
}

.advertisement-suite .search-shell {
    position: relative;
    min-width: min(100%, 300px);
}

.advertisement-suite .search-shell .form-control {
    min-height: 44px;
    padding-left: 2.7rem;
    border-radius: 14px;
    border-color: #cbd5e1;
    background: #fff;
}

.advertisement-suite .search-shell .search-icon {
    position: absolute;
    top: 50%;
    left: 14px;
    transform: translateY(-50%);
    color: #64748b;
    pointer-events: none;
}

.advertisement-suite .table-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    background: #fff;
}

.advertisement-suite .table-wrap table {
    margin-bottom: 0;
}

.advertisement-suite .table-wrap thead th {
    background: #f8fafc !important;
}
</style>
@endsection

@section('content')
<div class="page-content advertisement-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Campaign Catalog</span>
                                <h2 class="mt-3 mb-2">Advertisement Section</h2>
                                <p class="text-muted mb-0">Review advertisement entries and keep marketing costs organized with the same lighter card-and-toolbar layout.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('advertisements.index') }}">Advertisement</a></li>
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
                        <span class="label">Campaigns</span>
                        <h3>{{ number_format(count($advertisement_list ?? [])) }}</h3>
                        <p class="text-muted mb-0 mt-2">Saved marketing entries now sit inside the same cleaner dashboard rhythm as the rest of the refreshed app.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Spend View</span>
                        <h3>Cost Ledger</h3>
                        <p class="text-muted mb-0 mt-2">Keep campaign names and spend figures easier to scan before drilling into edits or cleanup actions.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card shell-card">
                    <div class="card-header">
                        <div class="toolbar-shell d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="card-title mb-1">Advertisement List</h5>
                                <p class="text-muted mb-0">Search campaigns and manage spend records from one compact control bar.</p>
                            </div>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="search-shell">
                                    <i class="ri-search-line search-icon"></i>
                                    <input type="text" id="advertisement-search" class="form-control" placeholder="Search advertisement">
                                </div>
                                @can('create advertisement')
                                <a href="{{ route('advertisements.create') }}" class="btn btn-primary" id="addproduct-btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Add Advertisement
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-wrap">
                            <table id="example" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Sr No.</th>
                                        <th data-ordering="false">Name</th>
                                        <th data-ordering="false">Amount</th>
                                        <th style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($advertisement_list as $list)
                                    <tr class="main-row">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <h6>{{ $list['name'] }}</h6>
                                        </td>
                                        <td>{{ $list['amount'] }}</td>
                                        <td>
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-fill align-middle"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @can('edit advertisement')
                                                    <li><a href="{{ route('advertisements.edit',$list['id']) }}" class="dropdown-item edit-item-btn"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>
                                                    @endcan

                                                    @can('delete advertisement')
                                                    <li>
                                                        <a class="dropdown-item remove-item-btn"
                                                        data-delete-popup="true"
                                                        data-bs-original-title="You are about to delete a Advertisement ?"  data-bs-original-description="Deleting your Advertisement will remove all of your information from our database."
                                                        data-original-title=""
                                                        data-url="{{ route('advertisements.delete',[$list['id']]) }}"
                                                        data-method="DELETE"
                                                        data-cb="afterDelete"
                                                        href="javascript:void(0)">
                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                        </a>
                                                    </li>
                                                    @endcan

                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
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

@section('page-script')
<script>
    $(document).ready(function () {
        $('#advertisement-search').on('keyup change', function () {
            const keyword = $(this).val().toLowerCase();

            $('#example tbody tr').each(function () {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.indexOf(keyword) !== -1);
            });
        });
    });
</script>
@endsection
