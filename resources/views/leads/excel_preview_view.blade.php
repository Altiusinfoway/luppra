@extends('layouts.app')
@section('content')
    <div class="page-content">
        <div class="container-fluid">


            <div class="row">

                <!-- Varying Modal Content -->
                <div class="col-lg-12">
                    <div class="card">
                        <h5 class="card-title m-2">Excel File Preview</h5>

                        <div class="card-body">

                            @foreach ($sheets as $sheetIndex => $sheet)
                                <h4>Sheet {{ $sheetIndex + 1 }}</h4>
                                @php
                                    $headers = $sheet[0] ?? [];
                                    $rows = array_slice($sheet, 1);
                                @endphp

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            @foreach ($headers as $head)
                                                <th>{{ $head }}</th>
                                            @endforeach
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rows as $i => $row)
                                            <tr>
                                                @foreach ($row as $val)
                                                    <td>{{ $val }}</td>
                                                @endforeach
                                                <td class="text-danger fw-bold">
                                                    {{ $messages[$i] ?? '' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endforeach

                            <form id="leadUploadForm">
                                @csrf
                                <input type="hidden" name="leads_data" value="{{ json_encode($sheets) }}">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </form>

                        </div>
                    </div>
                </div><!--end col-->
            </div>

        </div>
    </div>

@endsection

@section('page-script')
<script>
   $('#leadUploadForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const leadsData = form.find('[name="leads_data"]').val();

    $.ajax({
        url: "{{ route('leads.upload') }}",
        method: 'POST',
        data: {
            leads_data: leadsData,
            _token: '{{ csrf_token() }}'
        },
        success: function(res) {
            show_toastr('success', 'Upload in progress');

            let checkJob = setInterval(() => {
                $.get(`{{ route('leads.job.status') }}?job_key=${res.job_key}`, function(data) {
                    console.log('ddd',res.job_key);
                    if (data.status == 'completed') {
                        show_toastr('success', 'Leads saved successfully');
                        clearInterval(checkJob);

                    }
                });
            }, 3000);
        },
        error: function(err) {
            show_toastr('error', 'Upload failed');
        }
    });
});
</script>
@endsection
