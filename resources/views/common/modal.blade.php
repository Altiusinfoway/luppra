<!-- Modal  Start -->
<div class="modal fade" id="commonModal" tabindex="-1" aria-labelledby="commonModal"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-3 bg-info-subtle">
                <h5 class="modal-title" ></h5>
                <button type="button" class="btn-close" id="commonModalBtn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="common-modal-body p-3"></div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade zoomIn" id="deleteRecordModal" tabindex="-1" aria-labelledby="deleteRecordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close" id="btn-close"></button>
            </div>
            <div class="modal-body p-5 text-center">
                <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json"
                    trigger="loop" colors="primary:#405189,secondary:#f06548"
                    style="width:90px;height:90px"></lord-icon>
                <div class="mt-4 text-center">

                    <h4 class="fs-semibold del-modal-title"></h4>
                    <p class="text-muted fs-14 mb-4 pt-1 del-modal-description"></p>

                    <div class="hstack gap-2 justify-content-center remove">

                        <button
                            class="btn btn-link link-success fw-medium text-decoration-none material-shadow-none"
                            id="deleteRecord-close" data-bs-dismiss="modal"><i
                                class="ri-close-line me-1 align-middle"></i>
                            Close</button>
                        <button class="btn btn-danger" id="delete-record">Yes,
                            Delete It!!</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end modal -->

<!-- Modal -->
<div class="modal fade zoomIn" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close" id="btn-close"></button>
            </div>
            <div class="modal-body p-5 text-center">

                    <!-- <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json"
                    trigger="loop" colors="primary:#405189,secondary:#f06548"
                    style="width:90px;height:90px"></lord-icon> -->

                    <lord-icon
                        src="https://cdn.lordicon.com/lltgvngb.json"
                        trigger="loop"
                        colors="primary:#405189,secondary:#f06548"
                        style="width:90px;height:90px">
                    </lord-icon>
                <div class="mt-4 text-center">

                    <h4 class="fs-semibold confirm-modal-title">Are You Sure ?</h4>
                    <p class="text-muted fs-14 mb-4 pt-1 confirm-modal-description"></p>

                    <div class="hstack gap-2 justify-content-center remove">

                        <button
                            class="btn btn-link link-success fw-medium text-decoration-none material-shadow-none"
                            id="confirmation-close" data-bs-dismiss="modal"><i
                                class="ri-close-line me-1 align-middle"></i>
                            Close</button>
                        <button class="btn btn-danger" id="confirm-yes">Yes, I Want</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end modal -->

