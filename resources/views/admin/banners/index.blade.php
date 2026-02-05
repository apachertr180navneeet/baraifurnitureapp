@extends('admin.layouts.app')
@section('style')
@endsection
@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6 text-start">
            <h5 class="py-2 mb-2">
                <span class="text-primary fw-light">Banner Management</span>
            </h5>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                Add Banner
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="BannerTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="AddBannerForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="Enter Title" />
                        <small class="error-text text-danger"></small>
                    </div>

                    <!-- Image -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" name="image" id="image" class="form-control" />
                        <small class="error-text text-danger"></small>
                    </div>

                    <!-- hidden status default to active on add -->
                    <input type="hidden" id="addStatusValue" name="status" value="active" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="AddBanner">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="EditBannerForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="bannerId" name="id">
                    <!-- store current status in hidden field so update sends it -->
                    <input type="hidden" id="bannerStatusValue" name="status" value="active">

                    <!-- Title -->
                    <div class="mb-3">
                        <label for="editTitle" class="form-label">Title</label>
                        <input type="text" id="editTitle" name="title" class="form-control" placeholder="Enter Title" />
                        <small class="error-text text-danger"></small>
                    </div>

                    <!-- Image -->
                    <div class="mb-3">
                        <label for="editImage" class="form-label">Image</label>
                        <input type="file" id="editImage" name="image" class="form-control" />
                        <small class="error-text text-danger"></small>
                    </div>

                    <div class="mb-3">
                        <img id="previewImage" src="" alt="Banner Image" class="img-thumbnail" width="150">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="UpdateBanner">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
    <script>
        $(document).ready(function() {

            // Set CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table = $('#BannerTable').DataTable({
                processing: true,
                ajax: {
                    url: "{{ route('admin.banner.getall') }}",
                    type: 'GET',
                },
                columns: [
                    { data: "title" },
                    { 
                        data: "image",
                        render: (data) => data ? `<img src="${data}" class="img-thumbnail" width="100">` : ''
                    },
                    {
                        data: "status",
                        render: (data) => data === "active" 
                            ? '<span class="badge bg-label-success">Active</span>' 
                            : '<span class="badge bg-label-danger">Inactive</span>'
                    },
                    {
                        data: "action",
                        render: (data, type, row) => {
                            const statusBtn = row.status === 'inactive'
                                ? `<button class="btn btn-sm btn-success me-1" onclick="updateBannerStatus(${row.id}, 'active')">Activate</button>`
                                : `<button class="btn btn-sm btn-danger me-1" onclick="updateBannerStatus(${row.id}, 'inactive')">Deactivate</button>`;
                            const editBtn = `<button class="btn btn-sm btn-warning me-1" onclick="editBanner(${row.id})">Edit</button>`;
                            const deleteBtn = `<button class="btn btn-sm btn-danger" onclick="deleteBanner(${row.id})">Delete</button>`;
                            return `${statusBtn}${editBtn}${deleteBtn}`;
                        }
                    }
                ],
            });

            // Add Banner
            $('#AddBannerForm').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                formData.set('status', $('#addStatusValue').val()); // hidden status

                $('#addModal').find('.error-text').text('');

                $.ajax({
                    url: '{{ route("admin.banner.store") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response){
                        if(response.success){
                            setFlash("success", response.message);
                            $('#addModal').modal('hide');
                            $('#AddBannerForm')[0].reset();
                            table.ajax.reload();
                        }
                    },
                    error: handleAjaxError('#AddBannerForm')
                });
            });

            // Edit Banner Load
            window.editBanner = function(id) {
                const url = '{{ route("admin.banner.get", ":id") }}'.replace(':id', id);
                $.get(url, function(data){
                    $('#bannerId').val(data.id);
                    $('#editTitle').val(data.title);
                    $('#previewImage').attr('src', data.image);
                    $('#bannerStatusValue').val(data.status ?? 'active');
                    $('#editModal').modal('show');
                }).fail(() => setFlash("error","Banner not found."));
            };

            // Update Banner
            $('#EditBannerForm').submit(function(e){
                e.preventDefault();
                let formData = new FormData(this);
                formData.set('id', $('#bannerId').val());
                formData.set('status', $('#bannerStatusValue').val());

                $('#editModal').find('.error-text').text('');

                $.ajax({
                    url: '{{ route("admin.banner.update") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response){
                        if(response.success){
                            setFlash("success", response.message);
                            $('#editModal').modal('hide');
                            table.ajax.reload();
                        }
                    },
                    error: handleAjaxError('#EditBannerForm')
                });
            });

            // Update Status
            window.updateBannerStatus = function(id, status){
                const message = status === 'active' ? "Banner will be activated." : "Banner will be deactivated.";
                Swal.fire({
                    title: "Are you sure?",
                    text: message,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                }).then(result => {
                    if(result.isConfirmed){
                        $.post('{{ route("admin.banner.status") }}', {id, status}, function(response){
                            if(response.success) {
                                setFlash("success", response.message);
                                table.ajax.reload();
                            } else setFlash("error", response.message || "Failed to update status.");
                        }).fail(() => setFlash("error","Request failed."));
                    }
                });
            };

            // Delete Banner
            window.deleteBanner = function(id){
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to delete this Banner?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                }).then(result => {
                    if(result.isConfirmed){
                        const url = '{{ route("admin.banner.destroy", ":id") }}'.replace(':id', id);
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function(response){
                                if(response.success) setFlash("success", response.message);
                                table.ajax.reload();
                            },
                            error: () => setFlash("error","Failed to delete.")
                        });
                    }
                });
            };

            // Flash message helper
            function setFlash(type,message){
                Toast.fire({ icon:type, title:message });
            }

            // Error handler for FormData AJAX
            function handleAjaxError(formSelector){
                return function(xhr){
                    if(xhr.status === 422){
                        let errors = xhr.responseJSON.errors;
                        $(formSelector).find('.error-text').text('');
                        for(let field in errors){
                            const input = $(formSelector).find(`[name=${field}]`);
                            input.siblings('.error-text').text(errors[field][0]);
                        }
                    } else setFlash("error","An unexpected error occurred.");
                }
            }

        });
    </script>
@endsection

