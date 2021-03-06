<?php


function my_theme_enqueue_styles() {

    $parent_style = 'whoop'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
    wp_enqueue_style( 'bootstrap',
    get_stylesheet_directory_uri() . '/bootstrap.min.css',
    array(  ),
    wp_get_theme()->get('Version')
    );
    wp_enqueue_style( 'bootstrap-theme',
        get_stylesheet_directory_uri() . '/bootstrap-theme.min.css',
        array( 'bootstrap' ),
        wp_get_theme()->get('Version')
    );
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_script( 'tamzang_bootstrapJS', get_stylesheet_directory_uri() . '/js/bootstrap.min.js' , array(), '1.0',  false );

    // wp_enqueue_style( 'child-style',
    //     get_stylesheet_directory_uri() . '/style.css',
    //     array( 'bootstrap-theme' ),
    //     wp_get_theme()->get('Version')
    // );
    if ( is_page_template( 'add_product.php' ) ) {

      // SCRIPT FOR UPLOAD
      wp_enqueue_script('plupload-all');
      wp_enqueue_script('jquery-ui-sortable');
      wp_register_script('geodirectory-plupload-script', get_stylesheet_directory_uri() . '/js/geodirectory-plupload.min.js#asyncload', array(), GEODIRECTORY_VERSION,true);
      wp_enqueue_script('geodirectory-plupload-script');
      wp_enqueue_script( 'tamzang_jquery_validate', get_stylesheet_directory_uri() . '/js/jquery.validate.min.js' , array(), '1.0',  false );
      wp_enqueue_script( 'tamzang_product_validation', get_stylesheet_directory_uri() . '/js/product_validation.js' , array(), '1.0',  false );
      // SCRIPT FOR UPLOAD END

      // check_ajax_referer function is used to make sure no files are uplaoded remotly but it will fail if used between https and non https so we do the check below of the urls
      if (str_replace("https", "http", admin_url('admin-ajax.php')) && !empty($_SERVER['HTTPS'])) {
          $ajax_url = admin_url('admin-ajax.php');
      } elseif (!str_replace("https", "http", admin_url('admin-ajax.php')) && empty($_SERVER['HTTPS'])) {
          $ajax_url = admin_url('admin-ajax.php');
      } elseif (str_replace("https", "http", admin_url('admin-ajax.php')) && empty($_SERVER['HTTPS'])) {
          $ajax_url = str_replace("https", "http", admin_url('admin-ajax.php'));
      } elseif (!str_replace("https", "http", admin_url('admin-ajax.php')) && !empty($_SERVER['HTTPS'])) {
          $ajax_url = str_replace("http", "https", admin_url('admin-ajax.php'));
      } else {
          $ajax_url = admin_url('admin-ajax.php');
      }

      // place js config array for plupload

      $plupload_init = array(
          'runtimes' => 'html5,silverlight,flash,browserplus,gears,html4',
          'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
          'container' => 'plupload-upload-ui', // will be adjusted per uploader
          'drop_element' => 'dropbox', // will be adjusted per uploader
          'file_data_name' => 'async-upload', // will be adjusted per uploader
          'multiple_queues' => true,
          'max_file_size' => geodir_max_upload_size(),
          'url' => $ajax_url,
          'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
          'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
          'filters' => array(array('title' => __('Allowed Files', 'geodirectory'), 'extensions' => '*')),
          'multipart' => true,
          'urlstream_upload' => true,
          'multi_selection' => false, // will be added per uploader
          // additional post data to send to our ajax hook
          'multipart_params' => array(
              '_ajax_nonce' => "", // will be added per uploader
              'action' => 'plupload_action', // the ajax action name
              'imgid' => 0 // will be added per uploader
          )
      );

      $base_plupload_config = json_encode($plupload_init);

      $gd_plupload_init = array('base_plupload_config' => $base_plupload_config,
          'upload_img_size' => geodir_max_upload_size());

      wp_localize_script('geodirectory-plupload-script', 'gd_plupload', $gd_plupload_init);

    }elseif (is_page_template( 'product_list.php' )) {
      wp_enqueue_script( 'tamzang_delete_product', get_stylesheet_directory_uri() . '/js/tamzang_delete_product.js' , array(), '1.0',  false );
    }

    if (is_single()) {
      wp_enqueue_script( 'tamzang_add_cart', get_stylesheet_directory_uri() . '/js/tamzang_add_cart.js' , array(), '1.0',  false );
      
      // set variables for script
      wp_localize_script( 'tamzang_add_cart', 'tamzang_ajax_settings', array(
          'ajaxurl' => admin_url( 'admin-ajax.php' )
      ) );
    }
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

function remove_details_next_prev(){
    remove_action('geodir_details_main_content', 'geodir_action_details_next_prev', 80);
}
add_action('geodir_details_main_content', 'remove_details_next_prev', 1);

add_action('wp_enqueue_scripts','scripts_transfer_slip_picture');
function scripts_transfer_slip_picture(){
    if ( is_page_template('my_order.php') || is_page_template('shop_order.php') ) {
        wp_enqueue_script( 'uploader-script', get_stylesheet_directory_uri() . '/js/uploader/jquery.dm-uploader.min.js' , array(), '1.0',  false );
    }
}


add_action( 'wpcf7_init', 'custom_add_form_tag_buttonLatLong' );

function custom_add_form_tag_buttonLatLong() {
    wpcf7_add_form_tag( 'buttonlatlong', 'custom_buttonLatLong_tag_handler' );
}

function custom_buttonLatLong_tag_handler( $tag ) {
  $scriptSrc = get_stylesheet_directory_uri() . '/js/getLaLong.js';
  wp_enqueue_script( 'myhandle', $scriptSrc , array(), '1.0',  false );
  return '<div style="width: 130px;color:white;"><button id="myLatLong">แนบที่อยู่</button><div id="geoStatus" style="float: right;"></div></div>';
}

function get_all_regions($atts){
  //set default attributes and values
  $values = shortcode_atts( array(
      'records'   	=> '10',
  ), $atts );
  $records = intval($values['records']);
  $region_args = array(
    'what' => 'region',
    'city_val' => '',
    'region_val' => '',
    'country_val' => '',
    'compare_operator' =>'in',
    'country_column_name' => 'country',
    'region_column_name' => 'region',
    'city_column_name' => 'city',
    'location_link_part' => true,
    'order_by' => ' asc ',
    'no_of_records' => $no_of_records,
    'format' => array('type' => 'array')
  );
  $region_loc_array = geodir_get_location_array($region_args);
  $i = 0;
  ?>
  <ul class="locations_list">
  <?php
  foreach($region_loc_array as $region_item) {
    if($i % $records == 0) echo '</ul><ul class="locations_list">';
    ?>
    <li class="region">
      <a href="<?php echo home_url('/places/').$region_item->location_link;?>"><?php echo __( $region_item->region, 'geodirectory' ) ;?></a>
    </li>
    <?php
    $i += 1;
  }
  ?>
  </ul>
  <?php
}

add_shortcode('all_regions', 'get_all_regions');


function tamzang_add_remove_images( $newArr, $product_id ) {
	global $wpdb, $current_user;

	$temp_folder_name = 'temp_' . $current_user->data->ID;

	if ( $current_user->data->ID == '' ) {
		$temp_folder_name = 'temp_' . session_id();
	}

	$wp_upload_dir = wp_upload_dir();
	$temp_folder = $wp_upload_dir['path'] . '/' . $temp_folder_name;


	$images = array();
	foreach( $newArr as $img ) {
		$file_ext = pathinfo( $img, PATHINFO_EXTENSION );
		$file_name = basename( $img, "." . $file_ext );
		$filename =  $temp_folder . '/' . basename( $img );
		$new_file_name =  $wp_upload_dir['path'] . '/' . $file_name . '_' . time() . '.' . $file_ext;
		copy( $filename, $new_file_name );
		$images[] = $wp_upload_dir['url'] . '/' . $file_name . '_' . time() . '.' . $file_ext;
    $query = $wpdb->prepare("INSERT INTO product_images SET
                             product_id = %d,title = %s,file =%s,file =%s,image_order = '0'",
                             array($post->ID,$user_ID,$commment_image_adj)
                          );
    $wpdb->query($query);
	}

	geodir_delete_directory( $temp_folder );


	return $images;
}


/*
* Ref: function geodir_save_post_images
* File: plugins/geodirectory/geodirectory-fuctions/post_functions.php
* @since 1.5.7
*/
function tamzang_save_images($product_id = 0, $post_image = array(), $dummy = false)
{
    global $wpdb, $current_user;
    //$post_type = get_post_type($post_id);
    //$table = $plugin_prefix . $post_type . '_detail';

    //$post_images = geodir_get_images($post_id);
    $post_images = tamzang_get_product_images($product_id);

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE products SET featured_image = '' where id =%d",
            array($product_id)
        )
    );

    $invalid_files = $post_images;
    $valid_file_ids = array();
    $valid_files_condition = '';
    $geodir_uploaddir = '';

    $remove_files = array();

    if (!empty($post_image)) {

        $uploads = wp_upload_dir();
        $uploads_dir = $uploads['path'];
        $geodir_uploadpath = $uploads['path'];
        $geodir_uploadurl = $uploads['url'];
        $sub_dir = isset($uploads['subdir']) ? $uploads['subdir'] : '';
        $invalid_files = array();
        $postcurr_images = array();

        for ($m = 0; $m < count($post_image); $m++) {

            $menu_order = $m + 1;
            $file_path = '';

            /* --------- start ------- */

            $split_img_path = explode(str_replace(array('http://','https://'),'',$uploads['baseurl']), str_replace(array('http://','https://'),'',$post_image[$m]));

            $split_img_file_path = isset($split_img_path[1]) ? $split_img_path[1] : '';

            if (!$find_image = $wpdb->get_var($wpdb->prepare("SELECT ID FROM product_images WHERE file=%s AND product_id = %d", array($split_img_file_path, $product_id)))) {

                /* --------- end ------- */

                $curr_img_url = $post_image[$m];
                $image_name_arr = explode('/', $curr_img_url);
                $count_image_name_arr = count($image_name_arr) - 2;
                $count_image_name_arr = ($count_image_name_arr >= 0) ? $count_image_name_arr : 0;
                $curr_img_dir = $image_name_arr[$count_image_name_arr];
                $filename = end($image_name_arr);

                if (strpos($filename, '?') !== false) {
                    list($filename) = explode('?', $filename);
                }

                $curr_img_dir = str_replace($uploads['baseurl'], "", $curr_img_url);
                $curr_img_dir = str_replace($filename, "", $curr_img_dir);
                $img_name_arr = explode('.', $filename);
                $file_title = isset($img_name_arr[0]) ? $img_name_arr[0] : $filename;

                if (!empty($img_name_arr) && count($img_name_arr) > 2) {
                    $new_img_name_arr = $img_name_arr;
                    if (isset($new_img_name_arr[count($img_name_arr) - 1])) {
                        unset($new_img_name_arr[count($img_name_arr) - 1]);
                        $file_title = implode('.', $new_img_name_arr);
                    }
                }

                $file_title = sanitize_file_name($file_title);
                $file_name = sanitize_file_name($filename);
                $arr_file_type = wp_check_filetype($filename);
                $uploaded_file_type = $arr_file_type['type'];

                // Set an array containing a list of acceptable formats

                $allowed_file_types = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png');

                // If the uploaded file is the right format

                if (in_array($uploaded_file_type, $allowed_file_types)) {
                    if (!function_exists('wp_handle_upload')) {
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                    }

                    if (!is_dir($geodir_uploadpath)) {
                        mkdir($geodir_uploadpath);
                    }

                    $external_img = false;

                    if (strpos( str_replace( array('http://','https://'),'',$curr_img_url ), str_replace(array('http://','https://'),'',$uploads['baseurl'] ) ) !== false) {
                    } else {
                        $external_img = true;
                    }

                    if ($dummy || $external_img) {
                        $uploaded_file = array();
                        $uploaded = (array)fetch_remote_file($curr_img_url);
                        if (isset($uploaded['error']) && empty($uploaded['error'])) {
                            $new_name = basename($uploaded['file']);
                            $uploaded_file = $uploaded;
                        }else{
                            print_r($uploaded);exit;
                        }
                        $external_img = false;
                    } else {
                        $new_name = $product_id . '_' . $file_name;

                        if ($curr_img_dir == $sub_dir) {
                            $img_path = $geodir_uploadpath . '/' . $filename;
                            $img_url = $geodir_uploadurl . '/' . $filename;
                        } else {
                            $img_path = $uploads_dir . '/temp_' . $current_user->data->ID . '/' . $filename;
                            $img_url = $uploads['url'] . '/temp_' . $current_user->data->ID . '/' . $filename;
                        }

                        $uploaded_file = '';

                        if (file_exists($img_path)) {
                            $uploaded_file = copy($img_path, $geodir_uploadpath . '/' . $new_name);
                            $file_path = '';
                        } else if (file_exists($uploads['basedir'] . $curr_img_dir . $filename)) {
                            $uploaded_file = true;
                            $file_path = $curr_img_dir . '/' . $filename;
                        }

                        if ($curr_img_dir != $geodir_uploaddir && file_exists($img_path))
                            unlink($img_path);
                    }


                    if (!empty($uploaded_file)) {
                        if (!isset($file_path) || !$file_path) {
                            $file_path = $sub_dir . '/' . $new_name;
                        }

                        $postcurr_images[] = str_replace(array('http://','https://'),'',$uploads['baseurl'] . $file_path);

                        if ($menu_order == 1) {
                            $wpdb->query($wpdb->prepare("UPDATE products SET featured_image = %s where id =%d", array($file_path, $product_id)));
                        }

                        // Set up options array to add this file as an attachment
                        $attachment = array();
                        $attachment['product_id'] = $product_id;
                        $attachment['title'] = $file_title;
                        //$attachment['content'] = '';
                        $attachment['file'] = $file_path;
                        //$attachment['mime_type'] = $uploaded_file_type;
                        $attachment['menu_order'] = $menu_order;
                        //$attachment['is_featured'] = 0;

                        $attachment_set = '';

                        foreach ($attachment as $key => $val) {
                            if ($val != '')
                                $attachment_set .= $key . " = '" . $val . "', ";
                        }
                        $attachment_set = trim($attachment_set, ", ");
                        $wpdb->query("INSERT INTO product_images SET " . $attachment_set);
                        $valid_file_ids[] = $wpdb->insert_id;
                    }
                }

            } else {

                $valid_file_ids[] = $find_image;
                $postcurr_images[] = str_replace(array('http://','https://'),'',$post_image[$m]);
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE product_images SET menu_order = %d where file =%s AND product_id =%d",
                        array($menu_order, $split_img_path[1], $product_id)
                    )
                );

                if ($menu_order == 1)
                    $wpdb->query($wpdb->prepare("UPDATE products SET featured_image = %s where id =%d", array($split_img_path[1], $product_id)));
            }

        }

        if (!empty($valid_file_ids)) {
            $remove_files = $valid_file_ids;
            $remove_files_length = count($remove_files);
            $remove_files_format = array_fill(0, $remove_files_length, '%d');
            $format = implode(',', $remove_files_format);
            $valid_files_condition = " ID NOT IN ($format) AND ";
        }

        //Get and remove all old images of post from database to set by new order

        if (!empty($post_images)) {
            foreach ($post_images as $img) {
                if (!in_array(str_replace(array('http://','https://'),'',$img->src), $postcurr_images)) {
                    $invalid_files[] = (object)array('src' => $img->src);
                }
            }
        }
        $invalid_files = (object)$invalid_files;
    }

    $remove_files[] = $product_id;
    $wpdb->query($wpdb->prepare("DELETE FROM product_images WHERE " . $valid_files_condition . " product_id = %d", $remove_files));
    if (!empty($invalid_files))
        geodir_remove_attachments($invalid_files);

    geodir_remove_temp_images();
    //geodir_set_wp_featured_image();
}





