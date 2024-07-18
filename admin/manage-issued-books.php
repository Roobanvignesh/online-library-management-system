<?php
session_start();
error_reporting(0);
require '../vendor/autoload.php';
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
    {   
header('location:index.php');
}
else{ 



    ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Online Library Management System | Manage Issued Books</title>
    <!-- BOOTSTRAP CORE STYLE  -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FONT AWESOME STYLE  -->
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <!-- DATATABLE STYLE  -->
    <link href="assets/js/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <!-- CUSTOM STYLE  -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <!-- GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />

</head>
<body>
      <!------MENU SECTION START-->
<?php include('includes/header.php');?>
<!-- MENU SECTION END-->
    <div class="content-wrapper">
         <div class="container">
        <div class="row pad-botm">
            <div class="col-md-12">
                <h4 class="header-line">Manage Issued Books</h4>
    </div>
     <div class="row">
    <?php if($_SESSION['error']!="")
    {?>
<div class="col-md-6">
<div class="alert alert-danger" >
 <strong>Error :</strong> 
 <?php echo htmlentities($_SESSION['error']);?>
<?php echo htmlentities($_SESSION['error']="");?>
</div>
</div>
<?php } ?>
<?php if($_SESSION['msg']!="")
{?>
<div class="col-md-6">
<div class="alert alert-success" >
 <strong>Success :</strong> 
 <?php echo htmlentities($_SESSION['msg']);?>
<?php echo htmlentities($_SESSION['msg']="");?>
</div>
</div>
<?php } ?>



   <?php if($_SESSION['delmsg']!="")
    {?>
<div class="col-md-6">
<div class="alert alert-success" >
 <strong>Success :</strong> 
 <?php echo htmlentities($_SESSION['delmsg']);?>
<?php echo htmlentities($_SESSION['delmsg']="");?>
</div>
</div>
<?php } ?>

</div>


        </div>
        <div class="row">
    <div class="col-md-12">
        <!-- Advanced Tables -->
        <div class="panel panel-default">
            <div class="panel-heading">
                Issued Books
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Student Email</th>
                                <th>Book Name</th>
                                <th>ISBN </th>
                                <th>Issued Date</th>
                                <th>Return Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT tblstudents.FullName, tblstudents.EmailId, tblbooks.BookName, tblbooks.ISBNNumber, tblissuedbookdetails.IssuesDate, tblissuedbookdetails.ReturnDate, tblissuedbookdetails.id as rid FROM tblissuedbookdetails JOIN tblstudents ON tblstudents.StudentId=tblissuedbookdetails.StudentId JOIN tblbooks ON tblbooks.id=tblissuedbookdetails.BookId ORDER BY tblissuedbookdetails.id DESC";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1;
                            ?>
                            <?php foreach ($results as $result) : ?>
                                <tr class="odd gradeX">
                                    <td class="center"><?php echo htmlentities($cnt); ?></td>
                                    <td class="center"><?php echo htmlentities($result->FullName); ?></td>
                                    <td class="center"><?php echo htmlentities($result->EmailId); ?></td>
                                    <td class="center"><?php echo htmlentities($result->BookName); ?></td>
                                    <td class="center"><?php echo htmlentities($result->ISBNNumber); ?></td>
                                    <td class="center"><?php echo htmlentities($result->IssuesDate); ?></td>
                                    <td class="center"><?php
                                        if ($result->ReturnDate == "") {
                                            echo htmlentities("Not Return Yet");
                                        } else {
                                            echo htmlentities($result->ReturnDate);
                                        }
                                        ?></td>
                                    <td class="center">
                                        <a href="update-issue-bookdeails.php?rid=<?php echo htmlentities($result->rid); ?>" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a>
                                    </td>
                                </tr>
                                <?php $cnt = $cnt + 1; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Button to trigger sending reminders -->
                <button id="sendRemindersBtn" class="btn btn-success">Send Reminders</button>
            </div>
        </div>
    </div>
</div>
                    <!--End Advanced Tables -->
                </div>
            </div>


            
    </div>
    </div>
    <!-- JavaScript to trigger sending reminders -->
    <script>
    document.getElementById("sendRemindersBtn").addEventListener("click", function() {
        // Send reminders here using AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "send_reminders.php", true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                if (xhr.responseText.trim() !== '') {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            var details = response.details;
                            var message = "Reminders sent successfully.";
                            if (details.length > 0) {
                                message += "\nDetails:";
                                details.forEach(function(detail) {
                                    message += "\nEmail: " + detail.email + ", Status: " + detail.status;
                                    if (detail.status === 'failed') {
                                        message += ", Error: " + detail.error;
                                    }
                                });
                            }
                            console.log(message);
                            alert(message);
                            location.reload(); // Reload the page after sending reminders
                        } else {
                            alert("Error sending reminders: " + response.message);
                        }
                    } catch (error) {
                        console.error("Error parsing JSON: " + error);
                        alert("Error parsing JSON response.");
                    }
                } else {
                    console.error("Empty JSON response.");
                    alert("Empty JSON response.");
                }
            } else {
                console.error("Error in AJAX request: " + xhr.statusText);
                alert("Error sending reminders. Please try again.");
            }
        };
        xhr.send();
    });
</script>


     <!-- CONTENT-WRAPPER SECTION END-->
  <?php include('includes/footer.php');?>
      <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE THE LOADING TIME  -->
    <!-- CORE JQUERY  -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!-- BOOTSTRAP SCRIPTS  -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- DATATABLE SCRIPTS  -->
    <script src="assets/js/dataTables/jquery.dataTables.js"></script>
    <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
      <!-- CUSTOM SCRIPTS  -->
    <script src="assets/js/custom.js"></script>
</body>
</html>
<?php } ?>
