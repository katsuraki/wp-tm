<?php /* Template Name: ajax-driver-head */ ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
	// submit function
	$(function () {

        $('form').on('submit', function (e) {

          e.preventDefault();
          
		var request_method = $(this).attr("method"); //get form GET/POST method
		var form_data = $(this).serialize(); //Encode form elements for submission
		//var x = $("input[name='submit']",this).val(); 
		console.log("Check data "+form_data);
		/* 		
		
		var driver_id = $("#driverID").val();
		var tamzang_id = $("#TamzangId").val();
		console.log("AJAX @ Check Box Value is "+form_data);
		  */
		//console.log("AJAX @ Check Box Value X is "+x );
          $.ajax({
            type: "POST",
            url: ajaxurl,
            //data: {"action": "update_driver_piority", form_data},
			data:form_data,
            success: function (resultPhp) {
              alert("ทำการเปลี่ยน Head เรียบร้อย");
			  location.reload();
            }
          });

        });

    });
    </script>
<?php
global $wpdb;
echo '<script type="text/javascript">
           var ajaxurl = "' .admin_url('admin-ajax.php'). '";
         </script>';
		 
//$num_driver = 2;
// Get Value from Ajax

$super_driver_id = get_query_var('super_id');
$driver_id = get_query_var('driver_id');

//Get supervisor name
$super_driver_name = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT driver_name FROM driver WHERE Driver_id=%d", array($super_driver_id)
	)
);

//Get Driver name
$driver_name = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT driver_name FROM driver WHERE Driver_id=%d", array($driver_id)
	)
);

?>

<div class="driver-row">
	<p><strong><?php echo "Driver ID : ".$driver_id." Driver Name: ".$driver_name; ?></strong></p>
	<p style="color: red;"><?php echo "Supervisor ID : ".$super_driver_id." Supervisor Name: ".$super_driver_name; ?></p>
	
	<form id="driverForm">
		<input type="text" name="driverSuperID"> ใส่ Driver ID คนใหม่ที่ต้องการให้เป็น Head ที่ Driverระบุ ID ประจำตัวมา<br>
		<input type="hidden" id="orderID_<?php echo $super_driver_id; ?>" name="driverID" value="<?php echo $driver_id; ?>"/>
		<input type="hidden" name="action" value="update_head_driver" />
		<input name="submit" type="submit" value="Submit" />
	</form>
</div>
<?php



?>
