@extends('admin.layouts.app')

@section('style')
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row mb-3">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Customize Orders Management</span>
            </h5>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="CustomizeOrdersTable">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Remark</th>
                                    <th>Image</th>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="EditOrderForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Customize Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="orderId" name="id">

                    <!-- Remark -->
                    <div class="mb-3">
                        <label for="editRemark" class="form-label">Remark</label>
                        <textarea id="editRemark" name="remark" class="form-control"></textarea>
                        <small class="error-text text-danger"></small>
                    </div>

                    <!-- Image -->
                    <div class="mb-3">
                        <label for="editImage" class="form-label">Image</label>
                        <input type="file" id="editImage" name="image" class="form-control" />
                        <small class="error-text text-danger"></small>
                    </div>
                    <div class="mb-3">
                        <img id="previewImage" src="" alt="Order Image" class="img-thumbnail" width="150">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="UpdateOrder">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    const table = $('#CustomizeOrdersTable').DataTable({
        processing: true,
        ajax: { url: "{{ route('admin.customizeorders.getall') }}", type: 'GET' },
        columns: [
            { data: "orderId" },
            { data: "customer.full_name", defaultContent: "" },
            { data: "date" },
            { data: "remark" },
            { 
                data: "image",
                render: (data) => data ? `<img src="${data}" class="img-thumbnail" width="100">` : ''
            },
            {
                data: "status",
                render: (data) => data === "pending" 
                    ? '<span class="badge bg-label-warning">Pending</span>' 
                    : '<span class="badge bg-label-success">Completed</span>'
            },
            {
                data: "action",
                render: (data, type, row) => {
                    const statusBtn = row.status === 'pending'
                        ? `<button class="btn btn-sm btn-success me-1" onclick="updateOrderStatus(${row.id}, 'completed')">Complete</button>`
                        : `<button class="btn btn-sm btn-warning me-1" onclick="updateOrderStatus(${row.id}, 'pending')">Pending</button>`;
                    const editBtn = `<button class="btn btn-sm btn-warning me-1" onclick="editOrder(${row.id})">Edit</button>`;
                    return `${statusBtn}${editBtn}`;
                }
            }
        ],
    });

    // Edit Order Load
    window.editOrder = function(id) {
        const url = '{{ route("admin.customizeorders.get", ":id") }}'.replace(':id', id);
        $.get(url, function(data){
            console.log(data.customer.full_name);
            $('#orderId').val(data.id);
            $('#editOrderId').val(data.orderId);
            $('#editCustomerId').val(data.customer.full_name ?? '');
            $('#editDate').val(data.date);
            $('#editRemark').val(data.remark);
            $('#previewImage').attr('src', data.image ?? '');
            $('#editStatus').val(data.status);
            $('#editModal').modal('show');
        }).fail(() => Swal.fire("Error","Order not found.","error"));
    };

    // Update Order
    $('#EditOrderForm').submit(function(e){
        e.preventDefault();
        let formData = new FormData(this);
        formData.set('id', $('#orderId').val());

        $('#editModal').find('.error-text').text('');

        $.ajax({
            url: '{{ route("admin.customizeorders.update") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response){
                if(response.success){
                    Swal.fire("Success", response.message, "success");
                    $('#editModal').modal('hide');
                    table.ajax.reload();
                }
            },
            error: function(xhr){
                if(xhr.status === 422){
                    let errors = xhr.responseJSON.errors;
                    $('#EditOrderForm').find('.error-text').text('');
                    for(let field in errors){
                        $(`#EditOrderForm [name=${field}]`).siblings('.error-text').text(errors[field][0]);
                    }
                } else Swal.fire("Error","An unexpected error occurred.","error");
            }
        });
    });

    // Update Status
    window.updateOrderStatus = function(id, status){
        const message = status === 'pending' ? "Order will be pending." : "Order will be completed.";
        Swal.fire({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then(result => {
            if(result.isConfirmed){
                $.post('{{ route("admin.customizeorders.status") }}', {id, status}, function(response){
                    if(response.success) {
                        Swal.fire("Success", response.message, "success");
                        table.ajax.reload();
                    } else Swal.fire("Error", response.message || "Failed to update status.","error");
                }).fail(() => Swal.fire("Error","Request failed.","error"));
            }
        });
    };

});
</script>
@endsection
