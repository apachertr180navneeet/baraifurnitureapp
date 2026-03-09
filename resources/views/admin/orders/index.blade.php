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

let table; // make global

$(document).ready(function() {

    // DataTable
    table = $('#orderTable').DataTable({

        processing: true,

        ajax: '{{ route("admin.order.getall") }}',

        columns: [

            { data: 'id' },

            {
                data: 'created_at',
                render: (data) => {
                    if (!data) return '';
                    const date = new Date(data);
                    if (Number.isNaN(date.getTime())) return data;

                    const dd = String(date.getDate()).padStart(2, '0');
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const yyyy = date.getFullYear();

                    return `${dd}/${mm}/${yyyy}`;
                }
            },

            { data: 'product_names', defaultContent: '' },

            { data: 'customer.full_name', defaultContent: '' },

            { data: 'amount' },

            {
                data: "status",
                render: function(data){

                    if(data === "pending"){
                        return '<span class="badge bg-label-warning">Pending</span>';
                    }

                    if(data === "completed"){
                        return '<span class="badge bg-label-success">Completed</span>';
                    }

                    if(data === "cancelled"){
                        return '<span class="badge bg-label-danger">Cancelled</span>';
                    }

                    return data;
                }
            },

            {
                data: null,
                render: (data, type, row) => {

                    let pdfBtn = '';
                    let statusBtn = '';

                    // PDF Button
                    if (row.pdf_url) {

                        const pdfUrl = row.pdf_url.startsWith('http')
                            ? row.pdf_url
                            : `{{ url('/') }}/${row.pdf_url.replace(/^\/+/, '')}`;

                        pdfBtn = `<a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-primary me-1">View PDF</a>`;

                    } else {

                        pdfBtn = `<span class="text-muted me-1">No PDF</span>`;
                    }

                    // Status Buttons

                    if(row.status === 'pending'){

                        statusBtn = `
                        <button class="btn btn-sm btn-success me-1"
                        onclick="updateOrderStatus(${row.id}, 'completed')">
                        Complete
                        </button>

                        <button class="btn btn-sm btn-danger"
                        onclick="updateOrderStatus(${row.id}, 'cancelled')">
                        Cancel
                        </button>
                        `;

                    }
                    else if(row.status === 'completed'){

                        statusBtn = `
                        <button class="btn btn-sm btn-warning"
                        onclick="updateOrderStatus(${row.id}, 'pending')">
                        Mark Pending
                        </button>
                        `;
                    }
                    else if(row.status === 'cancelled'){

                        statusBtn = `
                        <button class="btn btn-sm btn-warning"
                        onclick="updateOrderStatus(${row.id}, 'pending')">
                        Reopen
                        </button>
                        `;
                    }

                    return pdfBtn + statusBtn;
                }
            }

        ],

    });

});

</script>


<script>

// Update Status

function updateOrderStatus(id, status){

    const message =
        status === 'pending'
        ? "Order will be pending."
        : status === 'completed'
        ? "Order will be completed."
        : "Order will be cancelled.";

    Swal.fire({

        title: "Are you sure?",
        text: message,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes"

    }).then(result => {

        if(result.isConfirmed){

            $.post('{{ route("admin.order.status") }}',
            {
                id: id,
                status: status,
                _token: '{{ csrf_token() }}'
            },
            function(response){

                if(response.success){

                    Swal.fire("Success", response.message, "success");

                    table.ajax.reload();

                }else{

                    Swal.fire("Error", response.message || "Failed to update status.","error");

                }

            }).fail(function(){

                Swal.fire("Error","Request failed.","error");

            });

        }

    });

}

</script>

@endsection