/*
* Ref: function geodir_get_images
* File: plugins/geodirectory/geodirectory-fuctions/post_functions.php
* @since 1.5.7
*/
function tamzang_get_product_images($product_id = 0, $limit = '')
{
    global $wpdb;
    if ($limit) {
        $limit_q = " LIMIT $limit ";
    } else {
        $limit_q = '';
    }

    $not_featured = '';
    $sub_dir = '';

    $arrImages = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM product_images WHERE product_id = %d ORDER BY menu_order ASC, id DESC $limit_q ",
            array($product_id)
        )

    );

    $counter = 0;
    $return_arr = array();

    if (!empty($arrImages)) {
        foreach ($arrImages as $attechment) {
            $img_arr = array();
            $img_arr['id'] = $attechment->id;
            $img_arr['product_id'] = isset($attechment->product_id) ? $attechment->product_id : 0;
            //$img_arr['user_id'] = isset($attechment->user_id) ? $attechment->user_id : 0;

            $file_info = pathinfo($attechment->file);

            if ($file_info['dirname'] != '.' && $file_info['dirname'] != '..')
                $sub_dir = stripslashes_deep($file_info['dirname']);

            $uploads = wp_upload_dir(trim($sub_dir, '/')); // Array of key => value pairs
            $uploads_baseurl = $uploads['baseurl'];
            $uploads_path = $uploads['path'];

            $file_name = $file_info['basename'];
            $uploads_url = $uploads_baseurl . $sub_dir;

            $img_arr['src'] = apply_filters('geodir_get_images_src',$uploads_url . '/' . $file_name,$file_name,$uploads_url,$uploads_baseurl);
            $img_arr['path'] = $uploads_path . '/' . $file_name;
            $width = 0;
            $height = 0;

            if (is_file($img_arr['path']) && file_exists($img_arr['path'])) {
                $imagesize = getimagesize($img_arr['path']);
                $width = !empty($imagesize) && isset($imagesize[0]) ? $imagesize[0] : '';
                $height = !empty($imagesize) && isset($imagesize[1]) ? $imagesize[1] : '';
            }

            $img_arr['width'] = $width;
            $img_arr['height'] = $height;

            $img_arr['file'] = $file_name; // add the title to the array
            $img_arr['title'] = $attechment->title; // add the title to the array
            //$img_arr['caption'] = isset($attechment->caption) ? $attechment->caption : ''; // add the caption to the array
            //$img_arr['content'] = $attechment->content; // add the description to the array
            //$img_arr['is_approved'] = isset($attechment->is_approved) ? $attechment->is_approved : ''; // used for user image moderation. For backward compatibility Default value is 1.

            $return_arr[] = (object)$img_arr;

            $counter++;

        }
        return apply_filters('geodir_get_images_arr',$return_arr);
    }
    return $return_arr;
}

function tamzang_ecommerce_view ($post_id){
  set_query_var( 'post_id', $post_id );
  set_query_var( 'cat_id', 0 );
  get_template_part( 'ecommerce-view' );
}

//to get all sub cids
function sub_cids($cid,$cids=0){
    global $cids, $wpdb;
    
    $categories = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM category WHERE parent = %d", array($cid)
        )
    );
    foreach ( $categories as $category ){
        $cids[]=$category->cid;
        sub_cids($category->cid,$cids);
    }
    
    return $cids;
    
}

function tamzang_get_all_products($post_id, $cat_id){
  global $wpdb;

  $cids=sub_cids($cat_id);
  $cids[]=$cat_id;
  $cids_str=implode(",",$cids);

  $arrProducts = $wpdb->get_results(
      $wpdb->prepare(
          "SELECT * FROM products where post_id = %d AND category_id in ($cids_str)", array($post_id)
      )
  );
  return $arrProducts;
}

function create_product_modal($product, $post_id){
  //global $post;
//   $post_id = $post->ID;
//   $arrProducts = tamzang_get_all_products($post_id);
  $nonce = wp_create_nonce( 'add_to_cart_' . $product->ID );
//   if (!empty($arrProducts)) {
//     foreach ( $arrProducts as $product ){
      $html = '';
      $html .= '<div class="modal fade" id="product_'.$product->ID.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">';
      $html .= '<div class="modal-dialog" role="document">';
      $html .= '<div class="modal-content">';
      $html .= '<form method="POST" id="add_cart_' . $product->ID . '" name="modal_add_cart">';
      $html .= '<div class="modal-header">';
      $html .= '<div class="order-col-9"><h3 class="modal-title" id="exampleModalLabel">'.$product->post_title.'</h3></div>';
      $html .= '<div class="order-col-3"><button type="button" class="close" data-dismiss="modal" aria-label="Close">';
      $html .= '<span aria-hidden="true">&times;</span>';
      $html .= '</button></div>';
      $html .= '</div>';
      $html .= '<div class="modal-body">';
      //$html .= json_encode(tamzang_get_product_images($product->id));
      $html .= create_product_carousel($product, geodir_get_images($product->ID, 'medium', get_option('geodir_listing_no_img')));
      // $html .= '<div class="sp-quantity">';
      // $html .= '<div class="input-group">';
      // $html .= '<span class="input-group-btn">';
      // $html .= '<button type="button" class="btn-tamzang-quantity quantity-left-minus btn btn-danger btn-number"  data-type="minus">';
      // $html .= '<span class="glyphicon glyphicon-minus"></span>';
      // $html .= '</button>';
      // $html .= '</span>';
      // $html .= '<div class="sp-input">';
      $html .= '<input type="hidden" class="quntity-input form-control" name="qty" value="1">';
      // $html .= '</div>';
      // $html .= '<span class="input-group-btn">';
      // $html .= '<button type="button" class="btn-tamzang-quantity btn-quantity quantity-right-plus btn btn-success btn-number" data-type="plus">';
      // $html .= '<span class="glyphicon glyphicon-plus"></span>';
      // $html .= '</button>';
      // $html .= '</span>';
      // $html .= '</div>';
      // $html .= '</div>';

      $html .= '<input type="hidden" name="post_id" value="'.$post_id.'"  />';
      $html .= '<input type="hidden" name="product_id" value="'.$product->ID.'"  />';
      $html .= '<input type="hidden" name="nonce" value="'.$nonce.'"  />';
      $html .= '<input type="hidden" name="action" value="add_to_cart"  />';
      $html .= '</div>';
      $html .= '<div class="modal-footer">';
      $html .= '<div class="order-col-6" style="text-align: left;">';
      $html .= '<h3>ราคา: '.str_replace(".00", "",number_format($product->geodir_price,2)).' บาท</h3>';
      $html .= '</div>';
      $html .= '<div class="order-col-6">';
      $html .= '<input type="submit" value="เพิ่มสินค้า" class="btn btn-primary"></input>';
      $html .= '</div>';
      $html .= '</div>';
      $html .= '</form>';
      $html .= '</div>';
      $html .= '</div>';
      $html .= '</div>';
      echo $html;
//     }
//   }

}

function create_product_carousel($product, $arr_images = array()){
  $html = '';
  //$total_image = count((array)$arr_images);

  $indicators = '';
  $slides = '';
  $is_first = true;
  $x = 0;
  foreach ($arr_images as $image){
    $indicators .= '<li data-target="#ProductCarousel_'.$product->ID.'" data-slide-to="'.$x.'" '.($is_first ? 'class="active"' : '').' ></li>';

    $slides .= '<div class="item '.($is_first ? 'active' : '').'">';
    $slides .= '<img src="'.$image->src.'" >';
    $slides .= '</div>';
    $x++;
    $is_first = false;
  }

  //$html .= '<p align="left" style = "font-size:18px">'.$product->long_desc.'</p>';
  $html .= '<div id="ProductCarousel_'.$product->ID.'" class="carousel slide" data-ride="carousel">';
  $html .= '<ol class="carousel-indicators">';
  $html .= $indicators;
  $html .= '</ol>';
  $html .= '<div class="carousel-inner">';
  $html .= $slides;
  $html .= '</div>';
  $html .= '<a class="left carousel-control" href="#ProductCarousel_'.$product->ID.'" data-slide="prev">';
  $html .= '<span class="glyphicon glyphicon-chevron-left"></span>';
  $html .= '<span class="sr-only">Previous</span>';
  $html .= '</a>';
  $html .= '<a class="right carousel-control" href="#ProductCarousel_'.$product->ID.'" data-slide="next">';
  $html .= '<span class="glyphicon glyphicon-chevron-right"></span>';
  $html .= '<span class="sr-only">Next</span>';
  $html .= '</a>';
  $html .= '';
  $html .= '</div>';



  return $html;
}

//add_action('geodir_after_single_post','create_product_modal');

//Ajax functions
add_action('wp_ajax_add_to_cart', 'add_to_cart_callback');

function add_to_cart_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'add_to_cart_' . $data['product_id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {
    $geodir_tamzang_id = geodir_get_post_meta( $data['post_id'], 'geodir_tamzang_id', true );
    $show_button = geodir_get_post_meta( $data['product_id'], 'geodir_show_addcart', true );
    // $show_button = $wpdb->get_var(
    //     $wpdb->prepare(
    //         "SELECT show_button FROM products where id = %d ", array($data['product_id'])
    //     )
    // );
    if(!empty($geodir_tamzang_id)&&$show_button){

        $product = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT product_id,qty FROM shopping_cart where product_id = %d AND wp_user_id = %d ", array($data['product_id'], $current_user->ID)
            )
        );
    
        if($wpdb->num_rows > 0)
        {
          $wpdb->query(
              $wpdb->prepare(
                  "UPDATE shopping_cart SET qty = %d where product_id = %d AND wp_user_id =%d",
                  array((int)$product->qty + (int)$data['qty'], $product->product_id, $current_user->ID)
              )
          );
        }else{
          $cart = array();
          $cart['wp_user_id'] = $current_user->ID;
          $cart['product_id'] = $data['product_id'];
          $cart['qty'] = $data['qty'];
    
          $cart_set = '';
    
          foreach ($cart as $key => $val) {
              if ($val != '')
                  $cart_set .= $key . " = '" . $val . "', ";
          }
          $cart_set = trim($cart_set, ", ");
          $wpdb->query("INSERT INTO shopping_cart SET " . $cart_set);
        }

        wp_send_json_success($data);

    }else{
        wp_send_json_error();
    }


  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }
  //$valid_file_ids[] = $wpdb->insert_id;

}


//Ajax functions
add_action('wp_ajax_delete_product', 'delete_product_callback');

function delete_product_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'delete_product_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $product_id = $data['id'];

  try {

    // $images = tamzang_get_product_images($product_id);

    // // check wp_user_id ด้วยว่าตรงไหม

    // if (!empty($images))
    //     geodir_remove_attachments($images);

    // $wpdb->query($wpdb->prepare("DELETE FROM product_images WHERE product_id = %d", $product_id));

    // $wpdb->query($wpdb->prepare("DELETE FROM products WHERE id = %d", $product_id));

    // $wpdb->query($wpdb->prepare("DELETE FROM shopping_cart WHERE product_id = %d", $product_id));


    wp_delete_post($product_id);


    wp_send_json_success($data);

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }


  //return $data;
}

