<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Delete - barailife</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .privacy-header {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 60px 0;
        }
        .privacy-section h5 {
            margin-top: 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<!-- Header Section -->
<section class="privacy-header text-center">
    <div class="container">
        <h1 class="fw-bold">Delete User</h1>
    </div>
</section>

<!-- Content Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Delete User by Mobile Number</h5>
                    </div>

                    <div class="card-body">
                        <form id="deleteUserForm">
                            
                            <!-- Laravel CSRF -->
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">

                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile Number</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="mobile" 
                                    name="mobile" 
                                    placeholder="Enter Mobile Number"
                                    maxlength="15">
                                <div class="invalid-feedback">
                                    Please enter a valid mobile number (10-15 digits).
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger">
                                    Delete User
                                </button>
                            </div>
                        </form>

                        <div id="responseMessage" class="mt-3"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-light py-4">
    <div class="container text-center">
        <p class="mb-0">© 2026 barailife. All rights reserved.</p>
    </div>
</footer>

<!-- jQuery (IMPORTANT) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function(){

    $('#deleteUserForm').on('submit', function(e){
        e.preventDefault();

        let mobile = $('#mobile').val().trim();
        let mobilePattern = /^[0-9]{10,15}$/;

        // Reset UI
        $('#mobile').removeClass('is-invalid');
        $('#responseMessage').html('');

        // Validation
        if(!mobilePattern.test(mobile)){
            $('#mobile').addClass('is-invalid');
            return;
        }

        // Confirmation
        if(!confirm("Are you sure you want to delete this user?")){
            return;
        }

        $.ajax({
            url: "{{ route('user.delete.by.mobile') }}",
            type: "POST", // Using POST + _method DELETE
            data: $(this).serialize(),
            success: function(response){

                $('#responseMessage').html(`
                    <div class="alert alert-success alert-dismissible fade show">
                        ${response.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);

                $('#deleteUserForm')[0].reset();
            },
            error: function(xhr){

                let message = "Something went wrong.";

                if(xhr.responseJSON && xhr.responseJSON.message){
                    message = xhr.responseJSON.message;
                }

                $('#responseMessage').html(`
                    <div class="alert alert-danger alert-dismissible fade show">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
            }
        });

    });

});
</script>

</body>
</html>