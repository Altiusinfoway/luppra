 <div class="modal fade" id="indiamartModel" tabindex="-1" aria-labelledby="indiamartModel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-info-subtle">
                    <h5 class="modal-title" id="ina">Indiamart</h5>
                    <button type="button" class="btn-close" id="addBoardBtn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                 <form method="post" action="{{ route('leads.india_mart_import') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="mt-4">
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success">Import leads from India Mart</button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="facebookModel" tabindex="-1" aria-labelledby="facebookModel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-info-subtle">
                    {{-- <h5 class="modal-title" id="ina">FaceBook</h5> --}}
                    <button type="button" class="btn-close" id="addBoardBtn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('leads.upload_fb_lead') }}">
                @csrf

                <div class="modal-body">
                    <div class="row">
                        <div class="mt-4">
                            <div class="hstack gap-2 justify-content-end">
                                <h4> comming soon</h4>
                                {{-- <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button> --}}
                                {{-- <button type="submit" class="btn btn-success">Import leads from Facebook</button> --}}
                            </div>
                        </div>
                    </div>
                </div>
                </form>

            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadModel" tabindex="-1" aria-labelledby="uploadModel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-info-subtle">
                    <h5 class="modal-title" id="ina">Upload Data</h5>
                    <button type="button" class="btn-close" id="addBoardBtn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="col-md-12">
                    <a class="download-link float-end" href="{{ asset('public/sample/lead_sample.xlsx') }}" download>Download Sample.xlsx</a>
                    </div>
                    <form id="uploadExcelForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row container">
                                    <label>Excel File Upload </label>
                                    <input type="file" name="excel_file" class="form-control mt-1" required>
                        </div>
                        <div class="row">
                            <div class="mt-4">
                                <div class="hstack gap-2 justify-content-end">
                                    <button type="button" class="btn btn-light"
                                        data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success" >Save</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div id="uploadMessage" class="mt-2 text-danger fw-bold"></div>
                </div>


            </div>
        </div>
    </div>
