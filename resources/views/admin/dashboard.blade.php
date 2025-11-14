@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Monitor your app performance and user activity')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users Card -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-transform duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 backdrop-blur-lg p-3 rounded-xl">
                <x-heroicon-o-users class="w-8 h-8"/>
            </div>
            <div class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                Total
            </div>
        </div>
        <div>
            <p class="text-white/80 text-sm font-medium mb-1">Total Users</p>
            <p class="text-4xl font-bold">{{ number_format($stats['total_users']) }}</p>
            <div class="mt-3 flex items-center text-sm">
                <x-heroicon-o-arrow-trending-up class="w-4 h-4 mr-1"/>
                <span class="text-white/90">All registered users</span>
            </div>
        </div>
    </div>

    <!-- Active Today Card -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-transform duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 backdrop-blur-lg p-3 rounded-xl">
                <x-heroicon-o-signal class="w-8 h-8"/>
            </div>
            <div class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                24h
            </div>
        </div>
        <div>
            <p class="text-white/80 text-sm font-medium mb-1">Active Today</p>
            <p class="text-4xl font-bold">{{ number_format($stats['active_users_today']) }}</p>
            <div class="mt-3 flex items-center text-sm">
                <x-heroicon-o-clock class="w-4 h-4 mr-1"/>
                <span class="text-white/90">Last 24 hours</span>
            </div>
        </div>
    </div>

    <!-- Total Matches Card -->
    <div class="bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-transform duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 backdrop-blur-lg p-3 rounded-xl">
                <x-heroicon-o-heart class="w-8 h-8"/>
            </div>
            <div class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                Matches
            </div>
        </div>
        <div>
            <p class="text-white/80 text-sm font-medium mb-1">Total Matches</p>
            <p class="text-4xl font-bold">{{ number_format($stats['total_matches']) }}</p>
            <div class="mt-3 flex items-center text-sm">
                <x-heroicon-o-sparkles class="w-4 h-4 mr-1"/>
                <span class="text-white/90">Successful connections</span>
            </div>
        </div>
    </div>

    <!-- Pending Reports Card -->
    <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-transform duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 backdrop-blur-lg p-3 rounded-xl">
                <x-heroicon-o-flag class="w-8 h-8"/>
            </div>
            <div class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                Pending
            </div>
        </div>
        <div>
            <p class="text-white/80 text-sm font-medium mb-1">Pending Reports</p>
            <p class="text-4xl font-bold">{{ number_format($stats['pending_reports']) }}</p>
            <div class="mt-3 flex items-center text-sm">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 mr-1"/>
                <span class="text-white/90">Needs attention</span>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- User Registrations Chart -->
    <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <x-heroicon-o-user-plus class="w-6 h-6 mr-2 text-blue-500"/>
                    User Registrations
                </h3>
                <p class="text-sm text-gray-500 mt-1">Last 30 days performance</p>
            </div>
            <div class="bg-blue-50 p-3 rounded-xl">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-blue-600"/>
            </div>
        </div>
        <div id="userRegistrationsChart"></div>
    </div>

    <!-- Matches Chart -->
    <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <x-heroicon-o-heart class="w-6 h-6 mr-2 text-pink-500"/>
                    Matches Created
                </h3>
                <p class="text-sm text-gray-500 mt-1">Last 30 days activity</p>
            </div>
            <div class="bg-pink-50 p-3 rounded-xl">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-pink-600"/>
            </div>
        </div>
        <div id="matchesChart"></div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('admin.users.index') }}" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition-all duration-200 border border-gray-100 group">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-lg font-bold text-gray-800 mb-2">Manage Users</h4>
                <p class="text-sm text-gray-600">View and manage all users</p>
            </div>
            <div class="bg-blue-50 p-3 rounded-xl group-hover:bg-blue-100 transition-colors">
                <x-heroicon-o-users class="w-8 h-8 text-blue-600"/>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.reports.index') }}" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition-all duration-200 border border-gray-100 group">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-lg font-bold text-gray-800 mb-2">Review Reports</h4>
                <p class="text-sm text-gray-600">Check pending reports</p>
            </div>
            <div class="bg-orange-50 p-3 rounded-xl group-hover:bg-orange-100 transition-colors">
                <x-heroicon-o-flag class="w-8 h-8 text-orange-600"/>
            </div>
        </div>
    </a>

    <a href="{{ route('admin.map.index') }}" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition-all duration-200 border border-gray-100 group">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-lg font-bold text-gray-800 mb-2">Live Map</h4>
                <p class="text-sm text-gray-600">View active users location</p>
            </div>
            <div class="bg-green-50 p-3 rounded-xl group-hover:bg-green-100 transition-colors">
                <x-heroicon-o-map class="w-8 h-8 text-green-600"/>
            </div>
        </div>
    </a>
</div>
@endsection

@push('scripts')
<script>
    // User Registrations Chart
    const userRegistrationsOptions = {
        series: [{
            name: 'Registrations',
            data: @json($userRegistrations->pluck('count'))
        }],
        chart: {
            type: 'area',
            height: 300,
            toolbar: { show: false },
            fontFamily: 'Inter, sans-serif',
        },
        colors: ['#3B82F6'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.2,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: @json($userRegistrations->pluck('date')),
            labels: {
                style: {
                    colors: '#64748B',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748B',
                    fontSize: '12px'
                }
            }
        },
        grid: {
            borderColor: '#E2E8F0',
            strokeDashArray: 5,
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function(val) {
                    return val + ' users'
                }
            }
        }
    };
    const userRegistrationsChart = new ApexCharts(document.querySelector("#userRegistrationsChart"), userRegistrationsOptions);
    userRegistrationsChart.render();

    // Matches Chart
    const matchesOptions = {
        series: [{
            name: 'Matches',
            data: @json($matchesPerDay->pluck('count'))
        }],
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false },
            fontFamily: 'Inter, sans-serif',
        },
        plotOptions: {
            bar: {
                borderRadius: 8,
                columnWidth: '60%',
                distributed: false,
            }
        },
        colors: ['#EC4899'],
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: @json($matchesPerDay->pluck('date')),
            labels: {
                style: {
                    colors: '#64748B',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748B',
                    fontSize: '12px'
                }
            }
        },
        grid: {
            borderColor: '#E2E8F0',
            strokeDashArray: 5,
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function(val) {
                    return val + ' matches'
                }
            }
        }
    };
    const matchesChart = new ApexCharts(document.querySelector("#matchesChart"), matchesOptions);
    matchesChart.render();
</script>
@endpush
