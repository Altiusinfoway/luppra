<footer class="footer">
    @php
        $website_nm = \App\Models\Utility::getWebsiteName();
    @endphp
    <div class="container-fluid">
        <div class="row align-items-center gy-2">
            <div class="col-sm-6">
                <div class="small text-muted">Commerce operations dashboard</div>
            </div>
            <div class="col-sm-6">
                <div class="text-sm-end small">
                    <script>
                        document.write(new Date().getFullYear())
                    </script> &copy; {{ $website_nm ?? '' }}
                </div>
            </div>
        </div>
    </div>
</footer>
