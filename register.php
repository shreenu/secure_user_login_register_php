<?php
require_once __DIR__.'/user.php';
$user = new user();
$user->sec_session_start();
if($user->login_check())
    header("location: userpage.php");


if(isset($_POST['username'],
        $_POST['email'],
        $_POST['password'],
        $_POST['confpassword'],
        $_POST['p'])){
    $status = $user->register($_POST['username'], $_POST['email'], $_POST['p']);
}


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Home</title>
        <!-- Bootstrap Core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <!-- Custom CSS -->
        <link href="css/style.css" rel="stylesheet">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
    <div class="wrap">
    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"> <i class="glyphicon glyphicon-leaf"></i> User</a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>
    <!-- Mail Container -->
    <div class="container">
        <div class="row">
            <?php
                if(!empty($status)){
                    if($status['error_status'] === TRUE){
                        ?>
            <div class="alert alert-warning" role="alert">
                <strong>Warning!</strong>
                <?= $status['error_email'] != NULL ?$status['error_email']."<br/>":"" ?>
                <?= $status['error_password'] != NULL ?$status['error_password']."<br/>":"" ?>
                <?= $status['error_msg'] != NULL ?$status['error_msg']."<br/>":"" ?>
                <?= $status['error_username'] != NULL ?$status['error_username']."<br/>":"" ?>
            </div>
                        <?php
                    }else{
                        ?>
            <div class="alert alert-success" role="alert">
                <strong>Success!</strong>
                Your Registration is completed please login.
            </div>
                        <?php
                    }
                }
            ?>
            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-default">
                <div class="panel-heading">Register</div>
                <div class="panel-body">
                    <form method="post" action="<?= $user->esc_url($_SERVER['PHP_SELF']); ?>" id="register-form">
                        <div class="form-group">
                            <label for="Usernameinput">Username</label>
                            <input type="text" name="username" class="form-control" id="Usernameinput" placeholder="Username">
                        </div>
                        <div class="form-group">
                            <label for="emailinput">Email address</label>
                            <input type="email" name="email" class="form-control" id="emailinput" placeholder="Email">
                        </div>
                        <div class="form-group">
                            <label for="InputPassword">Password</label>
                            <input type="password" name="password" class="form-control" id="password" placeholder="Password">
                        </div>
                        <div class="form-group">
                            <label for="ConfInputPassword">Confirm Password</label>
                            <input type="password" name="confpassword" class="form-control" id="confpassword" placeholder="Confirm Password">
                        </div>
                        <div class="form-group">
                            <input type="submit" value="Register" class="btn btn-primary">
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
    <!-- End Mail Container -->
    </div>
    </div>
    <footer class="footer">
        <div class="container">
                <p class="pull-left">© User 2015</p>

                <p class="pull-right">Privacy Policy | Terms | Blog</p>
        </div>
    </footer>
    <!-- jQuery -->
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/crypto.js"></script>
    <script src="js/user.js"></script>
    </body>
</html>