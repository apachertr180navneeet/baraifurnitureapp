$(document).ready(function () {

    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const table = $('#salespersonTable').DataTable({
        processing: true,
        ajax: {
            url: getSalespersonUrl,
            type: 'GET',
        },
        columns: [
            { data: "salesperson_code" },

            { data: "name" },

            { data: "mobile" },

            { data: "email" },

            {
                data: "status",
                render: (data) =>
                    data === "active"
                        ? '<span class="badge bg-label-success">Active</span>'
                        : '<span class="badge bg-label-danger">Inactive</span>'
            },

            {
                data: "action",
                render: (data, type, row) => {

                    const statusBtn = row.status === 'inactive'
                        ? `<button class="btn btn-sm btn-success me-1" onclick="updateStatus(${row.id}, 'active')">Activate</button>`
                        : `<button class="btn btn-sm btn-danger me-1" onclick="updateStatus(${row.id}, 'inactive')">Deactivate</button>`;

                    const editBtn = `
                        <button class="btn btn-sm btn-warning me-1"
                            onclick="editSalesperson(${row.id})">
                            Edit
                        </button>
                    `;

                    const deleteBtn = `
                        <button class="btn btn-sm btn-danger"
                            onclick="deleteSalesperson(${row.id})">
                            Delete
                        </button>
                    `;

                    return `${statusBtn}${editBtn}${deleteBtn}`;
                }
            }
        ],
    });

    // Flash message helper
    function setFlash(type, message) {
        Toast.fire({
            icon: type,
            title: message
        });
    }

});