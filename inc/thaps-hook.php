 <?php 
add_action( 'wp_ajax_thaps_ajax_get_search_value', 'thaps_ajax_get_search_value' );
add_action( 'wp_ajax_nopriv_thaps_ajax_get_search_value','thaps_ajax_get_search_value' );
/*************************/
// search result function
/*************************/
function thaps_ajax_get_search_value(){
        //setting value
        $limit =  esc_html(th_advance_product_search()->get_option( 'result_length' ));
        $no_reult_label =  esc_html(th_advance_product_search()->get_option( 'no_reult_label' ));
        $more_reult_label =  esc_html(th_advance_product_search()->get_option( 'more_reult_label' ));
        $enable_product_image =  esc_html(th_advance_product_search()->get_option( 'enable_product_image' ));
        $enable_product_price =  esc_html(th_advance_product_search()->get_option( 'enable_product_price' ));
        $enable_product_desc =  esc_html(th_advance_product_search()->get_option( 'enable_product_desc' ));
        $enable_product_sku =  esc_html(th_advance_product_search()->get_option( 'enable_product_sku' ));

        $show_category_in =  esc_html(th_advance_product_search()->get_option( 'show_category_in' ));

        /*********************/
        //fetch product result
        /*********************/
         if (isset($_REQUEST['match']) && $_REQUEST['match'] != ''){
              $match_ = sanitize_text_field($_REQUEST['match']);
              $results = new WP_Query(array(
              'post_type'     => 'product',
              'post_status'   => 'publish',
              'nopaging'      => true,
              'posts_per_page' => 100,
              's'             => $match_,
            ));

             $count = ( isset( $results->posts ) ? count( $results->posts ) : 0 );
             $items = array();
              
             
             // category show 
             if($show_category_in == true){
                $items['suggestions'][] = array(
                    'value'  => 'Category',
                    'type'   => 'heading',
                );
               $categories = thaps_ajax_getCategories( $match_, $limit );
               if(!empty($categories)){
               foreach ( $categories as $result ) {
                    $items['suggestions'][] = $result;
                }
                }else{
                    $items['suggestions'][] = array(
                   'value'  => $no_reult_label,
                   'type'   => 'no-result',
                    );   
                }
             
              }


             if (!empty($results->posts)){
              $items['suggestions'][] = array(
                    'value'  => 'Product',
                    'type'   => 'heading',
                );  
              foreach (array_slice($results->posts,0,$limit) as $result){
                $product = wc_get_product($result->ID);
                $r = array(
                  'value'   => $result->post_title,
                  'title'   => $result->post_title,
                  'id'      => $result->ID,
                  'url'     => get_permalink($result->ID), 
                  'type'    => 'product', 
                );
                if ( $enable_product_image == true) {
                        $r['imgsrc'] = wp_get_attachment_url($product->get_image_id());
                }
                if ( $enable_product_price == true) {
                        $r['price'] = $product->get_price_html();
                }
                if ( $enable_product_sku == true) {
                        $r['sku'] = $product->get_sku();
                }
                if ( $enable_product_desc == true) {
                        $r['desc'] = $product->get_short_description();
                }

                $items['suggestions'][] = $r;
              }

            

            // show more product
            if($limit < $count){
                 $moreproduct = array(
                    'id'    => 'more-result',
                    'value' => '',
                    'text'  => $more_reult_label,
                    'total' => $count,
                    'url'   => add_query_arg( array(
                    's'         => $match_,
                    'post_type' => 'product',
                ), home_url() ),
                    'type'  => 'more_products',
                );
                 $items['suggestions'][] = $moreproduct; 
              }
             

            }else{
                $items['suggestions'][] = array(
                   'value'  => $no_reult_label,
                   'type'   => 'no-result',
                );
            }
            echo json_encode($items);
            die();
         }

}

function thaps_ajax_getCategories( $keyword, $limit = 3 ){
        $results = array();
        $args = array(
            'taxonomy' => 'product_cat',
        );
        $productCategories = get_terms( 'product_cat', $args );
        $keywordUnslashed = wp_unslash( $keyword );
  
        $i = 0;
        foreach ( $productCategories as $cat ) {

            if ( $i < $limit ) {
                $catName = html_entity_decode( $cat->name );
                $pos = strpos( mb_strtolower( $catName ), mb_strtolower( $keywordUnslashed ) );
                if ( $pos !== false ) {
                    $results[$i] = array(
                        'term_id'     => $cat->term_id,
                        'taxonomy'    => 'product_cat',
                        'value'       => $catName,
                        'url'         => get_term_link( $cat, 'product_cat' ),
                        'type'        => 'taxonomy-cat',
                    );
                    $i++;
                }
            
            }
        
        }

        
        return $results;
}