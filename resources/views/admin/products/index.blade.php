@extends('admin.layouts.app')

@section('style')

<style>

    .color-row { display: flex; gap: 10px; margin-bottom: 5px; }

</style>

@endsection

@section('content')

<div class="container-fluid flex-grow-1 container-p-y">

    <div class="row">

        <div class="col-md-6 text-start">

            <h5 class="py-2 mb-2">

                <span class="text-primary fw-light">Products</span>

            </h5>

        </div>

        <div class="col-md-6 text-end">

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">

                Add Product

            </button>

        </div>

    </div>



    <div class="row">

        <div class="col-xl-12 col-lg-12">

            <div class="card">

                <div class="card-body">

                    <div class="table-responsive text-nowrap">

                        <table class="table table-bordered" id="productTable">

                            <thead>

                                <tr>

                                    <th>Name</th>

                                    <th>Category</th>

                                    <th>Price</th>

                                    <th>Stock</th>

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



<!-- Add Product Modal -->

<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">Add Product</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <form id="addProductForm" enctype="multipart/form-data">

                    <div class="row mb-3">

                        <div class="col-md-6">

                            <label for="name" class="form-label">Product Name</label>

                            <input type="text" id="name" class="form-control" placeholder="Enter Name">

                            <small class="error-text text-danger"></small>

                        </div>

                        <div class="col-md-6">

                            <label for="category_id" class="form-label">Category</label>

                            <select id="category_id" class="form-control">

                                <option value="">Select Category</option>

                                @foreach($categories as $cat)

                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>

                                @endforeach

                            </select>

                            <small class="error-text text-danger"></small>

                        </div>

                    </div>

                    <div class="row mb-3">

                        <div class="col-md-4">

                            <label for="price" class="form-label">Price</label>

                            <input type="number" id="price" class="form-control" placeholder="Enter Price">

                            <small class="error-text text-danger"></small>

                        </div>

                        <div class="col-md-4">

                            <label for="stock" class="form-label">Stock</label>

                            <input type="number" id="stock" class="form-control" placeholder="Enter Stock">

                            <small class="error-text text-danger"></small>

                        </div>

                        <div class="col-md-4">

                            <label for="image" class="form-label">Image</label>

                            <input type="file" id="image" class="form-control">

                            <small class="error-text text-danger"></small>

                        </div>

                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" class="form-control" rows="3" placeholder="Enter product description"></textarea>
                            <small class="error-text text-danger"></small>
                        </div>
                    </div>



                    <div class="mb-3">

                        <label class="form-label">Colors</label>

                        <div id="colorContainer"></div>

                        <button type="button" class="btn btn-sm btn-secondary" id="addColorBtn">Add Color</button>

                    </div>

                </form>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>

                <button type="button" class="btn btn-primary" id="AddProduct">Save</button>

            </div>

        </div>

    </div>

</div>



<!-- Edit Product Modal -->

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">Edit Product</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <form id="editProductForm" enctype="multipart/form-data">

                    <input type="hidden" id="productId">

                    <div class="row mb-3">

                        <div class="col-md-6">

                            <label for="editname" class="form-label">Product Name</label>

                            <input type="text" id="editname" class="form-control">

                            <small class="error-text text-danger"></small>

                        </div>

                        <div class="col-md-6">

                            <label for="editcategory_id" class="form-label">Category</label>

                            <select id="editcategory_id" class="form-control">

                                <option value="">Select Category</option>

                                @foreach($categories as $cat)

                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>

                                @endforeach

                            </select>

                            <small class="error-text text-danger"></small>

                        </div>

                    </div>

                    <div class="row mb-3">

                        <div class="col-md-4">

                            <label for="editprice" class="form-label">Price</label>

                            <input type="number" id="editprice" class="form-control">

                            <small class="error-text text-danger"></small>

                        </div>

                        <div class="col-md-4">

                            <label for="editstock" class="form-label">Stock</label>

                            <input type="number" id="editstock" class="form-control">

                            <small class="error-text text-danger"></small>

                        </div>

                        <div class="col-md-4">

                            <label for="editimage" class="form-label">Image</label>

                            <input type="file" id="editimage" class="form-control">

                            <small class="error-text text-danger"></small>

                        </div>

                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="editdescription" class="form-label">Description</label>
                            <textarea id="editdescription" class="form-control" rows="3" placeholder="Enter product description"></textarea>
                            <small class="error-text text-danger"></small>
                        </div>
                    </div>

                    <div class="mb-3">

                        <label class="form-label">Colors</label>

                        <div id="editColorContainer"></div>

                        <button type="button" class="btn btn-sm btn-secondary" id="editAddColorBtn">Add Color</button>

                    </div>

                </form>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>

                <button type="button" class="btn btn-primary" id="EditProduct">Save</button>

            </div>

        </div>

    </div>

