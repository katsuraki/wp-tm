<?php /* Template Name: driver-order */ ?>
<?php

?>

<script>

function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
  } else {
    alert("ไม่สามารถยืนยันตำแหน่งของคุณ ณ ปัจจุบันเพื่อส่งรายการอาหารให้ได้");
  }
}
function showPosition(position) {
	var user_id = <?php echo get_current_user_id() ;?>;
	console.log( "update address process" );
	var send_data = 'action=update_driver_location&Lat='+position.coords.latitude+'&Lng='+position.coords.longitude+"&user_id="+user_id;
	console.log(send_data);
	
    jQuery.ajax({
        type: 'POST',
		dataType: 'json',
        url: ajaxurl,
        data: send_data,
		success: function() 
	{
        alert("ยืนยันตำแหน่งเรียบร้อย");
		location.reload();
    },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
           console.log(textStatus);
        }
      });
}

jQuery(document).ready(function($){

    $('#main_order').on("click", '.page-item a', function(event) { 
        console.log($(this).data("page"));
        if($(this).data("page") != null)
        {
            $( "#main_order").load( ajaxurl+"?action=load_order_list&page="+$(this).data("page"), function( response, status, xhr ) {
                    if ( status == "error" ) {
                        var msg = "Sorry but there was an error: ";
                        $( "#main_order" ).html( msg + xhr.status + " " + xhr.statusText );
                    }

                    console.log("Response: "+status);
            });
        }

    });

    $('#confirm-order').on('click', '.btn-ok', function(e) {

        var order_id = $(this).data('id');
        var nonce = $(this).data('nonce');
        console.log( "ยืนยันรับ order: " + order_id );
        

        var send_data = 'action=driver_confirm_order&id='+order_id+'&nonce='+nonce;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "Order confirmed: " + JSON.stringify(msg) );
                if(msg.success){
                    //$( "#confirm_button_"+order_id ).html('<img src="http://test02.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/js/pass.png" />ยืนยันรับคำสั่งซื้อ');
                    $( ".wrapper-loading" ).load( ajaxurl+"?action=load_driver_order_template", function( response, status, xhr ) {
                        if ( status == "error" ) {
                        var msg = "Sorry but there was an error: ";
                        $( ".wrapper-loading" ).html( msg + xhr.status + " " + xhr.statusText );
                        }
                    });
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);

            }
        });

        $('#confirm-order').modal('toggle');

    });

    $('#confirm-order').on('click', '.btn-reject', function(e) {

        var order_id = $(this).data('id');
        var nonce = $(this).data('nonce');
        console.log( "ปฏิเสธ order: " + order_id );
        

        var send_data = 'action=driver_reject_order&id='+order_id+'&nonce='+nonce;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "Order confirmed: " + JSON.stringify(msg) );
                if(msg.success){
                    $( "#confirm_button_"+order_id ).html('<img src="http://test02.tamzang.com/wp-content/themes/GeoDirectory_whoop-child/js/error.png" />ปฏิเสธการสั่งซื้อ');
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);

            }
        });

        $('#confirm-order').modal('toggle');

        });


    $('#confirm-order').on('show.bs.modal', function(e) {
        var data = $(e.relatedTarget).data();
        $('.order-text', this).text(data.text);
        $('.btn-ok', this).data('id', data.id);
        $('.btn-ok', this).data('nonce', data.nonce);
        $('.btn-reject', this).data('id', data.id);
        $('.btn-reject', this).data('nonce', data.nonce);
        //console.log(data);
    });

    function isPositiveInteger(s)
    {
        return /^\d+$/.test(s);
    }

    $('body').on('click', '#confirm-adjust .btn-ok', function(e) {

        var order_id = $(this).data('id');
        var nonce = $(this).data('nonce');
        var adjust = $(this).data('adjust');
        console.log( "adjust order: " + order_id );

        var send_data = 'action=driver_adjust_price&id='+order_id+'&nonce='+nonce+'&adjust='+adjust;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "Order adjusted: " + JSON.stringify(msg) );
                if(msg.success){
                    $( "#order_adjust_"+order_id ).html('<font color="#eb9316"><b>รอลูกค้ายอมรับ</b></font>');
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log(textStatus);

            }
        });

        $('#confirm-adjust').modal('toggle');

        

    });


    $('body').on('show.bs.modal','#confirm-adjust', function(e) {
        var data = $(e.relatedTarget).data();
        var check = isPositiveInteger($('#adjust_'+data.id).val());
        if(check){
            $('.btn-ok', this).show();
            $('.btn-ok', this).data('id', data.id);
            $('.btn-ok', this).data('nonce', data.nonce);
            $('.btn-ok', this).data('adjust', $('#adjust_'+data.id).val());
            $('.adjust-text', this).append("คุณต้องการเพิ่มราคาอีก<b><i>"+$('#adjust_'+data.id).val()+"</i></b> บาท<br>คุณต้องการดำเนินการต่อหรือไม่?");
        }else{
            $('.btn-ok', this).hide();
            $('.adjust-text', this).text('กรุณาใส่ราคาให้ถูกต้อง');
        }
        
        //console.log(data);
    });

    jQuery(document).on("click", ".assign-order", function(){
        var order_id = $(this).data('id');
        var nonce = $(this).data('nonce');
        var driver_id = $('#assign-employee option:selected').val();
        console.log("ส่งต่อ Order:"+order_id);
        var send_data = 'action=supervisor_assign_order&id='+order_id+'&nonce='+nonce+'&driver_id='+driver_id;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "ส่งต่อ Order เรียบร้อย: " + JSON.stringify(msg) );
                if(msg.success){
                    $( ".wrapper-loading" ).load( ajaxurl+"?action=load_driver_order_template", function( response, status, xhr ) {
                        if ( status == "error" ) {
                        var msg = "Sorry but there was an error: ";
                        $( ".wrapper-loading" ).html( msg + xhr.status + " " + xhr.statusText );
                        }
                    });
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });

    });
	// paring driver button
	$('#chooseHeadDriver').click(function(){
	console.log("ChooseHead Driver Activate!!");
		$( "#driver-content").load( ajaxurl+"?action=chooseHeadDriver&driver_id="+<?php echo get_current_user_id() ;?>, function( response, status, xhr ) 
		{
		console.log("Second AJAX START and Tamzang ID is "+<?php echo get_current_user_id() ;?> );
			if ( status == "error" ) {
				var msg = "Sorry but there was an error: ";
				$( "#driver-content" ).html( msg + xhr.status + " " + xhr.statusText );
			}
		
		});

	});

    jQuery(document).on("click", ".driver-step", function(){
        var order_id = $(this).data('id');
        var nonce = $(this).data('nonce');
        $( ".wrapper-loading" ).toggleClass('order-status-loading');
        console.log("driver step Order:"+order_id);
        var send_data = 'action=driver_next_step&id='+order_id+'&nonce='+nonce;
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: send_data,
            success: function(msg){
                console.log( "driver step Order เรียบร้อย: " + JSON.stringify(msg) );
                if(msg.success){
                    if(msg.data == "close"){
                        $( ".wrapper-loading" ).load( ajaxurl+"?action=load_driver_order_template", function( response, status, xhr ) {
                            if ( status == "error" ) {
                                var msg = "Sorry but there was an error: ";
                                $( ".wrapper-loading" ).html( msg + xhr.status + " " + xhr.statusText );
                            }
                        });
                    }
                    else
                        $('.driver-step').html(msg.data);
                }
                $( ".wrapper-loading" ).toggleClass('order-status-loading');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
                $( ".wrapper-loading" ).html( textStatus );
            }
        });

    });
	
});



</script>


<div class="modal fade" id="confirm-order" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">ยืนยันรับคำสั่งซื้อ</h4>
            </div>
            <div class="modal-body">
                <p>คุณกำลังจะยืนยันรับคำสั่งซื้อรหัส <b><i class="order-text"></i></b></p>
                <p>คุณต้องการดำเนินการต่อหรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-reject" data-dismiss="modal" style="float:left;">ปฏิเสธ</button>
                <button type="button" class="btn btn-success btn-ok">ตกลง</button>
            </div>
        </div>
    </div>
</div>


<div id="main_order">
    <div class="wrapper-loading">
        <?php get_template_part( 'driver/driver', 'order_template' ); ?>
    </div>
</div>
