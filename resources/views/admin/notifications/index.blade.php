@extends('admin.layouts.app')
@section('style')
@endsection
@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Push Notification</span>
            </h5>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                Add Notification
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="NotificationTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Description</th>
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

<!-- Add Modal -->
<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Date -->
                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" id="date" class="form-control" />
                    <small class="error-text text-danger"></small>
                </div>

                <!-- Title -->
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" id="title" class="form-control" placeholder="Enter Title" />
                    <small class="error-text text-danger"></small>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" class="form-control" rows="3" placeholder="Enter Description"></textarea>
                    <small class="error-text text-danger"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="AddNotification">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="catId">

                <!-- Date -->
                <div class="mb-3">
                    <label for="editdate" class="form-label">Date</label>
                    <input type="date" id="editdate" class="form-control" />
                    <small class="error-text text-danger"></small>
                </div>

                <!-- Title -->
                <div class="mb-3">
                    <label for="editname" class="form-label">Title</label>
                    <input type="text" id="editname" class="form-control" placeholder="Enter Title" />
                    <small class="error-text text-danger"></small>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="editdescription" class="form-label">Description</label>
                    <textarea id="editdescription" class="form-control" rows="3" placeholder="Enter Description"></textarea>
                    <small class="error-text text-danger"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="EditNotification">Save</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    const table = $('#NotificationTable').DataTable({
        processing: true,
        ajax: {
            url: "{{ route('admin.notification.getall') }}",
            type: 'GET',
        },
        columns: [
            { data: "date" },
            { data: "title" },
            { data: "description" },
            {
                data: "status",
                render: (data, type, row) => {
                    return row.status == "active"
                        ? '<span class="badge bg-label-success me-1">Active</span>'
                        : '<span class="badge bg-label-danger me-1">Inactive</span>';
                }
            },
            {
                data: "action",
                render: (data, type, row) => {
                    const statusBtn = row.status == 'inactive'
                        ? `<button type="button" class="btn btn-sm btn-success" onclick="updateNotificationStatus(${row.id}, 'active')">Activate</button>`
                        : `<button type="button" class="btn btn-sm btn-danger" onclick="updateNotificationStatus(${row.id}, 'inactive')">Deactivate</button>`;
                    const editBtn = `<button class="btn btn-sm btn-warning" onclick="editNotification(${row.id})">Edit</button>`;
                    const deleteBtn = `<button class="btn btn-sm btn-danger" onclick="deleteNotification(${row.id})">Delete</button>`;
                    return `${statusBtn} ${editBtn} ${deleteBtn}`;
                }
            }
        ],
    });

    // Add Notification
    $('#AddNotification').click(function() {
        const data = {
            date: $('#date').val(),
            title: $('#title').val(),
            description: $('#description').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // clear errors
        $('#addModal').find('.error-text').text('');

        $.post('{{ route("admin.notification.store") }}', data, function(response) {
            if (response.success) {
                setFlash("success", response.message);
                $('#addModal').modal('hide');
                $('#addModal').find('input, textarea').val(''); // clear all fields
                table.ajax.reload();
            }
        }).fail(function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                for (let field in errors) {
                    $(`#${field}`).siblings('.error-text').text(errors[field][0]);
                }
            } else {
                setFlash("error", "An unexpected error occurred.");
            }
        });
    });

    // Load Edit Notification
    window.editNotification = function(id) {
        const url = '{{ route("admin.notification.get", ":id") }}'.replace(':id', id);
        $.get(url, function(data) {
            $('#catId').val(data.id);
            $('#editdate').val(data.date);
            $('#editname').val(data.title);
            $('#editdescription').val(data.description);
            $('#editModal').modal('show');
        }).fail(function() { 
            setFlash("error","Notification not found."); 
        });
    };

    // Save Edit Notification
    $('#EditNotification').click(function() {
        const data = {
            id: $('#catId').val(),
            date: $('#editdate').val(),
            title: $('#editname').val(),
            description: $('#editdescription').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $('#editModal').find('.error-text').text('');

        $.post('{{ route("admin.notification.update") }}', data, function(response) {
            if (response.success) {
                setFlash("success", response.message);
                $('#editModal').modal('hide');
                table.ajax.reload();
            }
        }).fail(function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                for (let field in errors) {
                    $(`#edit${field}`).siblings('.error-text').text(errors[field][0]);
                }
            } else {
                setFlash("error","An unexpected error occurred.");
            }
        });
    });

    // Update Status
    window.updateNotificationStatus = function(id,status) {
        const message = status == 1 ? "Notification will be active." : "Notification will be inactive.";
        Swal.fire({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("admin.notification.status") }}', { id, status, _token: $('meta[name="csrf-token"]').attr('content') }, function(response) {
                    if(response.success) setFlash("success", response.message);
                    table.ajax.reload();
                });
            }
        });
    };

    // Delete Notification
    window.deleteNotification = function(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "Do you want to delete this Notification?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if(result.isConfirmed){
                const url = '{{ route("admin.notification.destroy", ":id") }}'.replace(':id', id);
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(response){
                        if(response.success) setFlash("success", response.message);
                        table.ajax.reload();
                    }
                });
            }
        });
    };

    function setFlash(type,message){
        Toast.fire({ icon:type, title:message });
    }
});
</script>
@endsection
