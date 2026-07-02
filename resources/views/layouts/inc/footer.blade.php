<footer class="footer">
    @php
        $website_nm = \App\Models\Utility::getWebsiteName();
    @endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">

            </div>
            <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">
                    <script>
                        document.write(new Date().getFullYear())
                    </script> © {{ $website_nm ?? '' }}
                </div>
            </div>
        </div>
    </div>
</footer>
