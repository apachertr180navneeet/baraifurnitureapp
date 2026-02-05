@extends('admin.layouts.app')
@section('style')
<style>
    .badge-status { cursor: pointer; }
</style>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Orders</span>
            </h5>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="orderTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Order Date</th>
                                    <th>Product</th>
                                    <th>Customer</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {

    // DataTable
    const table = $('#orderTable').DataTable({
        processing: true,
        ajax: '{{ route("admin.order.getall") }}',
        columns: [
            { data: 'id' },
            { data: 'order_date' },
            { data: 'product.name', defaultContent: '' },
            { data: 'customer.full_name', defaultContent: '' },
            { data: 'price' },
            {
                data: 'status',
                render: (data, type, row) => {
                    let badgeClass = row.status === 'completed' ? 'bg-success' :
                                     row.status === 'pending' ? 'bg-warning' :
                                     'bg-danger';
                    return `<span class="badge badge-status ${badgeClass}">${row.status}</span>`;
                }
            },
            {
                data: null,
                render: (data, type, row) => {
                    let nextStatus = row.status === 'pending' ? 'completed' : 
                                     row.status === 'completed' ? 'cancelled' : 'pending';
                    return `<button class="btn btn-sm btn-primary" onclick="updateOrderStatus(${row.id}, '${nextStatus}')">Change Status</button>`;
                }
            }
        ],
    });

    // Update Order Status
    window.updateOrderStatus = function(id, status){
        const message = `Order status will be changed to "${status}".`;
        Swal.fire({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if(result.isConfirmed){
                $.post('{{ route("admin.order.status") }}', { id, status, _token:$('meta[name="csrf-token"]').attr('content') }, function(response){
                    if(response.success) Toast.fire({ icon:'success', title: response.message });
                    table.ajax.reload();
                }).fail(function(xhr){
                    Toast.fire({ icon:'error', title: xhr.responseJSON.message || 'Something went wrong!' });
                });
            }
        });
    };

});
</script>
@endsection
