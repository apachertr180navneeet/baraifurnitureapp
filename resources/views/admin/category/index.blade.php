@extends('admin.layouts.app')
@section('style')
@endsection
@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Category</span>
            </h5>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                Add Category
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="categoryTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
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
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" id="name" class="form-control" placeholder="Enter Category Name" />
                    <small class="error-text text-danger"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="AddCategory">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="catId">
                <div class="mb-3">
                    <label for="editname" class="form-label">Category Name</label>
                    <input type="text" id="editname" class="form-control" placeholder="Enter Category Name" />
                    <small class="error-text text-danger"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="EditCategory">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    const table = $('#categoryTable').DataTable({
        processing: true,
        ajax: {
            url: "{{ route('admin.category.getall') }}",
            type: 'GET',
        },
        columns: [
            { data: "name" },
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
                        ? `<button type="button" class="btn btn-sm btn-success" onclick="updateCategoryStatus(${row.id}, 'active')">Activate</button>`
                        : `<button type="button" class="btn btn-sm btn-danger" onclick="updateCategoryStatus(${row.id}, 'inactive')">Deactivate</button>`;
                    const editBtn = `<button class="btn btn-sm btn-warning" onclick="editCategory(${row.id})">Edit</button>`;
                    const deleteBtn = `<button class="btn btn-sm btn-danger" onclick="deleteCategory(${row.id})">Delete</button>`;
                    return `${statusBtn} ${editBtn} ${deleteBtn}`;
                }
            }
        ],
    });

    // Add Category
    $('#AddCategory').click(function() {
        const data = {
            name: $('#name').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        $('.error-text').text('');
        $.post('{{ route("admin.category.store") }}', data, function(response) {
            if (response.success) {
                setFlash("success", response.message);
                $('#addModal').modal('hide');
                $('#addModal').find('input').val('');
                table.ajax.reload();
            }
        }).fail(function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                for (let field in errors) {
                    $(`#${field}`).siblings('.error-text').text(errors[field][0]);
                }
            } else setFlash("error", "An unexpected error occurred.");
        });
    });

    // Edit Category (load data)
    window.editCategory = function(id) {
        const url = '{{ route("admin.category.get", ":id") }}'.replace(':id', id);
        $.get(url, function(data) {
            $('#catId').val(data.id);
            $('#editname').val(data.name);
            $('#editModal').modal('show');
        }).fail(function() { setFlash("error","Category not found."); });
    };

    // Save Edit
    $('#EditCategory').click(function() {
        const data = {
            id: $('#catId').val(),
            name: $('#editname').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        $.post('{{ route("admin.category.update") }}', data, function(response) {
            if (response.success) {
                setFlash("success", response.message);
                $('#editModal').modal('hide');
                table.ajax.reload();
            }
        }).fail(function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $('#editModal').find('.error-text').text('');
                for (let field in errors) {
                    $(`#edit${field}`).siblings('.error-text').text(errors[field][0]);
                }
            } else setFlash("error","An unexpected error occurred.");
        });
    });

    // Update Status
    window.updateCategoryStatus = function(id,status) {
        const message = status == 1 ? "Category will be active." : "Category will be inactive.";
        Swal.fire({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("admin.category.status") }}', { id, status, _token: $('meta[name="csrf-token"]').attr('content') }, function(response) {
                    if(response.success) setFlash("success", response.message);
                    table.ajax.reload();
                });
            }
        });
    };

    // Delete Category
    window.deleteCategory = function(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "Do you want to delete this category?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if(result.isConfirmed){
                const url = '{{ route("admin.category.destroy", ":id") }}'.replace(':id', id);
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
