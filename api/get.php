
<?php

require 'connection.php';
$out = [];

//$_SESSION['usertype'] = "student";
$_SESSION['mess_id'] = 1001;
$_SESSION['rollno'] = "B130112CS";
$_SESSION['month'] = "2015-10";

$out = [];



if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {

    if (isset($_GET['querytype'])) {

        $type = $_GET['querytype'];

        if ($_SESSION['usertype'] == 'mess') {
            switch ($type) {
                case 'available_students' : $out = list_available();
                    break;
                case 'added_students' : $out = added_members();
                    break;
                case 'extras_history': $out = extras_mess_perday();
                    break;
                case 'leave_history': $out = history_messcut();
                    break;
                case 'billings': $out = month_bill();
                    break;
                case 'analysis' : $out = mess_amount_analysis();
                    break;
                case 'forum' : $out = forum_post_mess();
                    break;
                case 'allextras' : $out = listAllExtras();
                    break;
                case 'messinfo' : $out = mess_info();
                    break;
                case 'monthextralist' : $out = month_extras_student();
                    break;
               
                case 'messdetails' : $out = details_mess();
                    break;
                case 'getmonths': $out = getMonths();
                    break;
            }
        } else if ($_SESSION['usertype'] == 'student') {
            switch ($type) {
                case 'ratings': $out = ratings_view();
                    break;
                case 'forum': $out = forum_post_student();
                    break;
                case 'month_bill' :$out = month_bill_student();
                    break;
                case 'getmonths': $out = getMonths();
                    break;
                case 'studentdetails' : $out = details_student();
                    break;
                 case 'monthbillstudent' : $out = month_bill_student();
                    break;
                case 'monthextralist' : $out = month_extras_student();
                    break;
            }
        }
    } else {
        $out['status'] = 'fail';
        $out['error'] = "querytype not set.";
    }
} else {
    $out['status'] = 'fail';
    $out['error'] = "authentication failure";
}

header('Content-type: text/plain');
echo json_encode($out, JSON_PRETTY_PRINT);

