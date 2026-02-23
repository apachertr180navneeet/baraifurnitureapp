@extends('admin.layouts.app')
@section('style')
<style>
    .dashboard-card {
        border: 1px solid #e6e6e6;
        border-radius: 10px;
        height: 100%;
    }
    .dashboard-card h6 {
        margin-bottom: 14px;
        font-weight: 600;
    }
    .dashboard-stat {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 14px;
    }
    .dashboard-link-card {
        text-decoration: none;
        color: inherit;
        display: block;
    }
</style>
@endsection  
@section('content')
<!-- Content -->
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6>Category</h6>
                    <div class="dashboard-stat"><span>Total</span><strong>{{ $dashboardCounts['category']['total'] }}</strong></div>
                    <div class="dashboard-stat"><span>Active</span><strong>{{ $dashboardCounts['category']['active'] }}</strong></div>
                    <div class="dashboard-stat mb-0"><span>In-active</span><strong>{{ $dashboardCounts['category']['inactive'] }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6>Product</h6>
                    <div class="dashboard-stat"><span>Total</span><strong>{{ $dashboardCounts['product']['total'] }}</strong></div>
                    <div class="dashboard-stat"><span>Active</span><strong>{{ $dashboardCounts['product']['active'] }}</strong></div>
                    <div class="dashboard-stat mb-0"><span>In-active</span><strong>{{ $dashboardCounts['product']['inactive'] }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4 mb-4">
            <a href="{{ route('admin.product.index', ['stock' => 'out_of_stock']) }}" class="dashboard-link-card">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h6>Out of stocks products</h6>
                        <div class="dashboard-stat mb-0"><span>Count</span><strong>{{ $dashboardCounts['out_of_stock_products'] }}</strong></div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6>Order</h6>
                    <div class="dashboard-stat"><span>Total</span><strong>{{ $dashboardCounts['order']['total'] }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6>Banner Management</h6>
                    <div class="dashboard-stat"><span>Total</span><strong>{{ $dashboardCounts['banner']['total'] }}</strong></div>
                    <div class="dashboard-stat"><span>Active</span><strong>{{ $dashboardCounts['banner']['active'] }}</strong></div>
                    <div class="dashboard-stat mb-0"><span>In-active</span><strong>{{ $dashboardCounts['banner']['inactive'] }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h6>Customize Orders</h6>
                    <div class="dashboard-stat"><span>Total</span><strong>{{ $dashboardCounts['customize_order']['total'] }}</strong></div>
                    <div class="dashboard-stat"><span>Pending</span><strong>{{ $dashboardCounts['customize_order']['pending'] }}</strong></div>
                    <div class="dashboard-stat mb-0"><span>Complete</span><strong>{{ $dashboardCounts['customize_order']['complete'] }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Content -->
@endsection
@section('script')
@endsection
