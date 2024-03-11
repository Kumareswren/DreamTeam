<?php
require_once 'tokenVerify.php';

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
 integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
 
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <title>Tutor Dashboard</title>

    <!-- <script>
        
        function verifyToken(token) {
        }

        var jwtToken = getCookie('token');

        if (jwtToken && verifyToken(jwtToken)) {

        } 
        else {
        
            window.location.href = 'index.php';
        }
    </script>
 -->
    <style>

  /* for e-Tutor */ .min-vh-100 .fs-5 {
    color: rgb(255, 255, 255); 
    font-family: "garamond"; 
    
}

/* for Sidebar items */  #menu .nav-link .d-none.d-sm-inline {
    color: #ffffff;
}

.custom-div {
    background-color: #FFF6D9;
    padding: 20px;
}

.nav-link {
    font-family: Arial, sans-serif; 
    font-size: 11px; 
    font-weight: 350; 
    padding: 13px 38px;
}

.nav-item:hover{
    color:floralwhite;
    
}

.nav-link:hover{
    background-color: #00425A;
    color: #ffffff;
}

.nav-item:hover .nav-link {
    background-color: #00425A;
    color: #ffffff;
}

.bi-house-fill {
    color: #8fc8bd;
}


.bi-journal-text{
    color: #8fc8bd;
}

.bi-table{
    color: #8fc8bd;
}

.bi-book{
    color: #8fc8bd;
}

.bi-newspaper{
    color: #8fc8bd;
}


.bi-envelope{
    color: #8fc8bd;
}


.bi-box-arrow-left{
    color: #8fc8bd;
}


.bg-secondary{
    background-color: #1F8A70!important;
}

.login-logo{
    display: block;
    width: 40px;
    height: 40px;
    transform: translateY(32px);
}

  </style>
</head>

<body>
    
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-secondary">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        
                        <span class="fs-5 d-none d-sm-inline">
                        <img src="icons/online-learning.png" alt="Education Logo" class="login-logo">
                        <span class="fs-5 d-none d-sm-inline etutor-text" style="margin-left: 50px;">e-Tutor</span>
                    </span>
                    </a> 
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start" id="menu">
                        
                      <li class="nav-item">
                          <a href="#" class="nav-link align-middle px-10">
                              <i class="fs-4 bi-house-fill"></i> <span class="ms-1 d-none d-sm-inline">Home</span>
                          </a>
                      </li>

                    
                    <li class="nav-item">
                                <a href="#" class="nav-link align-middle px-10 courses-link">
                            <i class="fs-4 bi-journal-text"></i> <span class="ms-1 d-none d-sm-inline">Courses</span>
                        </a>
                    </li>

                        <li class="nav-item">
                          <a href="#" class="nav-link align-middle px-10">
                              <i class="fs-4 bi-house-fill"></i> <span class="ms-1 d-none d-sm-inline">Chat</span>
                          </a>
                      </li>
                      
                      <li>
                          <a href="#" class="nav-link px-10 align-middle">
                              <i class="fs-4 bi-table"></i> <span class="ms-1 d-none d-sm-inline">Meetings</span></a>
                      </li>
                      
                      <li>
                        <a href="#" class="nav-link px-10 align-middle">
                            <i class="fs-4 bi-book"></i> <span class="ms-1 d-none d-sm-inline">Tutorial</span> </a>
                    </li>

                    <li>
                      <a href="#" class="nav-link px-10 align-middle">
                          <i class="fs-4 bi-newspaper"></i> <span class="ms-1 d-none d-sm-inline">Blog</span> </a>
                    </li>

                    <li>
                      <a href="#" class="nav-link px-10 align-middle">
                          <i class="fs-4 bi-envelope"></i> <span class="ms-1 d-none d-sm-inline">Email</span> </a>
                    </li>


                    <li class="nav-item">
                                <a href="#" class="nav-link align-middle px-10 student-link">
                            <i class="fs-4 bi bi-people"></i> <span class="ms-1 d-none d-sm-inline">Students</span>
                        </a>
                    </li>

                    

                      <li>
                          <a href="logout.php" class="nav-link px-10 align-middle">
                              <i class="fs-4 bi-box-arrow-left"></i> <span class="ms-1 d-none d-sm-inline">Logout</span> </a>
                      </li>

                    </ul>
                    
                </div>
            </div>
            <script>
        $(document).ready(function() {
    
    $('.student-link').click(function(event) {
        //event.preventDefault();
        $.ajax({
            url: 'ViewStudentList.php',
            success: function(data) {
                $('#componentContainer').html(data);
            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
        });
        
    });
});

    </script>
    <script>
        $(document).ready(function() {
    
    $('.courses-link').click(function(event) {
        //event.preventDefault();
        $.ajax({
            url: 'tutorCourses.php',
            success: function(data) {
                $('#componentContainer').html(data);
            },
            error: function(xhr, status, error) {
                console.error('An error occurred:', error);
            }
        });
        
    });
});

    </script>
            <div class="col py-3 custom-div">
                
                <main class="mt-5 pt-3">
                    <div class="container-fluid">
                      <div class="row">
                      <div class="col-md-12" id="componentContainer">
                        </div>
                      </div>
                      <br>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <!-- for charts? --> <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.2/dist/chart.min.js"></script> 
    
  </body>
</html>