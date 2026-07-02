@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Assign Roles Section</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Assign Roles</a></li>
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
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title  mb-0">Roles List</h5>
                                @can('create role')
                                    <div>
                                        <a href="{{ route('roles.create') }}" class="btn btn-sm btn-success" id="addproduct-btn"><i
                                                class="ri-add-line align-bottom me-1"></i> Add Role</a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card">
                                        <div class="card-body table-border-style">
                                            <div class="table-responsive">
                                                <table class="table datatable">
                                                    <thead class="bg-gradient bg-light">
                                                        <tr>
                                                            <th>{{ __('Role') }} </th>
                                                            <th>{{ __('Permissions') }} </th>
                                                            <th width="150">{{ __('Action') }} </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($roles as $role)
                                                            @if ($role->name != 'client' && !(Auth::user()->type == 'company' && $role->name == 'company'))
                                                                <tr class="font-style main-row">
                                                                    <td class="Role">{{ $role->name }}</td>
                                                                    <td class="Permission">
                                                                        <div class="row ">
                                                                            @foreach ($role->permissions()->pluck('name') as $permissionName)
                                                                                <div class="col-2">
                                                                                    <h5><span
                                                                                            class="badge rounded p-2 m-1 text-muted bg-light w-100 text-wrap">{{ $permissionName }}</span>
                                                                                    </h5>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </td>
                                                                    <td class="Action">
                                                                        <span>

                                                                            {{-- <div class="action-btn me-2">
                                                            <a href="{{ route('roles.edit', $role->id) }}"
                                                                class="mx-3 btn btn-sm align-items-center bg-info"
                                                                data-url="{{ route('roles.edit', $role->id) }}"
                                                                data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}"
                                                                data-title="{{ __('Role Edit') }}">
                                                                <i class="mdi mdi-pencil text-white"></i>
                                                            </a>
                                                        </div> --}}

                                                                            <div class="d-flex gap-2" style="width: 100px;">
                                                                                @can('edit role')
                                                                                    <div class="action-btn flex-fill">
                                                                                        <a href="{{ route('roles.edit', $role->id) }}"
                                                                                            class="btn btn-sm align-items-center bg-info text-white w-100">
                                                                                            <i
                                                                                                class="mdi mdi-pencil text-white me-1"></i>
                                                                                            Edit
                                                                                        </a>
                                                                                    </div>
                                                                                @endcan



                                                                                {{-- <div class="action-btn ">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['roles.destroy', $role->id],
                                                                    'id' => 'delete-form-' . $role->id,
                                                                ]) !!}
                                                                <a href="javascript:void(0);"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para bg-danger"
                                                                    data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i
                                                                        class="mdi mdi-delete text-white"></i></a>
                                                                {!! Form::close() !!}
                                                            </div> --}}
                                                                                {{-- @can('delete role')
                                                                                    <div class="action-btn flex-fill">
                                                                                        <a class="btn btn-sm align-items-center bs-pass-para bg-danger text-white w-100"
                                                                                            data-delete-popup="true"
                                                                                            data-bs-original-title="You are about to delete a Role?"
                                                                                            data-bs-original-description="Deleting this role will remove all associated permissions."
                                                                                            data-url="{{ route('roles.destroy', $role->id) }}"
                                                                                            data-cb="afterDelete"
                                                                                            data-method="DELETE"
                                                                                            href="javascript:void(0)">
                                                                                            <i
                                                                                                class="ri-delete-bin-fill align-bottom me-1 text-white"></i>
                                                                                            Delete
                                                                                        </a>
                                                                                    </div>
                                                                                @endcan --}}
                                                                            </div>


                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--end col-->
            </div><!--end row-->

            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Permission Activity History</h5>
                        </div>
                        <div class="card-body">
                            @include('activity._timeline', [
                                'activities' => $permissionActivityTimeline,
                                'emptyMessage' => 'No permission activity found.',
                            ])
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- container-fluid -->
    </div>
@endsection