function tamzang_cart_count()
{
  global $wpdb, $current_user;

  $cart_item = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT sum(qty) FROM shopping_cart where wp_user_id = %d", array($current_user->ID)
      )
  );

  if($cart_item == NULL)
    $cart_item = "0";

  return $cart_item;
}

function tamzang_get_all_products_in_cart($user_id){
  global $wpdb;
//   $arrProducts = $wpdb->get_results(
//       $wpdb->prepare(
//           "SELECT p.id as product_id,p.post_id,p.name,p.short_desc,p.featured_image,p.price,s.qty,p.stock
//           FROM products p INNER JOIN shopping_cart s
//           on p.id = s.product_id AND s.wp_user_id = %d AND p.post_id = %d ORDER BY s.id ", array($user_id, $post_id)
//       )
//   );
  add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_shop_id', 10, 2);
  add_filter('geodir_filter_widget_listings_join', 'inner_join_user_id', 10, 2);
  add_filter('geodir_filter_widget_listings_fields', 'select_shopping_cart_field', 10, 3);
  $query_args = array(
    'is_geodir_loop' => true,
    'post_type' => 'gd_product',
    'posts_per_page' => -1,
    'order_by' => 'post_title'
  );
  
  $arrProducts = geodir_get_widget_listings($query_args);


  return $arrProducts;
}


//Ajax functions
add_action('wp_ajax_update_product_cart', 'update_product_cart_callback');

function update_product_cart_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'update_product_cart_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $product_id = $data['id'];
  $qty = $data['qty'];

  try {

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE shopping_cart SET qty = %d where product_id = %d AND wp_user_id =%d",
            array($qty, $product_id, $current_user->ID)
        )
    );

    $total = geodir_get_post_meta($data['id'], 'geodir_price', true)*$qty;
    wp_send_json_success($total);

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }


  //return $data;
}


//Ajax functions
add_action('wp_ajax_delete_product_cart', 'delete_product_cart_callback');

function delete_product_cart_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'delete_product_cart_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $product_id = $data['id'];

  try {

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM shopping_cart WHERE product_id = %d AND wp_user_id =%d",
            array($product_id, $current_user->ID)
        )
    );

    //$total = tamzang_cart_count();
    wp_send_json_success($total);

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }


  //return $data;
}


//Ajax functions
add_action('wp_ajax_load_tamzang_cart', 'load_tamzang_cart_callback');

function load_tamzang_cart_callback(){
  $data = $_GET;
  set_query_var( 'post_id', $data['post_id'] );
  get_template_part( 'ajax-cart' );
  wp_die();
}


//Ajax functions
add_action('wp_ajax_update_order_status', 'update_order_status_callback');

function update_order_status_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;
  $current_date = date("Y-m-d H:i:s");
  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  //check the nonce
  if ( check_ajax_referer( 'update_order_status_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $order_id = $data['id'];
  $status = $data['status'];
  //get_post_field( 'post_author', $order->post_id )
  if($status == '99'){
    $order_status = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT status FROM orders where id = %d", array($order_id)
        )
    );

    if($order_status != 1)
        wp_send_json_error($order_status);
  }
  try {

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE orders SET status = %d where id = %d ",
            array($status, $order_id)
        )
    );

    $order = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT thread_id,wp_user_id,post_id FROM orders where id = %d ", array($order_id)
        )
    );

    //$shop_owner_id = get_post_field ('post_author', $order->post_id);
    $url = "";
    $user_nicename = "";
    if($status == "1" || $status == "2" || $status == "3") // ร้านค้าส่งข้อความไปหาลูกค้า
    {
        // $customer_info = get_userdata($order->wp_user_id);
        // $user_nicename = $customer_info->user_nicename;
        $url = home_url('/my-order/');
        
    }else // ลูกค้าส่งข้อความหาร้านค้า
    {
        // $author_id = get_post_field ('post_author', $order->post_id);
        // $user_nicename = get_the_author_meta( 'user_nicename' , $author_id ); 
        $url = home_url('/shop-order/').'?pid='.$order->post_id;
    }
    //$url = home_url('/members/'.$user_nicename.'/messages/view/'.$order->thread_id);

    $reply_message = "";
    switch ($status) {
        case "1":
            $reply_message = '<strong><p style="font-size:14px;">รอการจ่ายเงิน</p> <a href="'.$url.'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
            break;
        case "2":
            $reply_message = '<strong><p style="font-size:14px;">ยืนยันการจ่ายเงิน</p> <a href="'.$url.'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
            break;
        case "3":
            $reply_message = '<strong><p style="font-size:14px;">ทำการจัดส่งแล้ว</p> <a href="'.$url.'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
            break;
        case "4":
            $reply_message = '<strong><p style="font-size:14px;">ใบสั่งซื้อเลขที่: #'.$order_id.' ลูกค้าได้รับสินค้าแล้ว</p>';
            break;
        case "99":
            $reply_message = '<strong><p style="font-size:14px;">ลูกค้าได้ทำการยกเลิกการสั่งซื้อ</p> <a href="'.$url.'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
            break;
        default:
            $reply_message = "";
    }

    $wpdb->query(
        $wpdb->prepare(
          "INSERT INTO wp_bp_messages_messages SET thread_id = %d, sender_id = %d, subject = %s, message = %s, date_sent = %s ",
          array($order->thread_id, $current_user->ID, "ใบสั่งซื้อเลขที่: #".$order_id , $reply_message, $current_date)
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE wp_bp_messages_recipients SET unread_count = %d, sender_only = %d where thread_id = %d AND user_id != %d ",
            array(1, 0, $order->thread_id, $current_user->ID)
        )
    );

    wp_send_json_success($data);

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

  //wp_send_json_success($data);
  //return $data;
}

//Ajax functions
add_action('wp_ajax_user_received_product', 'user_received_product_callback');

function user_received_product_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_add_to_cart_.log', var_export( $data, true));

  //check the nonce
  if ( check_ajax_referer( 'user_received_product_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $order_id = $data['id'];
  $order_owner = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT wp_user_id FROM orders where id = %d", array($order_id)
      )
  );

  if($current_user->ID == $order_owner)
  {
    try {

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE orders SET status = %d where id = %d ",
              array(4, $order_id)
          )
      );

      wp_send_json_success($data);

    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
  }else{
    wp_send_json_error();
  }


  //wp_send_json_success($data);
  //return $data;
}

//Ajax functions
add_action('wp_ajax_load_order_status', 'load_order_status_callback');

function load_order_status_callback(){
  $data = $_GET;
  set_query_var( 'order_status', $data['order_status'] );
  get_template_part( 'ajax-order-status' );
  wp_die();
}


//Ajax functions
add_action('wp_ajax_add_transfer_slip_picture', 'add_transfer_slip_picture_callback');

function add_transfer_slip_picture_callback(){
  global $wpdb, $current_user;
  $data = $_POST;

  //check the nonce
  if ( check_ajax_referer( 'add_transfer_slip_picture_' . $data['order_id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }


  $owner = $wpdb->get_row(
      $wpdb->prepare(
          "SELECT wp_user_id,thread_id,post_id FROM orders where id = %d AND status != 99 ", array($data['order_id'])
      )
  );

  if($current_user->ID == $owner->wp_user_id)
  {
    $thread_id = $owner->thread_id;
    $current_date = date("Y-m-d H:i:s");
    //$target_dir = '/home/tamzang/domains/tamzang.com/public_html/Test02/wp-content/themes/GeoDirectory_whoop-child/images/upload/';
    $uploads = wp_upload_dir();
    $uploads_dir = $uploads['path'].'/slip/'; //C:/path/to/wordpress/wp-content/uploads/2010/05/slip
    if (!file_exists($uploads_dir))
    {
      mkdir($uploads_dir);
    }


    $old_file_name = basename($_FILES["file"]["name"]);
    $imageFileType = strtolower(pathinfo($old_file_name,PATHINFO_EXTENSION));
    $target_file = $uploads_dir . $data['order_id'] .'.'. $imageFileType;
    $image = $uploads['subdir'].'/slip/'.$data['order_id'] .'.'. $imageFileType;
    move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE orders SET image_slip = %s where id = %d ",
            array($image, $data['order_id'])
        )
    );

    $return = array(
        'image' => $uploads['url'].'/slip/'.$data['order_id'] .'.'. $imageFileType,
        'order_id'      => $data['order_id']
    );

    $reply_message = '<strong><p style="font-size:14px;">ลูกค้าได้ส่งหลักฐานการโอนเงินแล้ว</p> <a href="'.home_url('/shop-order/').'?pid='.$owner->post_id.'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
    $wpdb->query(
        $wpdb->prepare(
          "INSERT INTO wp_bp_messages_messages SET thread_id = %d, sender_id = %d, subject = %s, message = %s, date_sent = %s ",
          array($thread_id, $current_user->ID, "ใบสั่งซื้อเลขที่: #".$data['order_id'] , $reply_message, $current_date)
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE wp_bp_messages_recipients SET unread_count = %d where thread_id = %d AND user_id != %d ",
            array(1, $thread_id, $current_user->ID)
        )
    );

    wp_send_json_success($return);
  }else{
    wp_send_json_error();
  }
  //wp_send_json_success();
}

add_action('wp_ajax_add_tracking_image', 'add_tracking_image_callback');

function add_tracking_image_callback(){
  global $wpdb, $current_user;
  $data = $_POST;

  //check the nonce
  if ( check_ajax_referer( 'add_tracking_image_' . $data['order_id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }


  $order = $wpdb->get_row(
      $wpdb->prepare(
          "SELECT post_id,thread_id,wp_user_id FROM orders where id = %d AND status != 99 ", array($data['order_id'])
      )
  );

  if(geodir_listing_belong_to_current_user((int)$order->post_id))
  {
    $current_date = date("Y-m-d H:i:s");
    $thread_id = $order->thread_id;
    //$target_dir = '/home/tamzang/domains/tamzang.com/public_html/Test02/wp-content/themes/GeoDirectory_whoop-child/images/upload/';
    $uploads = wp_upload_dir();
    $uploads_dir = $uploads['path'].'/tracking/'; //C:/path/to/wordpress/wp-content/uploads/2010/05/tracking
    if (!file_exists($uploads_dir))
    {
      mkdir($uploads_dir);
    }


    $old_file_name = basename($_FILES["file"]["name"]);
    $imageFileType = strtolower(pathinfo($old_file_name,PATHINFO_EXTENSION));
    $target_file = $uploads_dir . $data['order_id'] .'.'. $imageFileType;
    $image = $uploads['subdir'].'/tracking/'.$data['order_id'] .'.'. $imageFileType;
    move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE orders SET tracking_image = %s where id = %d ",
            array($image, $data['order_id'])
        )
    );

    $return = array(
        'image' => $uploads['url'].'/tracking/'.$data['order_id'] .'.'. $imageFileType,
        'order_id'      => $data['order_id']
    );

    $reply_message = '<strong><p style="font-size:14px;">ร้านได้ส่งหลักฐานการจัดส่งแล้ว</p> <a href="'.home_url('/my-order/').'">คลิกที่นี่เพื่อดูใบสั่งซื้อ</a></strong>';
    $wpdb->query(
        $wpdb->prepare(
          "INSERT INTO wp_bp_messages_messages SET thread_id = %d, sender_id = %d, subject = %s, message = %s, date_sent = %s ",
          array($thread_id, $current_user->ID, "ใบสั่งซื้อเลขที่: #".$data['order_id'] , $reply_message, $current_date)
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "UPDATE wp_bp_messages_recipients SET unread_count = %d where thread_id = %d AND user_id != %d ",
            array(1, $thread_id, $current_user->ID)
        )
    );

    wp_send_json_success($return);
  }else{
    wp_send_json_error();
  }
  //wp_send_json_success();
}


//Ajax functions
add_action('wp_ajax_load_address_form', 'load_address_form_callback');

function load_address_form_callback(){
  global $wpdb, $current_user;
  $data = $_GET;

  $address_id = $data['address_id'];
  if (isset($address_id) && $address_id != ''){

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT wp_user_id FROM user_address where id = %d ", array($address_id)
        )
    );

    if($current_user->ID == $owner)
      set_query_var( 'address_id', $data['address_id'] );

  }

  get_template_part( 'address/myaddress', 'form' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_load_address_list', 'load_address_list_callback');

function load_address_list_callback(){
  //$data = $_GET;
  //set_query_var( 'address_id', $data['address_id'] );
  get_template_part( 'address/myaddress', 'list' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_get_province', 'get_province_callback');

function get_province_callback(){
  global $wpdb;

  $arrProvince = $wpdb->get_results("SELECT DISTINCT region FROM ".POST_LOCATION_TABLE." ORDER BY region ");

  $return_arr = array();
  if (!empty($arrProvince)) {
    foreach ( $arrProvince as $province ){
      $arr_province = array();
      $arr_province['province'] = $province->region;

      $return_arr[] = (object)$arr_province;
    }
  }

  wp_send_json_success($return_arr);

  // $response= array(
  //       'message'   => 'Saved',
  //       'ID'        => POST_LOCATION_TABLE
  //   );
  //   wp_send_json_success($response);

}

add_action('wp_ajax_get_district', 'get_district_callback');

function get_district_callback(){
  global $wpdb;
  $data = $_GET;

  $arrDistrict = $wpdb->get_results(
      $wpdb->prepare(
          "SELECT DISTINCT city FROM ".POST_LOCATION_TABLE." WHERE region=%s ORDER BY city ", array($data['region'])
      )
  );

  $return_arr = array();
  if (!empty($arrDistrict)) {
    foreach ( $arrDistrict as $district ){
      $arr_district = array();
      $arr_district['district'] = $district->city;

      $return_arr[] = (object)$arr_district;
    }
  }

  wp_send_json_success($return_arr);

}

add_action('wp_ajax_add_user_address', 'add_user_address_callback');

function add_user_address_callback(){
  global $wpdb, $current_user;
  $data = $_POST;

  if ( check_ajax_referer( 'add_user_address_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $user_address = array();

    $user_address['name'] = $data['name'];
    $user_address['address'] = $data['address'];
    $user_address['district'] = $data['dd_district'];
    $user_address['province'] = $data['dd_province'];
    $user_address['postcode'] = $data['tb_postcode'];
    $user_address['phone'] = $data['phone'];
	//Bank 20190206 add new 2 field
	$user_address['latitude'] = $data['lat'];
	$user_address['longitude'] = $data['lng'];
	


    $address_id = $data['address_id'];
    $sql = '';
    $where = '';
    if (isset($address_id) && $address_id != ''){ // update user_address
      $sql = "UPDATE user_address SET ";
      $where = " WHERE id=".$address_id;
    }else{ // insert user_address
      $count = $wpdb->get_var(
          $wpdb->prepare(
              "SELECT count(id) FROM user_address where wp_user_id = %d", array($current_user->ID)
          )
      );

      if($count == 0){
        $user_address['shipping_address'] = true;
        $user_address['billing_address'] = true;
      }
      $user_address['wp_user_id'] = $current_user->ID;
      $sql = "INSERT INTO user_address SET ";
    }

    $sql_set = '';

    foreach ($user_address as $key => $val) {
        if ($val != '')
            $sql_set .= $key . " = '" . $val . "', ";
    }
    $sql_set = trim($sql_set, ", ");
    $wpdb->query($sql . $sql_set . $where);

    wp_send_json_success();
  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}


//Ajax functions
add_action('wp_ajax_delete_user_address', 'delete_user_address_callback');

function delete_user_address_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'delete_user_address_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $address_id = $data['id'];

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT wp_user_id FROM user_address where id = %d ", array($address_id)
        )
    );

    if($current_user->ID == $owner){
      $wpdb->query(
          $wpdb->prepare(
              "DELETE FROM user_address WHERE id = %d ", array($address_id)
          )
      );
    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }


  //return $data;
}

//Ajax functions
add_action('wp_ajax_load_billing_address', 'load_billing_address_callback');

function load_billing_address_callback(){
  //$data = $_GET;
  //set_query_var( 'address_id', $data['address_id'] );
  get_template_part( 'address/myaddress', 'billing' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_update_billing_address', 'update_billing_address_callback');

function update_billing_address_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'update_billing_address_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $address_id = $data['billing_address'];

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT wp_user_id FROM user_address where id = %d ", array($address_id)
        )
    );

    if($current_user->ID == $owner){

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE user_address SET billing_address = true where id = %d AND wp_user_id = %d ",
              array($address_id, $current_user->ID)
          )
      );

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE user_address SET billing_address = false where id != %d AND wp_user_id = %d ",
              array($address_id, $current_user->ID)
          )
      );
    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}


