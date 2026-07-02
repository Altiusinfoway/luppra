@extends('layouts.app')

@section('content')
<style>
    .flatpickr-months .flatpickr-month
    {
        background: white;
    }
</style>
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Location</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Location</a></li>
                            {{-- <li class="breadcrumb-item active">List</li> --}}
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


                      <div class="d-flex flex-wrap align-items-center gap-2 col-12">
                            <input type="text"
                                id="date_range"
                                class="form-control"
                                style="max-width: 250px"
                                placeholder="Select Date Range"
                                readonly>

                            <button type="button"
                                    id="loadBtn"
                                    class="btn btn-success">
                                Load
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                        <div id="map" style="height: 500px; width: 100%;"></div>
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
