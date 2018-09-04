jQuery(document).ready(function($){
  console.log("add cart loaded");
  //$('#submitbutton').on('click',function(){
  //  var $button = $(this);
    //var name = $("#field1").val();
  //  console.log("Submitted-"+$button.data( 'post_id' ));
  //});
  //$( "form" ).submit(function( event ) {
  //  event.preventDefault();
  //  console.log( "Handler for .submit() called." );
  //});
  $('form[name="product_form"]').on('submit', function (e) {
    $(this).submit();
  });

  $('form').on('submit', function (e) {
    var clikedForm = $(this);
    //var name = clikedForm.find("[name='field1']").val()
    console.log( "Handler for .submit() called.--"+$(clikedForm).serialize() );
    e.preventDefault();

    //var submit = clikedForm.find(':submit');
    var submit = $("input[type=submit]",this);
    //console.log(submit);
    //$("input[type=submit]",this).prop('value', '...');
    //submit.prop('value', 'รอสักครู่...').prop('disabled', true);

    var post_id = $("input[name=post_id]",this).val();
    var $modalDiv = $(this).closest('.modal');
    $modalDiv.addClass('loading');

    // var cart = parseInt($("#tamzang_cart_count").text());
    // //console.log("cart="+cart);
    // var qty = parseInt($('input[name="qty"]',this).val());
    // //console.log("qty="+qty);
    // var total = cart+qty;
    $.ajax({
      type: "POST",
      url: geodir_var.geodir_ajax_url,
      data: $(clikedForm).serialize(),
      success: function(msg){
            console.log( "Data Saved: " + JSON.stringify(msg) );
            $modalDiv.modal('hide').removeClass('loading');

            $('.wrapper-loading').toggleClass('cart-loading');
            //$( "#table-my-cart" ).load( ajaxurl+"?action=load_tamzang_cart" );
            $( "#table-my-cart" ).load( ajaxurl+"?action=load_tamzang_cart&post_id="+post_id, function( response, status, xhr ) {
              if ( status == "error" ) {
                var msg = "Sorry but there was an error: ";
                $( "#table-my-cart" ).html( msg + xhr.status + " " + xhr.statusText );
              }
              console.log( "status: " + status );
              $('.wrapper-loading').toggleClass('cart-loading');
            });
            //$("#tamzang_cart_count").html(total);
            //submit.prop('value', 'เพิ่มสินค้า').prop('disabled', false);

            //console.log(tamzang_ajax_settings.ajaxurl);
            // ถ้า msg = 0 แสดงว่าไม่ได้ login
      },
      error: function(XMLHttpRequest, textStatus, errorThrown) {
         console.log(textStatus);
      }
    });

  });


  // $(".btn-tamzang-quantity").on("click", function () {
  //
  //     var $button = $(this);
  //     var oldValue = $button.closest('.sp-quantity').find("input.quntity-input").val();
  //
  //     if ($button.data( 'type' ) == "plus") {
  //         var newVal = parseFloat(oldValue) + 1;
  //     } else {
  //         // Don't allow decrementing below zero
  //         if (oldValue > 1) {
  //             var newVal = parseFloat(oldValue) - 1;
  //         } else {
  //             newVal = 1;
  //         }
  //     }
  //
  //     $button.closest('.sp-quantity').find("input.quntity-input").val(newVal);
  //
  // });



});