//Ajax functions
add_action('wp_ajax_load_shipping_address', 'load_shipping_address_callback');

function load_shipping_address_callback(){
  //$data = $_GET;
  //set_query_var( 'address_id', $data['address_id'] );
  get_template_part( 'address/myaddress', 'shipping' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_update_shipping_address', 'update_shipping_address_callback');

function update_shipping_address_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'update_shipping_address_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  $address_id = $data['shipping_address'];

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT wp_user_id FROM user_address where id = %d ", array($address_id)
        )
    );

    if($current_user->ID == $owner){

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE user_address SET shipping_address = true where id = %d AND wp_user_id = %d ",
              array($address_id, $current_user->ID)
          )
      );

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE user_address SET shipping_address = false where id != %d AND wp_user_id = %d ",
              array($address_id, $current_user->ID)
          )
      );
    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}


function tamzang_bp_user_address_nav_adder()
{
    global $bp;
    if (bp_is_user()) {
        $user_id = $bp->displayed_user->id;
    } else {
        $user_id = 0;
    }
    if ($user_id == 0) {
        return;
    }

    //$screen_function = tamzang_user_address();

    bp_core_new_nav_item(
        array(
            'name' => 'สมุดที่อยู่',
            'slug' => 'address',
            'position' => 100,
            'show_for_displayed_user' => false,
            'screen_function' => 'tamzang_user_address_screen',
            'item_css_id' => 'lists',
            'default_subnav_slug' => 'address'
        ));
}

add_action('bp_setup_nav', 'tamzang_bp_user_address_nav_adder',100);