function getMonths() {
    global $conn;

    $output = [];
    $sql = "SELECT DISTINCT DATE_FORMAT( t.StartDate, '%Y-%m') AS date FROM messjoins as t ORDER BY t.`StartDate`";
    $result = $conn->query($sql);
    if ($result) {

        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $date = $row;
            $d = split("-", $date['date']);
            $monthNum = $d[1];
            $dateObj = DateTime::createFromFormat('!m', $monthNum);
            $monthName = $dateObj->format('F');
            $df = $d[0]." ".$monthName;
            $data[] = ["month"=>$df,"format" => $date['date']];
        }
        $output['data'] = $data;
    } else {
        echo 'nope';
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to check if rollno exists
function rollexist() {
    $rollno = $_SESSION['rollno'];
    global $conn;
    //var_dump($conn);
    $output = [];
    $sql = "select * from members where RollNo='$rollno';";


    $result = $conn->query($sql);
    if ($result) {

        $output['status'] = "success";
        $data = [];
        if ($result->num_rows > 0)
            $data = ['exists' => true];
        else {
            $data = ['exists' => false];
        }
        $output['data'] = $data;
    } else {
        echo 'nope';
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to check the details of students' current extras
function current_extras() {
    $rollno = $_SESSION['rollno'];
    global $conn;
    global $db;
    $output = [];
    $month = date("Y-m");
    $sql = "select ExtrasName,DateTime from Extras,ExtrasTaken where " .
            "ExtrasTaken.ExtrasID = Extras.ExtrasID and RollNo = '$rollno' and DateTime like '$month-%' ";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to check the history of students all extras
function history_extras() {
    $rollno = $_SESSION['rollno'];
    global $conn;
    $output = [];
    $month = date("Y-M");
    $sql = "select ExtrasName,DateTime from Extras,ExtrasTaken where " .
            "ExtrasTaken.ExtrasID = Extras.ExtrasID and RollNo = '$rollno' ";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to find history of members added to a mess

function added_members() {
    $mess_id = $_SESSION['mess_id'];
    global $conn;
    $output = [];
    $sql = "select MemberName as name,StartDate as startdate,Members.RollNo as rollno from Members,MessJoins where " .
            "Members.RollNo = MessJoins.RollNo and MessID = '$mess_id' order by StartDate desc";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to find history of extras taken from a mess 

function extras_mess_history() {
    $mess_id = $_SESSION['mess_id'];
    global $conn;
    $output = [];
    $sql = "select MemberName,DateTime,Members.RollNo as RollNo,Extras.ExtrasID as ExtrasID, ExtrasName from Members,MessJoins,Mess,Extras,ExtrasTaken where " .
            "Members.RollNo = MessJoins.RollNo and Members.RollNo = ExtrasTaken.RollNo and ExtrasTaken.ExtrasID = Extras.ExtrasID and Mess.MessID = '$mess_id' order by DateTime desc";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to find history of messcuts in a mess
function history_messcut() {
    $mess_id = $_SESSION['mess_id'];
    global $conn;
    $output = [];
    $month = date("Y-m");
    $sql = "select MemberName as name ,Members.RollNo as rollno,FromDate as startdate ,ToDate as enddate from Members,MessCut where " .
            "Members.RollNo = MessCut.RollNo and MessID = $mess_id and FromDate like '$month-%' order by FromDate desc";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to find bill of all students in any given month month
function month_bill() {
    $mess_id = $_SESSION['mess_id'];
    $month = isset($_GET['month']) ? $_GET['month'] : date("Y-m");
    global $conn;
    $output = [];

    $no_days = 0;
    $cut_days = 0;
    $extras_total = 0;
    $perdayrate = 0;
    $day = date("Y-m-d");


    $sql = "select mj.StartDate as startdate, mj.RollNo as rollno,mem.MemberName as name, m.PerDayRate as perdayrate, "
            . "(select sum(DATEDIFF(ToDate,FromDate)) from MessCut,Members where MessCut.RollNo = Members.RollNo and Members.RollNo = mj.RollNo and FromDate like '$month-%') as cuts, "
            . "(select sum(Price) from ExtrasTaken,Extras where ExtrasTaken.ExtrasId = Extras.ExtrasId and ExtrasTaken.Rollno = mj.RollNo and DateTime like '$month-%') as extras "
            . "from MessJoins as mj,Mess as m, Members as mem where mem.RollNo = mj.RollNo and mj.MessId = m.MessId and mj.StartDate like '$month-%' and m.MessId = $mess_id";
    $result = mysqli_query($conn, $sql);


    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;


    return $output;
}

//Function for analysis of extras for a mess
function mess_amount_analysis() {
    $mess_id = $_SESSION['mess_id'];
    global $conn;
    $output = [];
    $sql = "select ExtrasName as name ,sum(Price) as amount , count(*) as count from Members, MessJoins, Mess, ExtrasTaken, Extras where " .
            "Members.RollNo = MessJoins.RollNo and Members.RollNo = ExtrasTaken.RollNo and ExtrasTaken.ExtrasID = Extras.ExtrasID and Mess.MessID = $mess_id group by Extras.ExtrasID";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to list all available students
function list_available() {
    $month = date("Y-m");
    global $conn;
    $output = [];
    $sql = "select MemberName as name, RollNo as rollno from Members where Members.RollNo not in" .
            "(select Members.RollNo as RollNo from Members, MessJoins where Members.RollNo=MessJoins.RollNo and StartDate like '$month-%') ";


    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result) {
        $output['status'] = "success";
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to find list of extras taken from a mess per day
function extras_mess_perday() {
    $mess_id = $_SESSION['mess_id'];
    global $conn;
    $output = [];
    $day = date("Y-m-d");
    $sql = "select MemberName as student ,DateTime as time ,Members.RollNo as rollno,Extras.ExtrasID as extrasid, ExtrasName as name,Price as price from Members,MessJoins,Mess,Extras,ExtrasTaken where " .
            "Members.RollNo = MessJoins.RollNo and Members.RollNo = ExtrasTaken.RollNo and ExtrasTaken.ExtrasID = Extras.ExtrasID and Mess.MessID = $mess_id and DateTime like '$day%'";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to view the forum of the given mess
function listAllExtras() {
    $mess_id = $_SESSION['mess_id'];
    global $conn;
    $output = [];
    $sql = "select ExtrasID as id, ExtrasName as name, Price as price from extras;";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

function forum_post_mess() {
    $mess_id = $_SESSION['mess_id'];
    global $conn;
    $output = [];
    $sql = "select Members.RollNo as rollno, MemberName as name, DATE_FORMAT(DateTime, '%Y-%m-%dT%TZ') as time, Comment as details from Members,Forum,Mess where " .
            "Members.RollNo = Forum.RollNo and Forum.MessID = Mess.MessID and Mess.MessID = $mess_id order by DateTime desc";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to view details of given mess
function mess_info() {
    $mess_id = $_SESSION['mess_id'];
    global $conn;
    $output = [];
    $sql = "select MessID as messid ,MessName as name ,MessCoordinator as coordinator,PerDayRate as perdayrate from Mess where Mess.MessID = $mess_id";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        $row = $result->fetch_assoc();
        $data = $row;
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to view the forum of the students' current mess
function forum_post_student() {
    $rollno = $_SESSION['rollno'];
    global $conn;
    $output = [];
    $sql = "select MessName, Members.RollNo as RollNo, MemberName,DATE_FORMAT(DateTime, '%Y-%m-%dT%TZ')as DateTime , Comment from Members,Forum,Mess,MessJoins where " .
            "Members.RollNo = MessJoins.RollNo and MessJoins.MessID = Mess.MessID and Forum.MessID = Mess.MessID and Members.RollNo='$rollno' order by DateTime desc";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to view mess ratings
function ratings_view() {
    $rollno = $_SESSION['rollno'];
    global $conn;
    $output = [];
    $sql = "select MessName, avg(RatingValue) as rating from Mess,Rating where Mess.MessID=Rating.MessID group by MessName";

    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    
    return $output;
}

//Function to find bill of a student in any given month month
function month_bill_student() {
    
    if(!isset($_GET['rollno'])||!isset($_GET['month'])){
        $output['status'] = 'fail';
        $output['error'] = "insufficient details";
        return $output;
    }
    
    $rollno = escape($_GET['rollno']);
    $month = escape($_GET['month']);
    global $conn;
    $output = [];

    $sql = "select mj.StartDate, mj.RollNo, m.PerDayRate, "
            . "(select sum(DATEDIFF(ToDate,FromDate)) from MessCut,Members where MessCut.RollNo = Members.RollNo and Members.RollNo = mj.RollNo and FromDate like '$month-%') as CutDays, "
            . "(select sum(Price) from ExtrasTaken,Extras where ExtrasTaken.ExtrasId = Extras.ExtrasId and ExtrasTaken.Rollno = mj.RollNo and DateTime like '$month-%') as ExtrasTotal "
            . "from MessJoins as mj,Mess as m where mj.MessId = m.MessId and mj.StartDate like '$month-%' and mj.RollNo = '$rollno'";
    $result1 = mysqli_query($conn, $sql);
    
    $sql = "select ExtrasName as name, Price as amount,DATE_FORMAT(DateTime, '%Y-%m-%dT%TZ') as time from "
            . "ExtrasTaken,Extras where ExtrasTaken.ExtrasId = Extras.ExtrasId and DateTime like '$month-%' and RollNo = '$rollno' order by DateTime desc";
    $result2 = mysqli_query($conn, $sql);
    
    if ($result1&&$result2) {
        $output['status'] = "success";
        $details = [];
        while ($row = $result1->fetch_assoc()) {
            $details = $row;
        }
        $extras = [];
        while ($row = $result2->fetch_assoc()) {
            $extras[] = $row;
        }
        
        $output['data'] = $details;
        $output['data']['extras'] = $extras;
        
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to find extras of a student in any given month month
function month_extras_student() {
    $rollno = $_SESSION['rollno'];
    $month = $_SESSION['month'];
    global $conn;
    $output = [];

    $sql = "select ExtrasName as name, Price as amount,DATE_FORMAT(DateTime, '%Y-%m-%dT%TZ') as time from "
            . "ExtrasTaken,Extras where ExtrasTaken.ExtrasId = Extras.ExtrasId and DateTime like '$month-%' and RollNo = '$rollno' order by DateTime desc";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $output['status'] = "success";
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }




    return $output;
}

//Function to find details a student
function details_student() {
    $rollno = $_SESSION['rollno'];
    global $conn;
    $output = [];
    $month = date('Y-m');
    //$sql = "select m.MemberName as name, m.RollNo as rollno, m.PhoneNo as phone, j.StartDate, j.MessId from members as m, messjoins as j where RollNo = '$rollno' and m.RollNo = j.Rollno and j.StartDate";
    $sql = "select m.MemberName as name, m.RollNo as rollno, m.PhoneNo as phone, "
            . "(select MessId from messjoins where RollNo = m.RollNo and startDate like '$month-%') as mess from members as m where RollNo = '$rollno';";
    
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $output['status'] = "success";
        //$data
        while ($row = $result->fetch_assoc()) {
            $data = $row;
        }
        $output['data'] = $data;
    } else {
        $output['status'] = 'fail';
        $output['error'] = "query error";
    }
    return $output;
}

//Function to find details a mess
function details_mess() {
    $messid = $_SESSION['mess_id'];
    global $conn;
    $output = [];

    $sql = "select * from Mess where MessID = $messid";
    $result = mysqli_query($conn, $sql);
    while ($row = $result->fetch_assoc()) {
        $output[] = $row;
    }


    return $output;
}

?>