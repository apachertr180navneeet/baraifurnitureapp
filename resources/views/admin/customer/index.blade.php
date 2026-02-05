@extends('admin.layouts.app')
@section('style')

@endsection
@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Customer</span>
            </h5>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                Add Customer
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="customerTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Location</th>
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
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="full_name" class="form-label">Name</label>
                        <input type="text" id="full_name" class="form-control" placeholder="Enter Name" />
                        <small class="error-text text-danger"></small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="phone" class="form-label">Mobile Number</label>
                        <input type="text" id="phone" class="form-control" placeholder="Enter Mobile Number" />
                        <small class="error-text text-danger"></small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" id="email" class="form-control" placeholder="Enter Email" />
                        <small class="error-text text-danger"></small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="city" class="form-label">Location</label>
                        <input type="text" id="city" class="form-control" placeholder="Enter Location" />
                        <small class="error-text text-danger"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="Addcustomer">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" id="compid">
                    <div class="col-md-12 mb-3">
                        <label for="editfull_name" class="form-label">Name</label>
                        <input type="text" id="editfull_name" class="form-control" placeholder="Enter Name" />
                        <small class="error-text text-danger"></small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="editphone" class="form-label">Mobile Number</label>
                        <input type="text" id="editphone" class="form-control" placeholder="Enter Mobile Number" />
                        <small class="error-text text-danger"></small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="editemail" class="form-label">Email</label>
                        <input type="text" id="editemail" class="form-control" placeholder="Enter Email" />
                        <small class="error-text text-danger"></small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="editcity" class="form-label">Location</label>
                        <input type="text" id="editcity" class="form-control" placeholder="Enter Location" />
                        <small class="error-text text-danger"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="EditUser">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        const table = $('#customerTable').DataTable({
            processing: true,
            ajax: {
                url: "{{ route('admin.customer.getall') }}",
                type: 'GET',
            },
            columns: [
                { data: "full_name" },
                { data: "phone" },
                { data: "email" },
                { data: "city" },
                {
                    data: "status",
                    render: (data, type, row) => {
                        if (row.status == 'active') {
                            return '<span class="badge bg-label-success me-1">Active</span>';
                        }
                        return '<span class="badge bg-label-danger me-1">Inactive</span>';
                    }
                },
                {
                    data: "action",
                    render: (data, type, row) => {
                        const statusButton = row.status == 'inactive'
                            ? `<button type="button" class="btn btn-sm btn-success" onclick="updateUserStatus(${row.id}, 'active')">Activate</button>`
                            : `<button type="button" class="btn btn-sm btn-danger" onclick="updateUserStatus(${row.id}, 'inactive')">Deactivate</button>`;

                        const deleteButton = `<button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(${row.id})">Delete</button>`;
                        const editButton = `<button type="button" class="btn btn-sm btn-warning" onclick="editUser(${row.id})">Edit</button>`;
                        return `${statusButton} ${editButton} ${deleteButton}`;
                    },
                },
            ],
        });

        // ========================
        // Add customer
        // ========================
        $('#Addcustomer').click(function(e) {
            e.preventDefault();

            let data = {
                full_name: $('#full_name').val(),
                phone: $('#phone').val(),
                email: $('#email').val(),
                city: $('#city').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            $('.error-text').text('');

            $.ajax({
                url: '{{ route('admin.customer.store') }}',
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        setFlash("success", response.message);
                        $('#addModal').modal('hide');
                        $('#addModal').find('input').val('');
                        table.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        for (let field in errors) {
                            let $field = $(`#${field}`);
                            if ($field.length) {
                                $field.siblings('.error-text').text(errors[field][0]);
                            }
                        }
                    } else {
                        setFlash("error", "An unexpected error occurred.");
                    }
                }
            });
        });

        // ========================
        // Edit customer (load data)
        // ========================
        window.editUser = function(userId) {
            const url = '{{ route("admin.customer.get", ":userid") }}'.replace(":userid", userId);
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $('#compid').val(data.id);
                    $('#editfull_name').val(data.full_name);
                    $('#editphone').val(data.phone);
                    $('#editemail').val(data.email);
                    $('#editcity').val(data.city);

                    $('#editModal').modal('show');
                },
                error: function() {
                    setFlash("error", "Customer not found. Please try again later.");
                }
            });
        };

        // ========================
        // Save edit
        // ========================
        $('#EditUser').on('click', function() {
            const userId = $('#compid').val();
            $.ajax({
                url: '{{ route('admin.customer.update') }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    full_name: $('#editfull_name').val(),
                    phone: $('#editphone').val(),
                    email: $('#editemail').val(),
                    city: $('#editcity').val(),
                    id: userId
                },
                success: function(response) {
                    if (response.success) {
                        setFlash("success", response.message);
                        $('#editModal').modal('hide');
                        $('#editModal').find('input, textarea, select').val('');
                        table.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $('#editModal').find('.error-text').text('');
                        for (let field in errors) {
                            let $field = $(`#edit${field}`);
                            if ($field.length) {
                                $field.siblings('.error-text').text(errors[field][0]);
                            }
                        }
                    } else {
                        setFlash("error", "An unexpected error occurred.");
                    }
                }
            });
        });

        // ========================
        // Update user status
        // ========================
        window.updateUserStatus = function(userId, status) {
            const message = status == "active"
                ? "Customer will be able to log in after activation."
                : "Customer will not be able to log in after deactivation.";

            Swal.fire({
                title: "Are you sure?",
                text: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Okay",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('admin.customer.status') }}",
                        data: { userId, status, _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (response) {
                            if (response.success) {
                                const successMessage = status == 1
                                    ? "Customer activated successfully."
                                    : "Customer deactivated successfully.";
                                setFlash("success", successMessage);
                            } else {
                                setFlash("error", "There was an issue changing the status.");
                            }
                            table.ajax.reload();
                        },
                        error: function () {
                            setFlash("error", "There was an issue processing your request.");
                        },
                    });
                } else {
                    table.ajax.reload();
                }
            });
        };

        // ========================
        // Delete user
        // ========================
        window.deleteUser = function(userId) {
            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to delete this customer?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes",
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = '{{ route("admin.customer.destroy", ":userId") }}'.replace(":userId", userId);
                    $.ajax({
                        type: "DELETE",
                        url,
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (response) {
                            if (response.success) {
                                setFlash("success", "Customer deleted successfully.");
                            } else {
                                setFlash("error", response.message || "There was an issue deleting the customer.");
                            }
                            table.ajax.reload();
                        },
                        error: function () {
                            setFlash("error", "There was an issue processing your request.");
                        },
                    });
                }
            });
        };

        // ========================
        // Flash helper
        // ========================
        function setFlash(type, message) {
            Toast.fire({
                icon: type,
                title: message
            });
        }
    });
</script>

@endsection