function tamzang_user_address_screen()
{
  //add_action( 'bp_template_title', 'tamzang_user_address_screen_title' );
  add_action( 'bp_template_content', 'tamzang_user_address_screen_content' );
  bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function tamzang_user_address_screen_title()
{
  ?>
  <h1>สมุดที่อยู่</h1>

  <?php
}

function tamzang_user_address_screen_content()
{
  wp_enqueue_script( 'tamzang_jquery_validate', get_stylesheet_directory_uri() . '/js/jquery.validate.min.js' , array(), '1.0',  false );
  ?>
  <div id="address-content" class="wrapper-loading">
    <?php get_template_part( 'address/myaddress', 'list' ); ?>
  </div>
  <?php
}


function tamzang_bp_user_shop_nav_adder()
{
    global $bp;
    if (bp_is_user()) {
        $user_id = $bp->displayed_user->id;
    } else {
        $user_id = 0;
    }
    if ($user_id == 0) {
        return;
    }

    //$screen_function = tamzang_user_address();

    bp_core_new_nav_item(
        array(
            'name' => 'ร้านค้าของฉัน',
            'slug' => 'myshop',
            'position' => 101,
            'show_for_displayed_user' => false,
            'screen_function' => 'tamzang_user_shop_screen',
            'item_css_id' => 'lists',
            'default_subnav_slug' => 'myshop'
        ));
}

add_action('bp_setup_nav', 'tamzang_bp_user_shop_nav_adder',101);

function tamzang_user_shop_screen()
{
  //add_action( 'bp_template_title', 'tamzang_user_shop_screen_title' );
  add_action( 'bp_template_content', 'tamzang_user_shop_screen_content' );
  bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function tamzang_user_shop_screen_title()
{
  ?>
  <h1>ร้านค้าของฉัน</h1>

  <?php
}

function tamzang_user_shop_screen_content()
{
  global $wpdb, $current_user;

  $my_query = new WP_Query( array(
      'post_type' => 'gd_place',
      'order'             => 'ASC',
      'orderby'           => 'title',
      'author' => $current_user->ID,
      'post_per_page' => -1,
      'nopaging' => true
  ) );

  if ( $my_query->have_posts() ) {

    ?>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <th>ชื่อร้านค้า</th>
          <th></th>
          <th></th>
          <th></th>
        </thead>
        <tbody>

        <?php

        while ( $my_query->have_posts() ) {

            $my_query->the_post();
            echo '<tr>';
            echo '<td>';
            echo '<a href="' .get_permalink(). ' ">';
            the_title();
            echo '</a>';
            echo '</td>';
            echo '<td style="text-align:center;"><a class="btn btn-info btn-block" href="'. home_url('/shop-order/') . '?pid='.get_the_ID() .'"><span style="color: #ffffff !important;" >รายการสั่งซื้อของร้าน</span></a></td>';
            echo '<td style="text-align:center;"><a class="btn btn-success btn-block" href="'. home_url('/add-listing/') . '?listing_type=gd_product&shop_id='.get_the_ID() .'"><span style="color: #ffffff !important;" >เพิ่มสินค้า</span></a></td>';
            echo '<td style="text-align:center;"><a class="btn btn-primary btn-block" href="'. home_url('/product-list/') . '?pid='.get_the_ID() .'"><span style="color: #ffffff !important;" >แก้ไขสินค้า</span></a></td>';
            echo '</tr>';

        }


        ?>

        </tbody>
      </table>
    </div>

    <?php


  }

}

	// Make Google map direction
function geodirectory_detail_page_google_map_link( $options ) {
    global $post;
    $post_type = geodir_get_current_posttype();
    if (($post_type !=gd_product)&&(!empty($post->post_latitude) && !empty($post->post_longitude) )) {
       
        $maps_url = add_query_arg( array(
                        'q' => get_the_title(),
                        'sll' => $post->post_latitude . ',' . $post->post_longitude,
                    ), 'https://maps.google.com/maps' );
        ?>
        <div class="direction_button">
        <p><a href="<?php echo $maps_url; ?>" target="_blank"><input type=button id=direction_button value='Get Directions on Google Maps'></a></p>
        </div>
        <?php
    }
}
//add_action( 'geodir-whoop-listing-slider-div', 'geodirectory_detail_page_google_map_link',10,2);
add_action( 'whoop_detail_page_hide_map', 'geodirectory_detail_page_google_map_link',10,2);

function hide_map_on_product_detail($hide_map){
    $post_type = geodir_get_current_posttype();
    if($post_type == "gd_product")
        return true;
    else
        return $hide_map;
}

add_filter( 'whoop_detail_page_hide_map', 'hide_map_on_product_detail',20,1);

function shop_product_list_tab($arr_tabs){

    $post_type = geodir_get_current_posttype();

    if($post_type == "gd_product"){
        unset($arr_tabs['post_map']);
        $arr_tabs['post_profile']['heading_text'] = "รายละเอียดสินค้า";
    }else{
        $arr_tabs['product_list'] = array(
            'heading_text'  => __( 'รายการสินค้า', 'geodirectory' ),
            'is_active_tab' => false,
            'is_display'    => true,
            'tab_content'   => ''
        );
    }



    return $arr_tabs;
}

add_filter('geodir_detail_page_tab_list_extend', 'shop_product_list_tab', 10, 1);

function remove_sidebar_right_from_product_detail(){
    $post_type = geodir_get_current_posttype();
    if($post_type == "gd_product")
        remove_action('geodir_detail_sidebar', 'geodir_action_details_sidebar', 10);
}

add_action('geodir_detail_sidebar', 'remove_sidebar_right_from_product_detail', 1);

function add_hidden_shop_id(){
    $listing_type = sanitize_text_field($_REQUEST['listing_type']);
    if($listing_type == "gd_product"){
        if(!empty($_REQUEST['shop_id'])){
            $post = geodir_get_post_info($_REQUEST['shop_id']);
            if($post){
                $is_current_user_owner = geodir_listing_belong_to_current_user((int)$post->ID);
                if ($is_current_user_owner)
                    echo '<input type="hidden" name="geodir_shop_id" id="geodir_shop_id" value="'.$post->ID.'"/>';
                else
                    wp_redirect(home_url());
            }
            else
                wp_redirect(home_url());
        }else{
            wp_redirect(home_url());
        }
    }
}

add_action('geodir_before_detail_fields', 'add_hidden_shop_id', 10);

// function test_request_info($request_info){
//     file_put_contents( dirname(__FILE__).'/debug/request_info.log', var_export( $request_info, true));
//     return $request_info;
// }

// add_filter('geodir_action_get_request_info', 'test_request_info', 10, 1);

function add_shop_link(){
    global $post, $preview;

    $is_current_user_owner = geodir_listing_belong_to_current_user();
    if (!$preview){

        if ($is_current_user_owner){
            $post_id = $post->ID;
            echo ' <p class="edit_link"><i class="fa fa-pencil"></i> <a href="' . home_url('/add-listing/') . '?listing_type=gd_product&shop_id='.$post_id . '">เพิ่มสินค้า</a></p>';
            echo ' <p class="edit_link"><i class="fa fa-pencil"></i> <a href="' . home_url('/product-list/') . '?pid='.$post_id . '">แก้ไขสินค้า</a></p>';
            echo ' <p class="edit_link"><i class="fa fa-pencil"></i> <a href="' . home_url('/shop-order/') . '?pid='.$post_id . '">รายการสั่งซื้อของร้าน</a></p>';
        }
        // else{
        //     echo ' <p class="edit_link"><i class="fa fa-pencil"></i> ' . do_shortcode( '[popup_anything id="34987"]' ) . '</p>';
        // }

    }
}

add_action('geodir_after_edit_post_link', 'add_shop_link', 10);

function tamzang_apply_shop_id_temp($where, $post_type){
    if ( is_page_template( 'product_list.php' ) ){
        $where .= ' AND geodir_shop_id = '.$_GET['pid'].' ';
    }else if(geodir_is_page( 'detail' )){
        global $post;
        $where .= ' AND geodir_shop_id = '.$post->ID.' ';
    }
    echo '<h1>'.$where.'</h1>';
    return $where;
}

//add_filter('geodir_filter_widget_listings_where', 'tamzang_apply_shop_id', 10, 2);

function tamzang_apply_shop_id($where, $post_type){
    $post_id = '';
    if(isset($_GET['pid']))
        $post_id = $_GET['pid'];
    else if(isset($_POST['pid'])){
        $post_id = $_POST['pid'];
    }
    else{
        global $post;
        if($post->post_type == 'gd_product')
            $post_id = $post->geodir_shop_id;
        else
            $post_id = $post->ID;
    }
    $where .= ' AND geodir_shop_id = '.$post_id.' ';
    return $where;
}

function tamzang_apply_category_id($where, $post_type){
    $array_cat = array();
    $array_cat = get_ancestors( $_POST['cat_id'], 'gd_productcategory' );
    $array_cat[] = $_POST['cat_id'];
    $where .= ' AND default_category in ('.implode(",",$array_cat).') ';
    return $where;
}

function inner_join_user_id($join, $post_type){
    global $current_user;
    $join .= " INNER JOIN shopping_cart s ON (s.wp_user_id = ".$current_user->ID." AND 
                wp_geodir_gd_product_detail.post_id = s.product_id)";
    return $join;
}

function select_shopping_cart_field($fields, $table, $post_type){
    return $fields.', s.qty as shopping_cart_qty ';
}



function remove_add_photo_btn(){
    global $post;
    if($post->post_type == 'gd_product'){
        $is_current_user_owner = geodir_listing_belong_to_current_user();
        if (!$is_current_user_owner)
            return false;
    }

    return true;
}

add_filter('whoop_big_header_show_add_photo_btn', 'remove_add_photo_btn', 10);

function excerpt_read_more_link($output) {
    global $post;
    if ($post->post_type != 'gd_product')
    {
      $output .= '<p><a href="'. get_permalink($post->ID) . '">read more</a></p>';  
    }
    return $output;
}
add_filter('the_excerpt', 'excerpt_read_more_link');

function check_product_owner_before_add_photo(){
    global $post;
    if($post->post_type == 'gd_product'){
        $is_current_user_owner = geodir_listing_belong_to_current_user();
        if (!$is_current_user_owner)
            wp_redirect(get_permalink($post->ID));
    }
}

add_action('geodir_biz_photos_main_content', 'check_product_owner_before_add_photo', 1);

function remove_location_url($breadcrumb, $separator){
    global $post;
    if(geodir_is_page( 'detail' ) && ($post->post_type == 'gd_product')){
        $arr = explode($separator,$breadcrumb);
        $lenght = count($arr);
        $shop_link = '<a href="'.get_permalink($post->geodir_shop_id).'">'.get_the_title($post->geodir_shop_id).'</a>';
        $breadcrumb = $arr[0].$separator.$shop_link.$separator.$arr[1].$separator.$arr[$lenght-2].$separator.$arr[$lenght-1];
    }
    return $breadcrumb;
}

add_filter('geodir_breadcrumb', 'remove_location_url',10,2);

function product_pagination($max_num_pages,$paged, $prev = '«', $next = '»') {
    //global $wp_query, $wp_rewrite;
    //$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
    $pagination = array(
        'base' => get_pagenum_link(1) . '%_%',
        'format' => '&paged=%#%',
        'total' => $max_num_pages,
        'current' => $paged,
        'prev_text' => __($prev),
        'next_text' => __($next),
        'type' => 'array'
    );

    $arr_page = paginate_links( $pagination );
    $html = "";
    if(!empty($arr_page)){
        $html .= '<ul class="pagination pagination-lg">';
        foreach ( $arr_page as $key => $page_link ){
            $html .= '<li class="page-item '.( strpos( $page_link, 'current' ) !== false ? 'active' : '').'">'.$page_link.'</li>';
        }
        $html .= '</ul>';
        $html .= '';
    }
 
    echo $html;
}



function shop_product_list_content($tab_index){
    global $post;
    if($tab_index == 'product_list')
    {
        $default_category_id = geodir_get_post_meta( $post->ID, 'default_category', true );
        $default_category = $default_category_id ? get_term( $default_category_id, 'gd_placecategory' ) : '';
        $parent = get_term($default_category->parent);

        //$geodir_tamzang_id = geodir_get_post_meta( $post->ID, 'geodir_tamzang_id', true );

        if(($parent->name == "อาหาร")||($default_category->name == "อาหาร"))
        {
            echo create_dropdown_categort(1,$post->ID);
            echo '<div id="tamzang-menu">';
            tamzang_menu_view($post->ID);
            echo '</div>';
        }
        else
        {
            echo create_dropdown_categort(2,$post->ID);
            echo '<div id="tamzang-menu">';
            tamzang_ecommerce_view($post->ID);
            echo '</div>';
        }
    }
}

add_action( 'geodir_after_tab_content', 'shop_product_list_content', 10, 1);

function tamzang_menu_view($post_id)
{
    set_query_var( 'post_id', $post_id );
    //set_query_var( 'geodir_tamzang_id', $geodir_tamzang_id );
    set_query_var( 'cat_id', 0 );
    get_template_part( 'menu-view' );
}

//Ajax functions
add_action('wp_ajax_nopriv_load_tamzang_ecommerce_view', 'load_tamzang_ecommerce_view_callback');

function load_tamzang_ecommerce_view_callback(){
  $data = $_POST;
  set_query_var( 'post_id', $data['post_id'] );
  set_query_var( 'cat_id', $data['cat_id'] );
  get_template_part( 'ecommerce-view' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_nopriv_load_tamzang_menu_view', 'load_tamzang_menu_view_callback');
add_action('wp_ajax_load_tamzang_menu_view', 'load_tamzang_menu_view_callback');

function load_tamzang_menu_view_callback(){
  $data = $_POST;
  set_query_var( 'pid', $data['pid'] );
  set_query_var( 'cat_id', $data['cat_id'] );
  if($data['cat_type'] == 1)
    get_template_part( 'menu-view' );
  else
    get_template_part( 'ecommerce-view' );
  wp_die();
}

function test_echo($html,$cf,$post_type){
    echo "<h1>html: ".$html."</h1>";
    echo "<h1>cf: ".print_r($cf)."</h1>";
    echo "<h1>html: ".$post_type."</h1>";
}

function create_dropdown_categort($type, $post_id){
    global $wpdb;
    $catList = get_cat_id_from_shop($post_id);
    $result = create_array_categort($catList);
     $html = '';
     if(!empty($result)){
         $html .= '<div class="order-row"><div class="order-col-4" style="float:right">
         <select id="dd_cat" data-cat_type="'.$type.'" data-id="'.$post_id.'">';
         foreach($result as $cl) {
             $html .= '<option value="'.$cl["id"].'">'.$cl["name"].'</option>';
         }
         $html .= '</select></div></div><div class="order-clear"></div>';
     }
	 return $html;
}

function create_array_categort($cat_list){
    $cate_array = array();
    $top_level = array();

    foreach($cat_list as $cate){    
        $temp = end(get_ancestors( $cate->default_category, 'gd_productcategory' ));
        if(!empty($temp))    
            $top_level[] = $temp;
        else
            $top_level[] = $cate->default_category;
        
    }

    $top_level = array_unique($top_level);

    foreach($top_level as $cate_id){
        $spacing = '';
        if(!empty($cate_id)){
            $cate_array[] = array("id" => $cate_id, "name" => $spacing . get_term_by('id', $cate_id, 'gd_productcategory')->name);
            $cate_array = fetchCategoryTree($cate_id, $spacing . '&nbsp;&nbsp;', $cate_array);
        }
    }
    return $cate_array;
}

function get_cat_id_from_shop($post_id){
    global $wpdb;

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DISTINCT(default_category) FROM wp_geodir_gd_product_detail where geodir_shop_id = %d ", array($post_id)
        )
    );

    return $results;
}
function fetchCategoryTree($parent = 0, $spacing = '', $user_tree_array = '') {

    if (!is_array($user_tree_array))
      $user_tree_array = array();
    

    $args = array( 'taxonomy' => 'gd_productcategory', 'parent' => $parent, 'hide_empty' => true );
    $terms = get_terms( $args );

    foreach ( $terms as $term ){
      $user_tree_array[] = array("id" => $term->term_id, "name" => $spacing . $term->name);
      $user_tree_array = fetchCategoryTree($term->term_id, $spacing . '&nbsp;&nbsp;', $user_tree_array);
    }
    return $user_tree_array;
  }

// AJAX function
add_action('wp_ajax_get_restaurant_positon', 'get_restaurant_positon');
//get  restaurant position by Tamzang_id
function get_restaurant_positon($tamzang_id) {
  
	 global $wpdb;
	//$data = $_POST;

	
  
	//$tamzang_id = $data['tamzang_id'];
	
	$sql = "SELECT * FROM wp_geodir_gd_place_detail where geodir_tamzang_id = ".$tamzang_id."";
	$result_res  = $wpdb->get_results($sql, ARRAY_A );
	$res_lat = $result_res[0]['post_latitude'];
	$res_lon = $result_res[0]['post_longitude'];
	
	return array($res_lat,$res_lon);
	/*
	$response= array(
		'lat' => $res_lat,
		'lon' => $res_lon
	);
	*/
	//wp_send_json_success($response);
	
	//wp_send_json_success("Work!!");
}

// AJAX function
add_action('wp_ajax_get_driver_restaurant', 'get_driver_restaurant');
//get list Driver for restaurant
function get_driver_restaurant() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "get_driver_restaurant START!", true));
	//print "Get Driver start";
	//$tamzang_id = $data['tamzang_id'];
	$tamzang_id = $_POST['tamzang_id'];
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Res Id:".$tamzang_id, true));
	$return_arr = array();
	$return_web = array();
	$restaurant_array = array();
    // Get Restaurant Position
	list($res_lat,$res_lon) = get_restaurant_positon($tamzang_id);
	
	// Put in ARRAY
	$restaurant_array['id'] = "res_id";
	$restaurant_array['Lat'] = $res_lat;
	$restaurant_array['Lon'] = $res_lon;
	$return_arr[] = $restaurant_array;
	
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $res_lat, true),FILE_APPEND);
	
	
	$sql = "SELECT * FROM driver ";
	
	$result_driver = $wpdb->get_results($wpdb->prepare("SELECT * FROM driver ",array()));
	
	//$total_driver = $wpdb->num_rows;
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $result_driver, true),FILE_APPEND);
	foreach ($result_driver as $driver)
	{
		$maker_drivers = array();
		$driver_lat = $driver->latitude;
		$driver_lon = $driver->longitude;
		$driver_id = $driver->Driver_id;
	    $maker_drivers['id'] = $driver_id;
		$maker_drivers['Lat'] = $driver_lat;
		$maker_drivers['Lon'] = $driver_lon;
		file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $driver_lat, true));
		
		$distance = distance($res_lat,$res_lon,$driver_lat,$driver_lon,"K");
		file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $distance, true),FILE_APPEND);
		if($distance <= 3)
		{
			//array_push($maker_drivers,$driver_id);
			$return_arr[] = $maker_drivers;
		}
		

	}
/*
	 $webhtmls = array();
	 $webhtmls['web'] = listdriver($result_driver);
	 $return_arr[] = $webhtmls;
	 */
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $return_arr, true));
	wp_send_json_success($return_arr);
	//wp_send_json_success(json_encode($maker_drivers));
	//echo json_encode($maker_drivers);
	//return array($maker_drivers);
}

// Calculate Distance
function distance($lat1, $lon1, $lat2, $lon2,$unit){
  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    return 0;
  }
  else {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
      return ($miles * 1.609344);
    } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
      return $miles;
    }
  }
}

//AJAX FUNCTION
add_action('wp_ajax_listdriver', 'listdriver');
function listdriver(){


  $driver_num = $_GET;
  set_query_var( 'total_driver', $driver_num['driver_num']);
  set_query_var( 'tamzang_id', $driver_num['tamzang_id']);
  $driver_id_array = explode(",", $driver_num['driver_id']);
  set_query_var( 'driver_id', $driver_num['driver_id']);
  
  //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_num['tamzang_id'], true));

  //set_query_var( 'total_driver', $driver_num );
  //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Driver START", true));
  get_template_part( 'ajax-driver-list' );

  wp_die();
}




function tamzang_bp_user_driver_nav_adder()
{
    global $bp;
    if (bp_is_user()) {
        $user_id = $bp->displayed_user->id;
    } else {
        $user_id = 0;
    }
    if ($user_id == 0) {
        return;
    }

    //$screen_function = tamzang_user_address();

    bp_core_new_nav_item(
        array(
            'name' => 'driver',
            'slug' => 'driver',
            'position' => 102,
            'show_for_displayed_user' => false,
            'screen_function' => 'tamzang_user_driver_screen',
            'item_css_id' => 'lists',
            'default_subnav_slug' => 'driver'
        ));
}

add_action('bp_setup_nav', 'tamzang_bp_user_driver_nav_adder',102);

function tamzang_user_driver_screen()
{
  add_action( 'bp_template_content', 'tamzang_user_driver_screen_content' );
  bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
}

function tamzang_user_driver_screen_content()
{
  wp_enqueue_script( 'tamzang_jquery_validate', get_stylesheet_directory_uri() . '/js/jquery.validate.min.js' , array(), '1.0',  false );

//   wp_register_style( 'gd-captcha-style', GEODIR_RECAPTCHA_PLUGIN_URL . '/css/gd-captcha-style.css', array(), GEODIR_RECAPTCHA_VERSION);
//   wp_enqueue_style( 'gd-captcha-style' );
  ?>
  <div id="driver-content" class="wrapper-loading">
    <?php
        global $wpdb, $current_user;

        $driver_approve = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT approve FROM register_driver where wp_user_id = %d ", array($current_user->ID)
            )
        );

        if(empty($driver_approve)){
            get_template_part( 'driver/driver', 'register' ); 
        }else{
            if($driver_approve)
                get_template_part( 'driver/driver', 'order' );
            else
                get_template_part( 'driver/driver', 'pending' ); 
        }
        
    ?>
  </div>
  <?php

}

