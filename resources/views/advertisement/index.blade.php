@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Advertisement Section</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('advertisements.index') }}">Advertisement</a></li>
                            <li class="breadcrumb-item active">List</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">

            <!-- Varying Modal Content -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Advertisement List</h5>
                        @can('create advertisement')
                        <a href="{{ route('advertisements.create') }}" class="btn btn-success" id="addproduct-btn">
                            <i class="ri-add-line align-bottom me-1"></i> Add Advertisement
                        </a>
                        @endcan
                    </div>
                    <div class="card-body">
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
            </div><!--end col-->
        </div><!--end row-->

    </div>
    <!-- container-fluid -->
</div>
@endsection
