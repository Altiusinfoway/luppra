@extends('layouts.app')

@section('page-css')
<style>
    .map-suite {
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.72) 0%, rgba(245, 247, 251, 0) 100%);
    }

    .map-suite .hero-shell,
    .map-suite .shell-card {
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .map-suite .hero-shell {
        background:
            radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 30%),
            radial-gradient(circle at left center, rgba(16, 185, 129, 0.14), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .map-suite .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        background: rgba(255, 255, 255, 0.86);
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .map-suite .summary-card {
        border: 1px solid rgba(255, 255, 255, 0.78);
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }

    .map-suite .summary-card .label {
        display: block;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .map-suite .summary-card h3 {
        margin: 0;
        font-size: 1.7rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .map-suite .toolbar-shell {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 14px 16px;
    }

    .map-suite .search-shell {
        position: relative;
        min-width: min(100%, 260px);
    }

    .map-suite .search-shell .form-control {
        min-height: 44px;
        border-radius: 14px;
        border-color: #cbd5e1;
        background: #fff;
    }

    .map-suite .map-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
    }

    .flatpickr-months .flatpickr-month
    {
        background: white;
    }
</style>
@endsection

@section('content')
<div class="page-content map-suite">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-7">
                                <span class="hero-eyebrow">Location Tracking</span>
                                <h2 class="mt-3 mb-2">Location</h2>
                                <p class="text-muted mb-0">Load date-based field movement and review mapped routes from a cleaner location dashboard aligned with the refreshed admin UI.</p>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Location</a></li>
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
                        <span class="label">Tracking</span>
                        <h3>Route Replay</h3>
                        <p class="text-muted mb-0 mt-2">Replay field movement by date range with the same cleaner KPI-first dashboard language as the refreshed admin screens.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Mode</span>
                        <h3>Map + Path</h3>
                        <p class="text-muted mb-0 mt-2">Keep date filtering, route drawing, and stop-point review grouped inside one operational map workspace.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card shell-card">
                    <div class="card-header">
                        <div class="toolbar-shell d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="card-title mb-1">Location Overview</h5>
                                <p class="text-muted mb-0">Pick a date range and load a cleaned-up route view from one compact tracking toolbar.</p>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="search-shell">
                                    <input type="text"
                                        id="date_range"
                                        class="form-control"
                                        placeholder="Select Date Range"
                                        readonly>
                                </div>

                                <button type="button"
                                        id="loadBtn"
                                        class="btn btn-primary">
                                    Load Route
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="map-wrap">
                            <div id="map" style="height: 500px; width: 100%;"></div>
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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
let map;
let markers = [];
let polyline = null;

function markerStyle(index, total) {
    if (index === 0) {
        return {
            color: '#15803d',
            fillColor: '#22c55e',
            label: 'Start Location',
        };
    }

    if (index === total - 1) {
        return {
            color: '#b91c1c',
            fillColor: '#ef4444',
            label: 'End Location',
        };
    }

    return {
        color: '#1d4ed8',
        fillColor: '#3b82f6',
        label: `Point ${index + 1}`,
    };
}

document.addEventListener("DOMContentLoaded", function () {

    // Initialize map centered in India
    map = L.map('map').setView([22.2968, 70.7800], 6);

    // OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    // Flatpickr for date range
    flatpickr("#date_range", {
        mode: "range",
        dateFormat: "Y-m-d",
        maxDate: "today"
    });

    // Load button click
    document.getElementById("loadBtn").addEventListener("click", function () {
        let dateRange = document.getElementById("date_range").value;
        if (!dateRange) {
             show_toastr('error', 'Please select date range.');
            return;
        }

        let dates = dateRange.split(" to ");
        let start_date = dates[0];
        let end_date = dates[1] ?? dates[0];

        fetchLocations(start_date, end_date);
    });
});

// Fetch locations from server
function fetchLocations(start_date, end_date) {

    $.ajax({
        url: "{{ route('employees.map_locations', $user_id) }}",
        type: "GET",
        data: { start_date, end_date },
        success: function (res) {

            clearMap();

            if (!res.status || res.locations.length === 0) {
                show_toastr('error', res.message || 'No location data found.');
                return;
            }

            let path = [];

            res.locations.forEach((loc, index) => {
                let lat = parseFloat(loc.latitude);
                let lng = parseFloat(loc.longitude);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return;
                }

                let latlng = [lat, lng];
                path.push(latlng);

                const markerMeta = markerStyle(index, res.locations.length);

                let marker = L.circleMarker(latlng, {
                    radius: 8,
                    color: markerMeta.color,
                    weight: 2,
                    fillColor: markerMeta.fillColor,
                    fillOpacity: 0.9
                })
                    .bindPopup(`<b>${markerMeta.label}</b><br>Lat: ${lat}<br>Lng: ${lng}<br>Time: ${loc.date_time}`)
                    .addTo(map);

                markers.push(marker);
            });

            if (path.length === 0) {
                show_toastr('error', 'No valid coordinates found for the selected range.');
                return;
            }

            const linePath = Array.isArray(res.route_geometry) && res.route_geometry.length > 1
                ? res.route_geometry
                : path;

            // Draw polyline
            polyline = L.polyline(linePath, { color: 'red', weight: 4 }).addTo(map);
            map.fitBounds(polyline.getBounds());

            if (res.route_mode === 'direct') {
                console.info('Road routing not available, using direct GPS trace.');
            }
        },
        error: function () {
            clearMap();
            show_toastr('error', 'Unable to load map locations right now.');
        }
    });
}

// Clear previous markers and polyline
function clearMap() {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    if (polyline) {
        map.removeLayer(polyline);
        polyline = null;
    }
}

</script>

@endsection