add_action('wp_ajax_register_driver', 'register_driver_callback');

function register_driver_callback(){
  global $wpdb, $current_user;
  $data = $_POST;

  if ( check_ajax_referer( 'register_driver_' . $current_user->ID, 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try
  {

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM register_driver where wp_user_id = %d", array($current_user->ID)
        )
    );

    if($id == NULL || empty($id) ){
        $current_date = date("Y-m-d H:i:s");

        $wpdb->query(
            $wpdb->prepare(
              "INSERT INTO register_driver SET wp_user_id = %d, name = %s, phone = %s, note = %s, regis_date = %s, approve = %d ",
              array($current_user->ID, $data['name'], $data['phone'], $data['note'], $current_date, 0)
            )
        );

        wp_send_json_success();
    }
    else{
        wp_send_json_error("ท่านได้สมัครสมาชิคแล้ว");
    }
  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_load_driver_pending', 'load_driver_pending_callback');

function load_driver_pending_callback(){
  get_template_part( 'driver/driver', 'pending'  );
  wp_die();
}

// AJAX function
add_action('wp_ajax_update_driver_piority', 'update_driver_piority');
//get list Driver for restaurant
function update_driver_piority() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_update_driver_piority START!", true));
    $data = $_POST;
	
	$driver_id = $_POST['driverID'];
	$Tamzang_id = $_POST['TamzangId'];
	$priority_num = $_POST['priority'];

	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $priority_num, true));

	
	$sql = $wpdb->prepare(
            "SELECT * FROM driver_of_restaurant WHERE Tamzang_id = %d ",
            array($Tamzang_id)
        );
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
	$result_driver = $wpdb->get_results($sql);
	
	//$total_driver = $wpdb->num_rows;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $result_driver, true));
	$is_viewed = $result[0]['id'];
	$is_exist = $wpdb->num_rows;
	
	
	if($is_exist > 0 )
	{
    $wpdb->query(
        $wpdb->prepare(
          "UPDATE  driver_of_restaurant SET win_%d = %d where Tamzang_id = %d",
          array($priority_num,$driver_id,$Tamzang_id)
        )
    );
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Update", true));
	}
	else{
		$wpdb->insert( 
			'driver_of_restaurant', 
			array( 'Tamzang_id' => $Tamzang_id, 'win_'.$priority_num => $driver_id), 
			array( '%d','%d','%d')
		);
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Insert", true));
	}



	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $return_arr, true));
	wp_send_json_success($return_arr);

}

//Ajax functions
add_action('wp_ajax_load_order_list', 'load_order_list_callback');

function load_order_list_callback(){
    $data = $_GET;
    set_query_var( 'pageNumber', $data['page'] );
    get_template_part( 'driver/driver', 'order_list'  );
    wp_die();
}


// AJAX function
add_action('wp_ajax_get_order_list_delivery', 'get_order_list_delivery');
//get list Driver for restaurant
function get_order_list_delivery() {
    global $wpdb;
	$return_arr = array();
	$return_web = array();
	$restaurant_array = array();
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_get_order_list_delivery START!", true));
    $data = $_POST;
	
    $Order_id = $data['OrderId'];
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $Order_id, true));

	if($Order_id == null)
	{
		$sql = $wpdb->prepare(
            "SELECT * FROM orders WHERE deliver_ticket = 'Y'and status <> 99 order by id ",array()
        );
	}
	else{
		$sql = $wpdb->prepare(
            "SELECT * FROM orders WHERE deliver_ticket = 'Y' and id = %d and status <> 99",array($Order_id)  
        );
	}
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
	$result_list_order = $wpdb->get_results($sql);
	
	//$total_driver = $wpdb->num_rows;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $result_driver, true));
	foreach ($result_list_order as $list_order)
	{
		$assign_drivers = array();
		$res_id = $list_order->post_id;
		$order_id = $list_order->id;
		$buyer_id = $list_order->wp_user_id;
		// get name of buyer
		$name = get_userdata($buyer_id);
		$order_date = $list_order->order_date;
	    $assign_drivers['id'] = $res_id;
		$assign_drivers['order_id'] = $order_id;
		$assign_drivers['buyer_name'] = $name->user_login;
		$name = get_userdata($buyer_id);
		$assign_drivers['order_date'] = $order_date;
		//file_put_contents( dirname(__FILE__).'/debug/driver_ID.log', var_export( $name->user_login, true));	
		
		$return_arr[] = $assign_drivers;
	}
	wp_send_json_success($return_arr);
    wp_die();
}

// Get restaurant name and Tamzang ID
function get_res_name_id($post_id){
	global $wpdb;
	
	$sql = "SELECT * FROM wp_geodir_gd_place_detail where post_id = ".$post_id."";
	$result_res  = $wpdb->get_results($sql, ARRAY_A );
	$res_name = $result_res[0]['post_title'];
	$tamzang_id = $result_res[0]['geodir_tamzang_id'];
	
	return array($res_name,$tamzang_id);
}

//AJAX FUNCTION
add_action('wp_ajax_listdriverassign', 'listdriverassign');
function listdriverassign(){


	$driver_assign_array = $_GET;
	set_query_var( 'total_order', $driver_assign_array['order_num']); 
	set_query_var( 'res_id', $driver_assign_array['res_id']);
	set_query_var( 'order_id', $driver_assign_array['order_id']);    
  
  //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_assign_array['res_id'], true));

  get_template_part( 'ajax-driver-assign' );
  wp_die();
}

function driver_map( $title, $post_latitude, $post_longitude ) {

    if ( !empty( $title ) && !empty( $post_latitude ) && !empty( $post_longitude ) ) {
        $maps_url = add_query_arg( array(
                        'q' => $title,
                        'sll' => $post_latitude . ',' . $post_longitude,
                    ), 'http://maps.google.com/' );
        ?>
        <a href="<?php echo $maps_url; ?>" class="btn btn-info" target="_blank"><span style="color: #ffffff !important;" >แผนที่</span></a>
        <?php
    }
	else if (empty( $title ) && !empty( $post_latitude ) && !empty( $post_longitude ))
	{
		$maps_url = add_query_arg( array(
                        'q' =>$post_latitude . ',' . $post_longitude,
                    ), 'http://maps.google.com/' );
        ?>
        <a href="<?php echo $maps_url; ?>" class="btn btn-info" target="_blank"><span style="color: #ffffff !important;" >แผนที่</span></a>
        <?php
	}
}

//Ajax functions
add_action('wp_ajax_driver_confirm_order', 'driver_confirm_order_callback');

function driver_confirm_order_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'driver_confirm_order_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT Driver_id FROM driver_order_log where Id = %d ", array($data['log_id'])
        )
    );

    if($current_user->ID == $owner){

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver_order_log SET status = 2 where Id = %d ",
                array($data['log_id'])
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE orders SET status = 2 where id = %d ",
                array($data['id'])
            )
        );

    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_driver_reject_order', 'driver_reject_order_callback');

function driver_reject_order_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'driver_confirm_order_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT Driver_id FROM driver_order_log where Id = %d ", array($data['log_id'])
        )
    );

    if($current_user->ID == $owner){

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE driver_order_log SET status = 4 where Id = %d ",
              array($data['log_id'])
          )
      );

    }

    wp_send_json_success();

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

function driver_text_step($step){
    if($step == 2)
        return "ยืนยันคำสั่งซื้อ";
    elseif($step == 3)
        return "รับสินค้า";
    elseif($step == 4)
        return "ปิดงาน";
}

add_action('wp_ajax_driver_next_step', 'driver_next_step_callback');

function driver_next_step_callback(){
  global $wpdb, $current_user;

  $data = $_POST;

  // check the nonce
  if ( check_ajax_referer( 'driver_next_step_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT Driver_id FROM driver_order_log where Driver_order_id = %d and driver_id = %d and status = 2", array($data['id'],$current_user->ID)
        )
    );

    if(!empty($owner)){

        $status = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT status FROM orders where id = %d ", array($data['id'])
            )
        );

        if($status < 5){
            $status++;

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE orders SET status = %d where id = %d ",
                    array($status, $data['id'])
                )
            );
            if($status == 5){
                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE driver_order_log SET status = 3 where Driver_id = %d AND Driver_order_id = %d ",
                        array($owner, $data['id'])
                    )
                );

                wp_send_json_success("close");
            }
            else
                wp_send_json_success(driver_text_step($status));
        }else{
            wp_send_json_error("error2");
        }

    }else{
        wp_send_json_error("error1");
    }

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_driver_adjust_price', 'driver_adjust_price_callback');

function driver_adjust_price_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'driver_adjust_price_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT Driver_id FROM driver_order_log where Id = %d ", array($data['log_id'])
        )
    );

    if($current_user->ID == $owner){

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE orders SET driver_adjust = %f where id = %d ",
              array($data['adjust'], $data['id'])
          )
      );

      wp_send_json_success();

    }else{
        wp_send_json_error();
    }

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_customer_response_adjust', 'customer_response_adjust_callback');

function customer_response_adjust_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'customer_response_adjust_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $customer = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT o.wp_user_id,o.total_amt,o.driver_adjust,s.price FROM orders as o
            inner join shipping_address as s on o.shipping_id = s.id
            where o.id = %d ", array($data['id'])
        )
    );

    if($current_user->ID == $customer->wp_user_id){

      $wpdb->query(
          $wpdb->prepare(
              "UPDATE orders SET adjust_accept = %d ".($data['status'] == "0" ? ", status = 99" : "")." where wp_user_id = %d AND id = %d ",
              array($data['status'], $customer->wp_user_id, $data['id'])
          )
      );

      wp_send_json_success($customer->total_amt+$customer->driver_adjust+$customer->price);

    }else{
        wp_send_json_error();
    }

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_load_driver_order_template', 'load_driver_order_template_callback');

function load_driver_order_template_callback(){
  get_template_part( 'driver/driver', 'order_template' );
  wp_die();
}

//Ajax functions
add_action('wp_ajax_supervisor_assign_order', 'supervisor_assign_order_callback');

function supervisor_assign_order_callback(){
  global $wpdb, $current_user;
  //$current_user->ID;

  $data = $_POST;
  //file_put_contents( dirname(__FILE__).'/debug/debug_delete_user_address.log', var_export( $data, true));

  // check the nonce
  if ( check_ajax_referer( 'supervisor_assign_order_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error();
  }

  try {

    $order = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM driver_order_log where Id = %d ", array($data['log_id'])
        )
    );

    if($current_user->ID == $order->driver_id){
        /*
        $employee = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT driver_id FROM driver where driver_id = %d AND supervisor = %d", array($data['driver_id'],$current_user->ID)
            )            
        );    
        */
        //Bank Adjust sql for driver who on task can't not recive any more order
        $employee = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT driver.driver_id,driver.driver_name FROM driver 
        WHERE Supervisor=%d
        and driver.driver_id NOT IN (SELECT DISTINCT driver_id FROM driver_order_log WHERE driver_order_id=%d or status IN (1,2))",
        array($current_user->ID,$data['driver_id'])
            )            
        );  
        if(!empty($employee )){
            $current_date = date("Y-m-d H:i:s");
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE driver_order_log SET status = 4, transfer_date = %s where Id = %d ",
                    array($current_date, $data['log_id'])
                )
            );

            $check_order = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM driver_order_log where driver_id = %d AND driver_order_id = %d AND status = 4 ", array($data['driver_id'],$order->driver_order_id)
                )
            );// ตรวจว่า A -> B แล้ว B -> A หรือเปล่า

            if(empty($check_order)){
                $wpdb->query(
                    $wpdb->prepare(
                    "INSERT INTO driver_order_log SET tamzang_id = %d, driver_id = %d, driver_order_id = %d, status = %d, assign_date = %s ",
                    array($order->tamzang_id, $data['driver_id'], $order->driver_order_id, 2, $current_date)
                    )
                );

                wp_send_json_success();
            }else{
                wp_send_json_error();
            }
        }
    }else{
        wp_send_json_error();
    }

    
  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

//Ajax functions
add_action('wp_ajax_driver_ready', 'driver_ready_callback');

function driver_ready_callback(){
  global $wpdb, $current_user;

  $data = $_POST;

  // check the nonce
  if ( check_ajax_referer( 'driver_ready_' . $data['id'], 'nonce', false ) == false ) {
      wp_send_json_error("error nonce");
  }

  try {

    $owner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT Driver_id FROM driver where Driver_id = %d ", array($data['id'])
        )
    );

    if($current_user->ID == $owner){

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE driver SET is_ready = !is_ready where Driver_id = %d ",
                array($data['id'])
            )
        );

        wp_send_json_success();

    }else{
        wp_send_json_error("wrong user");
    }

  } catch (Exception $e) {
      wp_send_json_error($e->getMessage());
  }

}

