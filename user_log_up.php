<?php
session_start();
?>
<div class="table-responsive" style="max-height: 500px;background: #F2C464;"> 
  <table class="table">
    <thead class="table-primary">
      <tr style="background: #F2C464;">
        <th>ID</th>
        <th>Card UID</th>
        <th>Name</th>
        <th>Date/Time</th>
        <th>Time Out</th>
        <th>Time In</th>
        <th>reason</th>
        <th>Return Time Estimate</th>
        <th>Deadline Time</th>
        <th>Warning Sent</th>
      </tr>
    </thead>

    <tbody class="table-secondary">
    <?php

//Connect to database
require'connectDB.php';
$searchQuery = " ";
$Start_date = " ";
$End_date = " ";
$Start_time = " ";
$End_time = " ";
$Card_sel = " ";

if (isset($_POST['log_date'])) {
  //Start date filter
  if ($_POST['date_sel_start'] != 0) {
      $Start_date = $_POST['date_sel_start'];
      $_SESSION['searchQuery'] = "checkindate='".$Start_date."'";
  }
  else{
      $Start_date = date("Y-m-d");
      $_SESSION['searchQuery'] = "checkindate='".date("Y-m-d")."'";

  }
  //End date filter
  if ($_POST['date_sel_end'] != 0) {
      $End_date = $_POST['date_sel_end'];
      $_SESSION['searchQuery'] = "checkindate BETWEEN '".$Start_date."' AND '".$End_date."'";
  }
  //Time-In filter
  if ($_POST['time_sel'] == "Time_in") {
    //Start time filter
    if ($_POST['time_sel_start'] != 0 && $_POST['time_sel_end'] == 0) {
        $Start_time = $_POST['time_sel_start'];
        $_SESSION['searchQuery'] .= " AND timein='".$Start_time."'";
    }
    elseif ($_POST['time_sel_start'] != 0 && $_POST['time_sel_end'] != 0) {
        $Start_time = $_POST['time_sel_start'];
    }
    //End time filter
    if ($_POST['time_sel_end'] != 0) {
        $End_time = $_POST['time_sel_end'];
        $_SESSION['searchQuery'] .= " AND timein BETWEEN '".$Start_time."' AND '".$End_time."'";
    }
  }
  //Time-out filter
  if ($_POST['time_sel'] == "Time_out") {
    //Start time filter
    if ($_POST['time_sel_start'] != 0 && $_POST['time_sel_end'] == 0) {
        $Start_time = $_POST['time_sel_start'];
        $_SESSION['searchQuery'] .= " AND timeout='".$Start_time."'";
    }
    elseif ($_POST['time_sel_start'] != 0 && $_POST['time_sel_end'] != 0) {
        $Start_time = $_POST['time_sel_start'];
    }
    //End time filter
    if ($_POST['time_sel_end'] != 0) {
        $End_time = $_POST['time_sel_end'];
        $_SESSION['searchQuery'] .= " AND timeout BETWEEN '".$Start_time."' AND '".$End_time."'";
    }
  }
  //Card filter
  if ($_POST['card_sel'] != 0) {
      $Card_sel = $_POST['card_sel'];
      $_SESSION['searchQuery'] .= " AND card_uid='".$Card_sel."'";
  }
}

if ($_POST['select_date'] == 1) {
    $Start_date = date("Y-m-d");
    $_SESSION['searchQuery'] = "checkindate='".$Start_date."'";
}

// $sql = "SELECT * FROM users_logs WHERE checkindate=? AND pic_date BETWEEN ? AND ? ORDER BY id ASC";
$sql = "SELECT * FROM logs ORDER BY id ASC";

$result = mysqli_stmt_init($conn);

if (!mysqli_stmt_prepare($result, $sql)) {
    echo '<p class="error">SQL Error</p>';
}
else{
    mysqli_stmt_execute($result);
    $resultl = mysqli_stmt_get_result($result);
    if (mysqli_num_rows($resultl) > 0){
        while ($row = mysqli_fetch_assoc($resultl))
        {
?>
          <TR>
          <TD><?php echo $row['id'];?></TD>
          <TD><?php echo $row['uid'];?></TD>
          <TD><?php echo $row['name'];?></TD>
          <TD><?php echo $row['timestamp'];?></TD>
          <TD><?php echo $row['exit_time'];?></TD>
          <TD><?php echo $row['return_time'];?></TD>
          <TD><?php echo $row['reason'];?></TD>
          <TD><?php echo $row['return_time_estimate'];?></TD>
          <TD><?php echo $row['deadline_time'];?></TD>
          <TD><?php echo $row['warning_sent'];?></TD>


          </TR>
<?php
        }
    }
}
echo $sql;
?>
    </tbody>
  </table>
</div>