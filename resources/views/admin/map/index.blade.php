@extends('layouts.admin')

@section('title', 'Live Map')
@section('page-title', 'Live Map - Online Users')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="mb-4 flex justify-between items-center">
        <h3 class="text-lg font-semibold">Users Online (Last 30 minutes): <span id="userCount">{{ $users->count() }}</span></h3>
        <button onclick="refreshMap()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
            Refresh
        </button>
    </div>

    <div id="map" class="w-full h-[600px] rounded-lg"></div>
</div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}"></script>
<script>
    let map;
    let markers = [];

    function initMap() {
        // Initialize map centered on a default location
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 12,
            center: { lat: 48.8566, lng: 2.3522 } // Paris default
        });

        loadUsers();
    }

    function loadUsers() {
        fetch('{{ route("admin.map.users-data") }}')
            .then(response => response.json())
            .then(data => {
                // Clear existing markers
                markers.forEach(marker => marker.setMap(null));
                markers = [];

                // Update user count
                document.getElementById('userCount').textContent = data.users.length;

                // Add new markers
                data.users.forEach(user => {
                    const marker = new google.maps.Marker({
                        position: { lat: user.latitude, lng: user.longitude },
                        map: map,
                        title: user.name,
                        icon: {
                            url: user.gender === 'male' ? 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png' : 'http://maps.google.com/mapfiles/ms/icons/pink-dot.png',
                            scaledSize: new google.maps.Size(40, 40)
                        }
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="p-2">
                                <h4 class="font-bold">${user.name}</h4>
                                <p class="text-sm">Gender: ${user.gender}</p>
                                <p class="text-sm">Last active: ${user.last_active_at}</p>
                                <a href="/admin/users/${user.id}" class="text-blue-600 text-sm">View Profile</a>
                            </div>
                        `
                    });

                    marker.addListener('click', () => {
                        infoWindow.open(map, marker);
                    });

                    markers.push(marker);
                });

                // Fit bounds to show all markers
                if (data.users.length > 0) {
                    const bounds = new google.maps.LatLngBounds();
                    data.users.forEach(user => {
                        bounds.extend({ lat: user.latitude, lng: user.longitude });
                    });
                    map.fitBounds(bounds);
                }
            });
    }

    function refreshMap() {
        loadUsers();
    }

    // Initialize map on page load
    window.onload = initMap;

    // Auto-refresh every 30 seconds
    setInterval(refreshMap, 30000);
</script>
@endpush
