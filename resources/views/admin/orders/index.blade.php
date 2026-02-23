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

                data: null,

                render: (data, type, row) => {
                    if (!row.pdf_url) {
                        return `<span class="text-muted">No PDF</span>`;
                    }

                    const pdfUrl = row.pdf_url.startsWith('http')
                        ? row.pdf_url
                        : `{{ url('/') }}/${row.pdf_url.replace(/^\/+/, '')}`;

                    return `<a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-primary">View PDF</a>`;

                }

            }

        ],

    });

});

</script>

@endsection