// AJAX function
add_action('wp_ajax_assign_order_driver', 'assign_order_driver');
//get list Driver for restaurant
function assign_order_driver() {
    global $wpdb;
	$current_date = date("Y-m-d H:i:s");
	$return_arr = array();
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_update_driver_piority START!", true));
    $data = $_POST;

    $status = $_POST['status'];
	$order_id = $_POST['orderID'];
    $Tamzang_id = $_POST['TamzangId'];
    $emer_driver_id = $data['driverID'];
    $driver_id = $_POST['priority'];
    $driver_id_sql = (empty($emer_driver_id))?$driver_id:$emer_driver_id;

    


	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_id, true));

	/*
	$sql = $wpdb->prepare(
            "SELECT * FROM driver_order_log WHERE driver_order_id = %d and driver_id = %d ",
            array($order_id,$driver_id)
        );
		*/
		

		
	$sql = "SELECT * FROM driver_order_log where (status = 1 or status = 2) and (driver_order_id =".$order_id." or driver_id =".$driver_id_sql.")";
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
	$result_driver = $wpdb->get_results($sql, ARRAY_A );
	
	//$total_driver = $wpdb->num_rows;
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $sql, true));
	$is_status = $result_driver[0]['status'];
	$is_exist = $wpdb->num_rows;
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $is_status, true),FILE_APPEND);
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $is_exist, true),FILE_APPEND);
	
	//Cancel Assign
	if( ($is_exist >=0) && ($status == "Cancel") )
	{
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Cancel", true));
    $wpdb->query(
        $wpdb->prepare(
          "UPDATE  driver_order_log SET status = 4 where driver_order_id = %d and status in (1,2)",
          array($order_id)
        )
    );
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Update", true));
	$return_result = "Cancel Order is compleate";
	wp_send_json_success($return_result);
	}
	//Abort
	else if( ($is_exist >=0) && ($status == "Abort") )
	{
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Cancel", true));
	// Update order in driver_order_log
    $wpdb->query(
        $wpdb->prepare(
          "UPDATE  driver_order_log SET status = 5 where driver_order_id = %d and status != 4",
          array($order_id)
        )
    );
	// Update order in orders
    $wpdb->query(
        $wpdb->prepare(
          "UPDATE  orders SET deliver_ticket = 'Y',status = 99 where id = %d",
          array($order_id)
        )
    );
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Update", true));
	$return_result = "Abort Order is compleate";
	wp_send_json_success($return_result);
	}
	else if(($is_exist >0) && ($status == null))
	{
		$return_result = "Cannot Assign this order to Driver This Order is already Assign";
		wp_send_json_success($return_result);
	}
	else{
		if(empty($emer_driver_id))
		{
			
			//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "No Emer and Date :".$current_date, true),FILE_APPEND);
			$query = $wpdb->prepare("INSERT INTO driver_order_log SET
                             tamzang_id = %d,driver_id = %s,driver_order_id =%d,status = 1,assign_date =%s",
                             array($Tamzang_id,$driver_id,$order_id,$current_date)
                          );
            $wpdb->query($query);
            $username = get_user_by('id',$driver_id);
		}
		else
		{
			
			$query = $wpdb->prepare("INSERT INTO driver_order_log SET
                             tamzang_id = %d,driver_id = %s,driver_order_id =%d,status =1,assign_date =%s",
                             array($Tamzang_id,$emer_driver_id,$order_id,$current_date)
                          );
            $wpdb->query($query);
            $username = get_user_by('id',$emer_driver_id);
		}
		
		$driver_message = (empty($emer_driver_id))?$driver_id:$emer_driver_id;
        file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "User name is :".$username->user_nicename, true),FILE_APPEND);
       // file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "User name is :".$username, true),FILE_APPEND);
		
		// สร้าง message
		$current_date = date("Y-m-d H:i:s");
		$thread_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT thread_id FROM wp_bp_messages_messages ORDER BY thread_id DESC LIMIT 1 ", array()
			)
		);
		$thread_id++;
		$wpdb->query(
			$wpdb->prepare(
			"INSERT INTO wp_bp_messages_messages SET thread_id = %d, sender_id = 1, subject = %s, message = %s, date_sent = %s ",
			array($thread_id, "ใบสั่งซื้อเลขที่: #".$order_id , '<strong><p style="font-size:14px;">ได้รับ order จากพนักงานตามสั่ง</p> <a href="'.home_url('/members/').$username->user_nicename."/driver/".'">คลิกที่นี่เพื่อดู Order</a></strong>', $current_date)
			)
		);
		
	
		$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO wp_bp_messages_recipients SET user_id = %d, thread_id = %d, unread_count = %d, sender_only = %d, is_deleted = %d ",
			array($driver_message, $thread_id, 1, 0, 0)
		)
		);
	
		$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO wp_bp_messages_recipients SET user_id = 1, thread_id = %d, unread_count = %d, sender_only = %d, is_deleted = %d ",
			array( $thread_id, 0, 1, 0)
		)
		);
		
		
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Insert", true),FILE_APPEND);
		
        // Send Notification to user who Subscribe with OneSignal
        
		$message = "มี Order อาหารมาใหม่จาก Tamzang";
		
		$sql = $wpdb->prepare(
				"SELECT device_id FROM onesignal where user_id=%d ", array($driver_message)
			);
		$player_id_array = $wpdb->get_results($sql);
		foreach ($player_id_array as $list_player_device)
		{
			$player_id = $list_player_device->device_id;
			$response = sendMessage($player_id,$message);
			$return["allresponses"] = $response;
			$return = json_encode( $return);
			file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "Return :".$return."\n", true),FILE_APPEND);
        }
        
		$return_result = "New order is assign";
        wp_send_json_success($return_result);
        //echo $return_result;
	
	}

	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $return_arr, true));
	

	//wp_send_json_success($return_arr);

}

// Onesignal Function Create Notification to player_id
function sendMessage($player_id,$message){
		$playerID = $player_id;
		file_put_contents( dirname(__FILE__).'/debug/onesignal.log',"Send Notice Start to player_ID :".$playerID,FILE_APPEND);
		$content = array(
			"en" => $message
			);
		
		$fields = array(
			'app_id' => "73b7d329-0a82-4e80-aa74-c430b7b0705b",
			'include_player_ids' => array($playerID),
			//'include_player_ids' => array("1c072fb6-f1b3-44ba-9f19-7a6fb5534366","646d645e-382d-45d9-aea9-916401fe3954"),
			'data' => array("foo" => "bar"),
			'contents' => $content
		);
		
		$fields = json_encode($fields);
    	//print("\nJSON sent:\n");
    	//print($fields);
        
        
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
		
        return $response;
        
	}

// AJAX function
add_action('wp_ajax_get_driver_regis', 'get_driver_regis');
//get list Driver for restaurant
function get_driver_regis() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_update_driver_piority START!", true));

	$sql = $wpdb->prepare(
            "SELECT * FROM register_driver where approve = 0 ",array()  
    );
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $sql, true));
	$result_driver = $wpdb->get_results($sql);
	$is_exist = $wpdb->num_rows;

	if($is_exist >0)
	{
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Cancel", true));
		//$result_list_order = $wpdb->get_results($sql);
		foreach ($result_driver as $list_order)
		{
			$assign_drivers = array();
			$usr_id = $list_order->wp_user_id;
			$name = $list_order->name;
			$phone = $list_order->phone;
			$note = $list_order->note;
			// put value into process call Template

			//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $usr_id, true));
			set_query_var( 'usr_id', $usr_id); 
			set_query_var( 'name', $name); 
			set_query_var( 'phone', $phone); 
			set_query_var( 'note', $note); 
			get_template_part( 'ajax-driver-approve' );
		}
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Update", true));	
	
	wp_die();
	}
	else{
		$return_result = "0 Result";
		//wp_send_json_success($return_result);
		return $return_result;
		//get_template_part( 'ajax-driver-approve' );
	}

}

// AJAX function
add_action('wp_ajax_approve_driver', 'approve_driver');
//get list Driver for restaurant
function approve_driver() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "wp_ajax_update_driver_piority START!", true));
	$data = $_POST;
	$usr_id_array = $_POST['UserID'];
	$usr_id = explode(",", $usr_id_array);
	$count = count($usr_id);
	//$Tamzang_id = $_POST['TamzangId'];
	//$priority_num = $_POST['priority'];
	
	// generate sql
	$sql ="(".$usr_id_array.")";
	//$sql = str_replace("'","",$sql);	
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $sql, true));	

	// update status  register_driver		
	$status_sql = "UPDATE register_driver SET approve = 1 where wp_user_id IN ".$sql." and approve = '0'";
	$result_status = $wpdb->get_results($status_sql, ARRAY_A );
	
	// Get Value From register_driver
	$sql = "select * from register_driver where wp_user_id IN ".$sql;
	$result_list = $wpdb->get_results($sql, ARRAY_A );

	
	//$result_list = $wpdb->get_results($sql);
	//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $result_list, true),FILE_APPEND);
	foreach ($result_list as $row => $value )
	{
		$query = $wpdb->prepare("INSERT INTO driver SET
                             Driver_id = %d,driver_name = %s,phone =%d",
                             array($value['wp_user_id'],$value['name'],$value['phone'])
                          );
		$wpdb->query($query);
		//file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( "Insert status ".$insert_status, true),FILE_APPEND);
	}

}

// AJAX function
add_action('wp_ajax_update_driver_location', 'update_driver_location');
//get list Driver for restaurant
function update_driver_location() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "update_driver_location START!", true));
	$data = $_POST;
	$lat_db = $_POST['Lat'];
	$lng_db = $_POST['Lng'];
	$user_id = $_POST['user_id'];	

	// update status  register_driver	
	$status_sql = "UPDATE driver SET latitude = ".$lat_db." ,longitude =".$lng_db." where Driver_id = ".$user_id."";
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $status_sql, true));
	$result_status = $wpdb->get_results($status_sql, ARRAY_A );
}

//Ajax functions
add_action('wp_ajax_chooseHeadDriver', 'chooseHeadDriver');

function chooseHeadDriver(){
	global $wpdb;
	$data = $_GET;
	
	$driver_id = $data['driver_id'];
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $data['driver_id'], true));
	//Check If Driver have supervisor bfr go to Choose_head page
	
	$super_driver_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT supervisor  FROM driver WHERE Driver_id=%d", array($driver_id)
      )
	);
	if(empty($super_driver_id)){
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "No Super Driver", true),FILE_APPEND);
		get_template_part( 'driver/driver', 'choose_head');
		wp_die();
	}
	else{
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Super Driver Exit".$super_driver_id, true),FILE_APPEND);
			$super_driver_name = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT driver_name FROM driver WHERE Driver_id=%d", array($super_driver_id)
				)
			);
		set_query_var( 'superDriver_name', $super_driver_name );
		set_query_var( 'superDriver_id', $super_driver_id );
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Bfr Get Template".$super_driver_name, true),FILE_APPEND);
		get_template_part( 'driver/driver', 'exit_head');
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "After Get Template", true),FILE_APPEND);
		wp_die();
	}
}


// AJAX function
add_action('wp_ajax_update_head_driver', 'update_head_driver');
//get list Driver for restaurant
function update_head_driver() {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "update_head_driver START!", true));

	$super_id = $_POST['driverSuperID'];
	$driver_id = $_POST['driverID'];

	// update supervisor driver	
    $wpdb->query(
        $wpdb->prepare(
          "UPDATE  driver SET supervisor = %d where Driver_id = %d",
          array($super_id,$driver_id)
        )
    );
}


// AJAX function
add_action('wp_ajax_get_delivery_fee', 'get_delivery_fee');
//get list Driver for restaurant
function get_delivery_fee($pid) {
    global $wpdb;
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "update_head_driver START!", true));

	//$post_id = $_POST['postID'];
	$post_id = $pid;
	//$buyer_id = $_POST['buyerID'];
	
	//Get Buyer Latitude and Longitude from address of user
	$buyer_point = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT latitude,longitude FROM user_address where wp_user_id = %d AND shipping_address = 1 ", array(get_current_user_id())
		)
    );
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "point :".$buyer_point->latitude, true));
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "ID :".get_current_user_id(), true),FILE_APPEND);
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "POST ID :".$post_id, true),FILE_APPEND);
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Point Long :".$buyer_point->longitude, true),FILE_APPEND);
	
	//Check point of this address exit
    $deliver_fee = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT price,distance FROM delivery_fee where wp_user_id = %d AND post_id = %d and latitude = %s and longitude = %s ", array(get_current_user_id(),$post_id,$buyer_point->latitude,$buyer_point->longitude)
        )
    );
	
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Price :".$deliver_fee->price, true),FILE_APPEND);
	if(!empty($deliver_fee->price))
	{
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "! Have Delivery_fee", true),FILE_APPEND);
		return array($deliver_fee->price,$deliver_fee->distance);
	}
	else{
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "! Do Not Have Delivery_fee", true),FILE_APPEND);
		//Get Shop Latitude and Longitude from GD_place_detail
		$post_point = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT post_latitude,post_longitude FROM wp_geodir_gd_place_detail where post_id = %d ", array($post_id)
			)
		);
		//Calculate distance from google
		$check = $post_point->post_latitude.":".$post_point->post_longitude.":".$buyer_point->latitude.":".$buyer_point->longitude;
		//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "google distance :".$check, true),FILE_APPEND);
		$distance = google_distance($post_point->post_latitude,$post_point->post_longitude,$buyer_point->latitude,$buyer_point->longitude);
		if($distance != "ขณะนี้ไม่สามารถคำนวนระยะทางของผู้ซื้อได้ชั่วคราว")
		{
			//Calculate delivery Fee
			
			$delivery_value = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT base,base_adjust,km_tier1,km_tier1_value,km_tier2,km_tier2_value FROM delivery_variable where post_id = %d ", array($post_id)
				)
			);
			$range_t1 = (($delivery_value->km_tier1)>=$distance)? $distance:$delivery_value->km_tier1;			
			file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Range T1:".$range_t1, true),FILE_APPEND);
			$range_t2 = (($delivery_value->km_tier1)<=$distance)?((($delivery_value->km_tier2)>=$distance-$range_t1)? $distance-($delivery_value->km_tier1):$delivery_value->km_tier2):0;
			file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Range T2 :".$range_t2, true),FILE_APPEND);
			$deliver_fee = ($delivery_value->base + $delivery_value->base_adjust)+ ($range_t1*$delivery_value->km_tier1_value)+(($range_t2*$delivery_value->km_tier2_value));
			$deliver_fee = round($deliver_fee,2);
			
			$wpdb->query(
				$wpdb->prepare(
				"INSERT INTO delivery_fee SET wp_user_id = %d, post_id = %d, latitude = %s, longitude = %s, price = %f, distance = %s ",
				array(get_current_user_id(),$post_id,$buyer_point->latitude,$buyer_point->longitude,$deliver_fee,$distance)
				)
			);
			file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Delivery_fee :".$deliver_fee, true),FILE_APPEND);
			return array($deliver_fee,$distance);
		}
		else{
			return array(0,0);
		}
	}
}

