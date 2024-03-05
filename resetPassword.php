<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FFF6D9; /* Orange 50 */
        }
        .error {
            color: red; /* Set the text color to red */
        }
        .reset-container {
            max-width: 400px;
            margin: auto;
            margin-top: 10%;
        }
        .reset-card {
            padding: 20px;
            width: 80%; /* Adjust the width here */
        }
        .reset-form {
            margin-bottom: 20px;
        }
        .reset-btn {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 reset-container">
                <div class="reset-card card shadow">
                    <h2 class="text-center mb-4">Reset Password</h2>
                    <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
                    <div class="card-body">
                        <!-- Reset password form -->
                        <form action="resetPasswordBackend.php" method="post" class="reset-form">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary reset-btn">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