</div>

@endsection



@section('script')

<script>

$(document).ready(function() {



    // DataTable

    const table = $('#productTable').DataTable({

        processing: true,

        ajax: '{{ route("admin.product.getall") }}',

        columns: [

            { data: 'name' },

            { data: 'category.name', defaultContent: '' },

            { data: 'price' },

            { data: 'stock' },

            {

                data: 'status',

                render: (data, type, row) => {

                    return row.status == 'active'

                        ? '<span class="badge bg-label-success me-1">Active</span>'

                        : '<span class="badge bg-label-danger me-1">Inactive</span>';

                }

            },

            {

                data: null,

                render: (data, type, row) => {

                    const statusBtn = row.status == 'inactive'

                        ? `<button class="btn btn-sm btn-success" onclick="updateProductStatus(${row.id}, 'active')">Activate</button>`

                        : `<button class="btn btn-sm btn-danger" onclick="updateProductStatus(${row.id}, 'inactive')">Deactivate</button>`;

                    const editBtn = `<button class="btn btn-sm btn-warning" onclick="editProduct(${row.id})">Edit</button>`;

                    const deleteBtn = `<button class="btn btn-sm btn-danger" onclick="deleteProduct(${row.id})">Delete</button>`;

                    return `${statusBtn} ${editBtn} ${deleteBtn}`;

                }

            }

        ],

    });



    // Add Color - Add Product

    let colorCount = 0;

    $('#addColorBtn').click(() => {

        colorCount++;

        $('#colorContainer').append(`

            <div class="color-row" id="colorRow${colorCount}">

                <input type="color" name="colors[${colorCount}][color_name]" class="form-control" placeholder="Color Name">

                <input type="number" name="colors[${colorCount}][qty]" class="form-control" placeholder="Qty">

                <button type="button" class="btn btn-danger btn-sm" onclick="$('#colorRow${colorCount}').remove()">X</button>

            </div>

        `);

    });



    // Add Color - Edit Product

    let editColorCount = 0;

    $('#editAddColorBtn').click(() => {

        editColorCount++;

        $('#editColorContainer').append(`

            <div class="color-row" id="editColorRow${editColorCount}">

                <input type="color" name="colors[${editColorCount}][color_name]" class="form-control" placeholder="Color Name">

                <input type="number" name="colors[${editColorCount}][qty]" class="form-control" placeholder="Qty">

                <button type="button" class="btn btn-danger btn-sm" onclick="$('#editColorRow${editColorCount}').remove()">X</button>

            </div>

        `);

    });



    // Add Product AJAX

    $('#AddProduct').click(function() {

        let formData = new FormData();

        formData.append('name', $('#name').val());

        formData.append('category_id', $('#category_id').val());

        formData.append('price', $('#price').val());

        formData.append('stock', $('#stock').val());

        formData.append('description', $('#description').val());

        if($('#image')[0].files.length>0) formData.append('image', $('#image')[0].files[0]);

        $('#colorContainer .color-row').each(function(index){

            formData.append(`colors[${index}][color_name]`, $(this).find('input').eq(0).val());

            formData.append(`colors[${index}][qty]`, $(this).find('input').eq(1).val());

        });

        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));



        $('#addProductForm .error-text').text('');

        $.ajax({

            url: '{{ route("admin.product.store") }}',

            type: 'POST',

            data: formData,

            contentType:false,

            processData:false,

            success: function(response){

                if(response.success){

                    Toast.fire({ icon:'success', title:response.message });

                    $('#addModal').modal('hide');

                    $('#addProductForm')[0].reset();

                    $('#colorContainer').html('');

                    table.ajax.reload();

                }

            },

            error:function(xhr){

                if(xhr.status===422){

                    let errors=xhr.responseJSON.errors;

                    for(let field in errors){

                        let fieldName=field.includes('colors')?field.replace(/\./g,'_'):field;

                        $('#addProductForm .error-text').each(function(){

                            if($(this).prev().attr('id')===fieldName) $(this).text(errors[field][0]);

                        });

                    }

                } else Toast.fire({icon:'error',title:'An unexpected error occurred.'});

            }

        });

    });



    // Edit Product AJAX Load

    window.editProduct = function(id){

        const url='{{ route("admin.product.get", ":id") }}'.replace(':id', id);

        $.get(url, function(data){

            $('#productId').val(data.id);

            $('#editname').val(data.name);

            $('#editcategory_id').val(data.category_id);

            $('#editprice').val(data.price);

            $('#editstock').val(data.stock);

            $('#editdescription').val(data.description);

            $('#editColorContainer').html('');

            editColorCount = 0;

            if(data.colors){

                data.colors.forEach((color, index)=>{

                    editColorCount++;

                    $('#editColorContainer').append(`

                        <div class="color-row" id="editColorRow${editColorCount}">

                            <input type="color" name="colors[${editColorCount}][color_name]" class="form-control" value="${color.color_name}" placeholder="Color Name">

                            <input type="number" name="colors[${editColorCount}][qty]" class="form-control" value="${color.qty}" placeholder="Qty">

                            <button type="button" class="btn btn-danger btn-sm" onclick="$('#editColorRow${editColorCount}').remove()">X</button>

                        </div>

                    `);

                });

            }

            $('#editModal').modal('show');

        }).fail(function(){ Toast.fire({ icon:'error', title:'Product not found.' }); });

    };



    // Update Product AJAX

    $('#EditProduct').click(function(){

        let formData = new FormData();

        formData.append('id', $('#productId').val());

        formData.append('name', $('#editname').val());

        formData.append('category_id', $('#editcategory_id').val());

        formData.append('price', $('#editprice').val());

        formData.append('stock', $('#editstock').val());

        formData.append('description', $('#editdescription').val());

        if($('#editimage')[0].files.length>0) formData.append('image', $('#editimage')[0].files[0]);

        $('#editColorContainer .color-row').each(function(index){

            formData.append(`colors[${index}][color_name]`, $(this).find('input').eq(0).val());

            formData.append(`colors[${index}][qty]`, $(this).find('input').eq(1).val());

        });

        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $('#editProductForm .error-text').text('');



        $.ajax({

            url: '{{ route("admin.product.update") }}',

            type: 'POST',

            data: formData,

            contentType:false,

            processData:false,

            success:function(response){

                if(response.success){

                    Toast.fire({ icon:'success', title:response.message });

                    $('#editModal').modal('hide');

                    table.ajax.reload();

                }

            },

            error:function(xhr){

                if(xhr.status===422){

                    let errors=xhr.responseJSON.errors;

                    $('#editProductForm .error-text').text('');

                    for(let field in errors){

                        let fieldName=field.includes('colors')?field.replace(/\./g,'_'):field;

                        $('#editProductForm .error-text').each(function(){

                            if($(this).prev().attr('id')===fieldName) $(this).text(errors[field][0]);

                        });

                    }

                } else Toast.fire({ icon:'error', title:'An unexpected error occurred.' });

            }

        });

    });



    // Status

    window.updateProductStatus = function(id, status){

        const message=status=='active'?"Product will be active.":"Product will be inactive.";

        Swal.fire({

            title:"Are you sure?",

            text:message,

            icon:"warning",

            showCancelButton:true,

            confirmButtonText:"Yes",

        }).then((result)=>{

            if(result.isConfirmed){

                $.post('{{ route("admin.product.status") }}', { id, status, _token:$('meta[name="csrf-token"]').attr('content') }, function(response){

                    if(response.success) Toast.fire({ icon:'success', title:response.message });

                    table.ajax.reload();

                });

            }

        });

    };



    // Delete

    window.deleteProduct = function(id){

        Swal.fire({

            title:"Are you sure?",

            text:"Do you want to delete this product?",

            icon:"warning",

            showCancelButton:true,

            confirmButtonText:"Yes",

        }).then((result)=>{

            if(result.isConfirmed){

                const url='{{ route("admin.product.destroy", ":id") }}'.replace(':id', id);

                $.ajax({

                    url:url,

                    type:'DELETE',

                    data:{ _token:$('meta[name="csrf-token"]').attr('content') },

                    success:function(response){

                        if(response.success) Toast.fire({ icon:'success', title:response.message });

                        table.ajax.reload();

                    }

                });

            }

        });

    };



});

</script>

@endsection