function google_distance($post_lat,$post_lng,$buyer_lat,$buyer_lng) {
	
	// google map geocode api url

	$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$post_lat,$post_lng&destinations=$buyer_lat,$buyer_lng&key=AIzaSyC3mypqGAf0qnl5xGwsxwQinUIfeiTIYtM";
	//file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "google URL :".$url, true),FILE_APPEND);
	

	//echo $url."<br>";
	// get the json response
    $resp_json = file_get_contents($url);

	// decode the json
	$resp = json_decode($resp_json, true);


	// response status will be 'OK', if able to geocode given address

	if($resp['status'] == "OK")
	{
		// get the important data
		$distance = $resp['rows'][0]['elements'][0]['distance']['value'];
		$int_distance = (float)$distance;
		$final_distance = $int_distance/1000;
		file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Distance : ".$final_distance, true),FILE_APPEND);
		return $final_distance;
	}
	else{
		return "ขณะนี้ไม่สามารถคำนวนระยะทางของผู้ซื้อได้ชั่วคราว";
	}
	
}
//Ajax functions
add_action('wp_ajax_get_driver_super', 'get_driver_super');
function get_driver_super(){
	global $wpdb;
	$data = $_POST;
	
	$driver_id = $data['DriverId'];
	file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( $driver_id, true));
	//Check If Driver have supervisor bfr go to Choose_head page
	
	$super_driver_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT supervisor  FROM driver WHERE Driver_id=%d", array($driver_id)
      )
	);
	if(empty($super_driver_id)){
		wp_send_json_success("Driver คนนี้ไม่มีหัวหน้า");
	}
	else{
		file_put_contents( dirname(__FILE__).'/debug/driver_start.log', var_export( "Super Driver Exit".$super_driver_id, true),FILE_APPEND);
			$super_driver_name = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT driver_name FROM driver WHERE Driver_id=%d", array($super_driver_id)
				)
			);
		$super_drivers['id'] = $super_driver_id;
		$super_drivers['name'] = $super_driver_name;
		$return_arr[] = $super_drivers;
		wp_send_json_success($return_arr); 
	}
}

//AJAX FUNCTION
add_action('wp_ajax_listdriversuper', 'listdriversuper');
function listdriversuper(){
	$driver_assign_array = $_GET;
	set_query_var( 'super_id', $driver_assign_array['super_driver_id']); 
	set_query_var( 'driver_id', $driver_assign_array['driver_id']); 
   
  //file_put_contents( dirname(__FILE__).'/debug/driver.log', var_export( $driver_assign_array['super_driver_id'], true));

  get_template_part( 'ajax-driver-head' );
  wp_die();
}

add_action( 'geodir_listing_after_pinpoint', 'open_time_listview', 10, 3 );

function open_time_listview($post_id, $post){
    $open = date(get_option('time_format'), strtotime($post->geodir_open_time));
    $close = date(get_option('time_format'), strtotime($post->geodir_close_time));
    echo '<div class="geodir_more_info" style="clear:both;">
        <span class="geodir-i-time">
        <i class="fa fa-clock-o"></i>: '.$open.' - '.$close.'</span></div>';
}

add_action( 'geodir_after_listing_post_excerpt', 'short_des_listview', 10, 3 );

function short_des_listview($post){
    echo '<div class="geodir-whoop-address">'.$post->post_content.'</div>';
}

//AJAX FUNCTION
add_action('wp_ajax_updateOnesignal', 'updateOnesignal');
add_action('wp_ajax_nopriv_updateOnesignal', 'updateOnesignal');

function updateOnesignal(){	
	global $wpdb;
	$data = $_POST;
	$queery_sql = $data['doing']; 
	$device_id_bfr = $data['device_id'];
	$device_id = trim($device_id_bfr,'Optional(\\")');
	$user_id = get_current_user_id();
	$device_type = $data['deviceType'];
	
	
	file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal PHP Start", true));
	file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal PHP Is enable is :".$device_id, true),FILE_APPEND);
	

	//Check data about this device in DB
	$super_driver_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT user_id  FROM onesignal WHERE device_id=%d and device_type=%d", array($device_id,$device_type)
      )
	);
	if(empty($super_driver_id)){
		if($queery_sql == "INSERT"){
			$query = $wpdb->prepare("INSERT INTO onesignal SET
                             device_id = %s,device_type = %s,user_id =%d",
                             array($device_id,$device_type,$user_id)
                          );
			$wpdb->query($query);
			file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal INSERT".$device_id, true),FILE_APPEND);
		}
	}
	else {
		if($queery_sql == "DELETE"){
		$wpdb->query($wpdb->prepare("DELETE FROM onesignal WHERE device_id = %d", $device_id));
		file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal Delete".$device_id, true),FILE_APPEND);
		}
	}
}

//Hock OneSignal with home page
add_action('geodir_sidebar_home_bottom_section', 'my_onesignal_check', 10);
add_action('wp_login', 'my_onesignal_check');
function my_onesignal_check(){
	$usrlogin = (is_user_logged_in())?1:0;
	$device = (wp_is_mobile())?"Mobile":"PC";
	file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal Start Device is ".$device." and login is".$usrlogin, true));
?>
<script>
console.log("Before OneSignal Start:"); 

var usrlogincheck = <?php echo $usrlogin ?>;
var usrID = <?php echo get_current_user_id()?>;
var usrDevice = "<?php echo $device?>";

OneSignal.push(function() {
	console.log("OneSignal Start!!:");
  OneSignal.isPushNotificationsEnabled(function(isEnabled) {
    file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal push-notice  ".$usrDevice." and login is".$usrlogincheck, true),FILE_APPEND);
	console.log("OneSignal Check isEnabled:"+isEnabled);
    if ((isEnabled)&&(usrlogincheck))
	{
      	OneSignal.getUserId(function(deviceId) {
			console.log("OneSignal User ID:", deviceId);
			jQuery.ajax({
				type: 'POST',
				dataType: 'json',
				url: ajaxurl,
				data: 'action=updateOnesignal&doing=INSERT&device_id='+deviceId+'&userid='+usrID+'&deviceType='+usrDevice,
				success: function(arrayPHP) 
				{

				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					console.log(textStatus);
				}
			});
		});
		console.log("Push notifications are enabled!");
	}
	
    else
	{
		OneSignal.getUserId(function(deviceId) {
			console.log("OneSignal User ID:", deviceId);
			jQuery.ajax({
				type: 'POST',
				dataType: 'json',
				url: ajaxurl,
				data: 'action=updateOnesignal&doing=DELETE&device_id='+deviceId+'&deviceType='+usrDevice,
				success: function(arrayPHP) 
				{

				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					console.log(textStatus);
				}
			});
		});
		console.log("Push notifications are Not enabled Yet!");
	}
	
  });
  // Occurs when the user's subscription changes to a new value.
  OneSignal.on('subscriptionChange', function (isSubscribed) {
    console.log("The user's subscription state is now:", isSubscribed);
	if(usrlogincheck){
		if(isSubscribed){
			OneSignal.getUserId(function(deviceId) {
				console.log("OneSignal User ID:", deviceId);
				jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: ajaxurl,
					data: 'action=updateOnesignal&doing=INSERT&device_id='+deviceId+'&userid='+usrID+'&deviceType='+usrDevice,
					success: function(arrayPHP) 
					{
	
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) {
						console.log(textStatus);
					}
				});
			});
			console.log("User login and click sub!");
		}
		else{
			OneSignal.getUserId(function(deviceId) {
			console.log("OneSignal User ID:", deviceId);
				jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: ajaxurl,
					data: 'action=updateOnesignal&doing=DELETE&device_id='+deviceId+'&deviceType='+usrDevice,
					success: function(arrayPHP) 
					{
		
					},
					error: function(XMLHttpRequest, textStatus, errorThrown) {
						console.log(textStatus);
					}
				});
			});
			console.log("User Login and unsub!");
		}
	}
  });
});
</script>
<?php

}

//AJAX FUNCTION
add_action('wp_ajax_updateusrnoti', 'updateusrnoti');
add_action('wp_ajax_nopriv_updateusrnoti', 'updateusrnoti');
function updateusrnoti(){
	$data = $_POST;
    $queery_sql = $data['doing']; 
    
	$device_id_bfr = $data['device_id'];
    $device_id = trim($device_id_bfr,'Optional(\\")');    
	$user_id = get_current_user_id();
	$device_type = $data['deviceType'];	
	if($user_id != 0)
	{
		updateOneSignaliOS($queery_sql,$device_id,$user_id,$device_type);
		file_put_contents( dirname(__FILE__).'/debug/iostest.log', var_export( "Check user login".$device_id."user login is ".$user_id."Doing is".$queery_sql, true));
	}
}

function updateOneSignaliOS($queery_sql,$device_id,$user_id,$device_type){
	global $wpdb;
	
	file_put_contents( dirname(__FILE__).'/debug/iostest.log', var_export( "Check user login".$device_id."user login is ".$user_id, true));
	//Check data about this device in DB
	$super_driver_id = $wpdb->get_var(
      $wpdb->prepare(
          "SELECT user_id  FROM onesignal WHERE device_id=%d and device_type=%d", array($device_id,$device_type)
      )
	);
	if(empty($super_driver_id)){
		if($queery_sql == "INSERT"){
			$query = $wpdb->prepare("INSERT INTO onesignal SET
                             device_id = %s,device_type = %s,user_id =%d",
                             array($device_id,$device_type,$user_id)
                          );
			$wpdb->query($query);
			file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal INSERT".$device_id, true),FILE_APPEND);
		}
	}
	else {
		if($queery_sql == "DELETE"){
		$wpdb->query($wpdb->prepare("DELETE FROM onesignal WHERE device_id = %d", $device_id));
		file_put_contents( dirname(__FILE__).'/debug/onesignal.log', var_export( "updateOnesignal Delete".$device_id, true),FILE_APPEND);
		}
	}
}


add_action('geodir_detail_before_main_content','restaurantName');
function restaurantName()
{
	global $post;
	$post_type = geodir_get_current_posttype();	
	$shop_id = geodir_get_post_meta(get_the_ID(),'geodir_shop_id',true);	
	$shop_title = get_the_title($shop_id);
	
	if ($post_type == "gd_product")
	{
        //echo "WPShout was here.".$post->ID;
        create_product_modal($post,$shop_id);
        echo '<li><a href="'.get_permalink( $shop_id ).'">'.get_the_title( $shop_id ).'</a></li>';
        
	}
}
add_action('whoop_detail_page_hide_map','addCartProductBtn',9);
function addCartProductBtn()
{
	global $post;
	$post_type = geodir_get_current_posttype();
	if ($post_type == "gd_product")
	{
		if($post->geodir_show_addcart){
            echo '<b>ราคา '.$post->geodir_price.'<sup>บาท</sup><b>';
			echo '<button type="button" style="color:white;" 
			data-toggle="modal" data-target="#product_'.$post->ID.'">+</button>';
		}
	}
}

add_action('geodir_detail_before_main_content','addOrderButton');
function addOrderButton()
{
global $post;
?>
	<!--  Bank   -->
	<div class="order-online-big-button">
	<?php 
	$check_button= $post->geodir_Button_enable;
	if($check_button){	
		echo "<span class='glf-button' data-glf-cuid=",$post->geodir_CUID," data-glf-ruid=",$post->geodir_RUID," data-glf-auto-open='false'>สั่งเลย</span><script src='https://www.foodbooking.com/widget/js/ewm2.js' defer async ></script>";
	}?>
	</div>
	
	<!-- Bank Add ORDER Big BUTTON   -->	
	<!-- Bank  -->
	<div class="order-online-small-button">
	<?php
	if($check_button){
	
	echo "<span class='glf-button glyphicon' data-glf-cuid=",$post->geodir_CUID," data-glf-ruid=",$post->geodir_RUID," data-glf-auto-open='false'></span><script src='https://www.foodbooking.com/widget/js/ewm2.js' defer async ></script>";
	
	}?>
	</div>
	<!-- Bank Add Shop Cart BUTTON on top  -->
	<?php
	if(wp_is_mobile())
	{
		if((strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false)||(strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false))
		{
			?>	
			<script>
			function goBack() {
				window.history.back();
			}
			</script>
<?php
		}
	}
}







?>
