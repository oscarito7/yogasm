<?php
/**
 * WordPress Importer class for managing the import process of a CSV file
 *
 * @package WordPress
 * @subpackage Importer
 */
if ( ! class_exists( 'WP_Importer' ) )
	return;

class WF_CpnImpExpCsv_Coupon_Import extends WP_Importer {

    var $id;
    var $file_url;
    var $delimiter;
    var $profile;
//	var $merge_empty_cells;
    // mappings from old information to new
    var $processed_posts = array();
    var $merge;
    // Results
    var $import_results  = array();

    /**
     * Constructor
     */
    public function __construct() {

        if (WC()->version < '2.7.0') {
            $this->log = new WC_Logger();
        } else {
            $this->log = wc_get_logger();
        }
        $this->import_page             = 'coupon_csv';
	$this->file_url_import_enabled = apply_filters( 'coupon_csv_coupon_file_url_import_enabled', true );
    }

    /**
     * Registered callback function for the WordPress Importer
     *
     * Manages the three separate stages of the CSV import process
     */
    public function dispatch() {
        global $woocommerce, $wpdb;
        $this->sku_checkbox = !empty($_POST['sku_checkbox']) || !empty($_GET['sku_checkbox']) ? 1 : 0;
        if ( ! empty( $_POST['delimiter'] ) ) {
            $this->delimiter = stripslashes( trim( $_POST['delimiter'] ) );
        }else if ( ! empty( $_GET['delimiter'] ) ) {
            $this->delimiter = stripslashes( trim( $_GET['delimiter'] ) );
        }
        if ( ! $this->delimiter )
            $this->delimiter = ',';
        if ( ! empty( $_POST['profile'] ) ) {
            $this->profile = sanitize_text_field( $_POST['profile'] ) ;
        }else if ( ! empty( $_GET['profile'] ) ) {
            $this->profile = sanitize_text_field( $_GET['profile'] );
        }
        if ( ! $this->profile )
            $this->profile = '';
//		if ( ! empty( $_POST['merge_empty_cells'] ) || ! empty( $_GET['merge_empty_cells'] ) ) {
//			$this->merge_empty_cells = 1;
//		} else{
//			$this->merge_empty_cells = 0;
//		}
        if (!empty($_POST['merge']) || !empty($_GET['merge'])) {
            $this->merge = 1;
        } else {
            $this->merge = 0;
        }
        $step = empty( $_GET['step'] ) ? 0 : absint($_GET['step']) ;
        switch ( $step ) {
            case 0 :
                $this->header();
                $this->greet();
                break;
            case 1 :
                $this->header();
                check_admin_referer( 'import-upload' );
                if(!empty($_GET['file_url']))
                    $this->file_url = esc_attr( $_GET['file_url'] );
                if(!empty($_GET['file_id']))
                    $this->id = absint($_GET['file_id']) ;
                if ( !empty($_GET['clearmapping']) || $this->handle_upload() ){
                    $this->import_options();
                }else{
                    //_e( 'Error with handle_upload!', 'wf_order_import_export' );
                    wp_redirect(wp_get_referer().'&wf_coupon_ie_msg=3');
                }
                exit;
                break;
            case 2 :
                $this->header();
                check_admin_referer( 'import-woocommerce' );
                $this->id = absint($_POST['import_id']) ;
                if ( $this->file_url_import_enabled )
                        $this->file_url = esc_attr( $_POST['import_url'] );
                if ( $this->id )
                    $file = get_attached_file( $this->id );
                else if ( $this->file_url_import_enabled )
                    $file =  $this->file_url;
                $file = str_replace( "\\", "/", $file );
                $tab = (isset($_GET['tab']) && !empty($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'coupon');
                if ( $file ) {
                    include_once(dirname(__FILE__) . '/../views/html-wf-common-header.php');
                ?>

                <div class="tool-box ordimpexp-bg-white ordimpexp-p-20p">
                    <table id="import-progress" class="widefat_importer widefat">
                        <thead>
                            <tr>
                                <th class="status">&nbsp;</th>
                                <th class="row"><?php _e( 'Row', 'wf_order_import_export' ); ?></th>
                                <th><?php _e( 'Coupon Id', 'wf_order_import_export' ); ?></th>
                                <th><?php _e( 'Coupon Name', 'wf_order_import_export' ); ?></th>
                                <th class="reason"><?php _e( 'Status Msg', 'wf_order_import_export' ); ?></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="importer-loading">
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                        <tbody></tbody>
                    </table>
                </div>
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        if ( ! window.console ) { window.console = function(){}; }
                        var processed_posts = [];
                        var i               = 1;
                        var done_count      = 0;

                        function import_rows( start_pos, end_pos ) {
                            var data = {
                                action: 	'coupon_csv_import_request',
                                file:       '<?php echo addslashes( $file ); ?>',
                                mapping:    '<?php echo json_encode( wc_clean($_POST['map_from']) ); ?>',
                                profile:    '<?php echo $this->profile; ?>',
                                eval_field: '<?php echo stripslashes(json_encode(wc_clean($_POST['eval_field']),JSON_HEX_APOS)) ?>',
                                delimiter:  '<?php echo $this->delimiter; ?>',
                                merge:      '<?php echo $this->merge; ?>',
                                sku_checkbox: '<?php echo $this->sku_checkbox; ?>',
                                //merge_empty_cells: '<?php //echo $this->merge_empty_cells; ?>',
                                start_pos:  start_pos,
                                end_pos:    end_pos,
                                wt_nonce:   '<?php echo wp_create_nonce(WF_CPN_IMP_EXP_ID) ?>'
                            };
                            data.eval_field = $.parseJSON(data.eval_field);
                            return $.ajax({
                                url:        '<?php echo add_query_arg( array( 'import_page' => $this->import_page, 'step' => '3', 'merge' => ! empty( $_GET['merge'] ) ? '1' : '0' ), admin_url( 'admin-ajax.php' ) ); ?>',
                                data:       data,
                                type:       'POST',
                                success:    function( response ) {
                                    if ( response ) {
                                        try {
                                            // Get the valid JSON only from the returned string
                                            if ( response.indexOf("<!--WC_START-->") >= 0 )
                                                response = response.split("<!--WC_START-->")[1]; // Strip off before after WC_START
                                            if ( response.indexOf("<!--WC_END-->") >= 0 )
                                                response = response.split("<!--WC_END-->")[0]; // Strip off anything after WC_END
                                            // Parse
                                            var results = $.parseJSON( response );
                                            if ( results.error ) {
                                                $('#import-progress tbody').append( '<tr id="row-' + i + '" class="error"><td class="status" colspan="5">' + results.error + '</td></tr>' );
                                                i++;
                                            } else if ( results.import_results && $( results.import_results ).size() > 0 ) {
                                                $.each( results.processed_posts, function( index, value ) {
                                                    processed_posts.push( value );
                                                });
                                                $( results.import_results ).each(function( index, row ) {
                                                    $('#import-progress tbody').append( '<tr id="row-' + i + '" class="' + row['status'] + '"><td><mark class="result" title="' + row['status'] + '">' + row['status'] + '</mark></td><td class="row">' + i + '</td><td>' + row['post_id'] + '</td><td>' + row['post_title'] + '</td><td class="reason">' + row['reason'] + '</td></tr>' );
                                                    i++;
                                                });
                                            }
                                        } catch(err) {}
                                    } else {
                                        $('#import-progress tbody').append( '<tr class="error"><td class="status" colspan="5">' + '<?php _e( 'AJAX Error', 'wf_order_import_export' ); ?>' + '</td></tr>' );
                                    }
                                    var w = $(window);
                                    var row = $( "#row-" + ( i - 1 ) );
                                    if ( row.length ) {
                                        w.scrollTop( row.offset().top - (w.height()/2) );
                                    }
                                    done_count++;
                                    $('body').trigger( 'coupon_csv_import_request_complete' );
                                }
                            });
                        }
                        var rows = [];

                        <?php
                        $limit = apply_filters( 'coupon_csv_import_limit_per_request', 10 );
                        $enc   = mb_detect_encoding( $file, 'UTF-8, ISO-8859-1', true );
                        if ( $enc )
                                setlocale( LC_ALL, 'en_US.' . $enc );
                        @ini_set( 'auto_detect_line_endings', true );

                        $count             = 0;
                        $previous_position = 0;
                        $position          = 0;
                        $import_count      = 0;

                        // Get CSV positions
                        if ( ( $handle = fopen( $file, "r" ) ) !== FALSE ) {
                            while ( ( $postmeta = fgetcsv( $handle, 0, $this->delimiter) ) !== FALSE ) {
                                $count++;
                                if ( $count >= $limit ) {
                                    $previous_position = $position;
                                    $position          = ftell( $handle );
                                    $count             = 0;
                                    $import_count      ++;
                                                    // Import rows between $previous_position $position
                                    ?>rows.push( [ <?php echo $previous_position; ?>, <?php echo $position; ?> ] ); <?php
                                }
                            }
                            // Remainder
                            if ( $count > 0 ) {
                                    ?>rows.push( [ <?php echo $position; ?>, '' ] ); <?php
                                    $import_count      ++;
                            }
                            fclose( $handle );
                        }
                        ?>

                        var data = rows.shift();
                        var regen_count = 0;
                        import_rows( data[0], data[1] );
                        $('body').on( 'coupon_csv_import_request_complete', function() {
                            if ( done_count == <?php echo $import_count; ?> ) {
                                import_done();
                            } else {
                                // Call next request
                                data = rows.shift();
                                import_rows( data[0], data[1] );
                            }
                        } );
                        function import_done() {
                                var data = {
                                        action: 'coupon_csv_import_request',
                                        file: '<?php echo $file; ?>',
                                        processed_posts: processed_posts,
                                        wt_nonce:   '<?php echo wp_create_nonce(WF_CPN_IMP_EXP_ID) ?>'
                                };

                                $.ajax({
                                        url: '<?php echo add_query_arg( array( 'import_page' => $this->import_page, 'step' => '4', 'merge' => ! empty( $_GET['merge'] ) ? 1 : 0 ), admin_url( 'admin-ajax.php' ) ); ?>',
                                        data:       data,
                                        type:       'POST',
                                        success:    function( response ) {
                                                console.log( response );
                                                $('#import-progress tbody').append( '<tr class="complete"><td colspan="5">' + response + '</td></tr>' );
                                                $('.importer-loading').hide();
                                        }
                                });
                        }
                    });
                </script>
                <?php
                } else {
                    echo '<p class="error">' . __( 'Error finding uploaded file!', 'wf_order_import_export' ) . '</p>';
                }
                break;
            case 3 :
                // Check access
                $nonce = (isset($_POST['wt_nonce']) ? sanitize_text_field($_POST['wt_nonce']) : '');
                if (!wp_verify_nonce($nonce,WF_CPN_IMP_EXP_ID) || !WF_Coupon_Import_Export_CSV::hf_user_permission()) {
                    wp_die(__('Access Denied', 'wf_order_import_export'));
                }
                $file      = stripslashes( $_POST['file'] ); // Validating given path is valid path, not a URL
                if (filter_var($file, FILTER_VALIDATE_URL)) {
                    die();
                }
                add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );
                if ( function_exists( 'gc_enable' ) )
                        gc_enable();
                @set_time_limit(0);
                @ob_flush();
                @flush();
                $wpdb->hide_errors();
                $file      = stripslashes( $_POST['file'] );
                $mapping   = json_decode( stripslashes( $_POST['mapping'] ), true );
                $profile   = isset( $_POST['profile'] ) ? wc_clean($_POST['profile']):'';
                $eval_field = wc_clean($_POST['eval_field']);
                $start_pos = isset( $_POST['start_pos'] ) ? absint( $_POST['start_pos'] ) : 0;
                $end_pos   = isset( $_POST['end_pos'] ) ? absint( $_POST['end_pos'] ) : '';

                if($profile!== '')
                {
                    $profile_array = get_option('wf_cpn_csv_imp_exp_mapping');
                    $profile_array[$profile] = array($mapping,$eval_field);
                    update_option('wf_cpn_csv_imp_exp_mapping', $profile_array);	
                }

                $position = $this->import_start( $file, $mapping, $start_pos, $end_pos, $eval_field );
                $this->import();
                $this->import_end();

                $results                    = array();
                $results['import_results']  = $this->import_results;
                $results['processed_posts'] = $this->processed_posts;

                echo "<!--WC_START-->";
                echo json_encode( $results );
                echo "<!--WC_END-->";
                exit;
                break;
            case 4 :                
                // Check access 
                $nonce = (isset($_POST['wt_nonce']) ? sanitize_text_field($_POST['wt_nonce']) : '');
                if (!wp_verify_nonce($nonce,WF_CPN_IMP_EXP_ID) || !WF_Coupon_Import_Export_CSV::hf_user_permission()) {
                    wp_die(__('Access Denied', 'wf_order_import_export'));
                }

                add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );

                if ( function_exists( 'gc_enable' ) )
                        gc_enable();

                @set_time_limit(0);
                @ob_flush();
                @flush();
                $wpdb->hide_errors();

                $file      = stripslashes( $_POST['file'] );
                $this->processed_posts = isset( $_POST['processed_posts']) ? array_map('intval',$_POST['processed_posts']) : array();

                _e( 'Step 1...', 'wf_order_import_export' ) . ' ';

                wp_defer_term_counting( true );
                wp_defer_comment_counting( true );

                _e( 'Step 2...', 'wf_order_import_export' ) . ' ';

                echo 'Step 3...' . ' '; // Easter egg
                _e( 'Finalizing...', 'wf_order_import_export' ) . ' ';


                // SUCCESS
                _e( 'Finished. Import complete.', 'wf_order_import_export' );
                                 
                $this->import_end();
                if(in_array(pathinfo($file, PATHINFO_EXTENSION),array('txt','csv'))){
                    unlink($file); // deleting temparary file from meadia library by path
                }
                exit;
                break;
        }
        $this->footer();
    }

    /**
     * format_data_from_csv
     */
    public function format_data_from_csv( $data, $enc ) {
        return ( $enc == 'UTF-8' ) ? $data : utf8_encode( $data );
    }

    /**
     * Display pre-import options
     */
    public function import_options(){
        $j = 0;
        if ( $this->id )
            $file = get_attached_file( $this->id );
        else if ( $this->file_url_import_enabled )
            $file =  $this->file_url;
        else
            return;
        // Set locale
        $enc = mb_detect_encoding( $file, 'UTF-8, ISO-8859-1', true );
        if ( $enc ) setlocale( LC_ALL, 'en_US.' . $enc );
        @ini_set( 'auto_detect_line_endings', true );
        // Get headers
        if ( ( $handle = fopen( $file, "r" ) ) !== FALSE ){
            $row = $raw_headers = array();
            $header = fgetcsv( $handle, 0, $this->delimiter );
            while ( ( $postmeta = fgetcsv( $handle, 0, $this->delimiter ) ) !== FALSE ){
                foreach ( $header as $key => $heading ){
                    if ( ! $heading ) continue;
                    $s_heading = strtolower( $heading );
                    $row[$s_heading] = ( isset( $postmeta[$key] ) ) ? $this->format_data_from_csv( $postmeta[$key], $enc ) : '';
                    $raw_headers[ $s_heading ] = $heading;
                }
                break;
            }
            fclose( $handle );
        }
        $mapping_from_db  = get_option( 'wf_cpn_csv_imp_exp_mapping');
        if( $this->profile!=='' && !empty($_GET['clearmapping'])){
            unset($mapping_from_db[$this->profile]);
            update_option('wf_cpn_csv_imp_exp_mapping', $mapping_from_db);	
            $this->profile = '';
        }
        if($this->profile !== '')
        $mapping_from_db = $mapping_from_db[$this->profile];
        $saved_mapping = null;
        $saved_evaluation = null;
        if($mapping_from_db && is_array($mapping_from_db) && count($mapping_from_db) == 2 && empty($_GET['clearmapping'])){
//          $reset_action     = 'admin.php?clearmapping=1&amp;profile='.$this->profile.'&amp;import=' . $this->import_page . '&amp;step=1&amp;merge=' . ( ! empty( $_GET['merge'] ) ? 1 : 0 ) . '&amp;file_url=' . $this->file_url . '&amp;delimiter=' . $this->delimiter . '&amp;merge_empty_cells=' . $this->merge_empty_cells . '&amp;sku_checkbox='.$this->sku_checkbox.'&amp;file_id=' . $this->id . '';
            $reset_action     = 'admin.php?clearmapping=1&amp;profile='.$this->profile.'&amp;import=' . $this->import_page . '&amp;step=1&amp;merge=' . $this->merge . '&amp;file_url=' . $this->file_url . '&amp;delimiter=' . $this->delimiter . '&amp;sku_checkbox='.$this->sku_checkbox.'&amp;file_id=' . $this->id . '';
            $reset_action = esc_attr(wp_nonce_url($reset_action, 'import-upload'));
            echo '<h3>' . __( 'Columns are pre-selected using the Mapping file: "<b style="color:gray">'.$this->profile.'</b>".  <a href="'.$reset_action.'"> Delete</a> this mapping file.', 'wf_order_import_export' ) . '</h3>';
            $saved_mapping = $mapping_from_db[0];
            $saved_evaluation = $mapping_from_db[1];
        }

        include( 'views-coupon/html-wf-import-options.php' );
    }

    /**
     * The main controller for the actual import stage.
     */
    public function import(){
        wp_suspend_cache_invalidation( true );
        $this->hf_coupon_log_data_change( 'coupon-csv-import', '---' );
        $this->hf_coupon_log_data_change( 'coupon-csv-import', __( 'Processing coupons.', 'wf_order_import_export' ) );
        foreach ( $this->parsed_data as $key => &$item ){
            //$coupon = $this->parser->parse_coupon( $item, $this->merge_empty_cells );
            $coupon = $this->parser->parse_coupon( $item);
            if ( ! is_wp_error( $coupon ) )
                $this->process_coupon( $coupon);
            else
                $this->add_import_result( 'failed', $coupon->get_error_message(), 'Not parsed', json_encode( $item ) );
            unset( $item, $coupon );
        }
        $this->hf_coupon_log_data_change( 'coupon-csv-import', __( 'Finished processing coupons.', 'wf_order_import_export' ) );
        wp_suspend_cache_invalidation( false );
    }

    /**
     * Parses the CSV file and prepares us for the task of processing parsed data
     *
     * @param string $file Path to the CSV file for importing
     */
    public function import_start( $file, $mapping, $start_pos, $end_pos, $eval_field ) {
        $memory    = size_format( (WC()->version < '2.7.0')?woocommerce_let_to_num( ini_get( 'memory_limit' ) ):wc_let_to_num( ini_get( 'memory_limit' ) )  );
        $wp_memory = size_format( (WC()->version < '2.7.0')? woocommerce_let_to_num( WP_MEMORY_LIMIT ) : wc_let_to_num( WP_MEMORY_LIMIT ) );
        $this->hf_coupon_log_data_change( 'coupon-csv-import', '---[ New Import ] PHP Memory: ' . $memory . ', WP Memory: ' . $wp_memory );
        $this->hf_coupon_log_data_change( 'coupon-csv-import', __( 'Parsing coupons CSV.', 'wf_order_import_export' ) );
        $this->parser = new WF_CSV_Parser_Coupon( 'shop_coupon' );
        list( $this->parsed_data, $this->raw_headers, $position ) = $this->parser->parse_data( $file, $this->delimiter, $mapping, $start_pos, $end_pos, $eval_field );
        $this->hf_coupon_log_data_change( 'coupon-csv-import', __( 'Finished parsing coupons CSV.', 'wf_order_import_export' ) );
        unset( $import_data );
        wp_defer_term_counting( true );
        wp_defer_comment_counting( true );
        return $position;
    }

    /**
     * Performs post-import cleanup of files and the cache
     */
    public function import_end() {
        do_action( 'import_end' );
    }

    /**
     * Handles the CSV upload and initial parsing of the file to prepare for
     * displaying author import options
     *
     * @return bool False if error uploading or invalid file, true otherwise
     */
    public function handle_upload() {
        if($this->handle_ftp()){
            return true;
        }
        if ( empty( $_POST['file_url'] ) ) {
            $file = wp_import_handle_upload();
            if ( isset( $file['error'] ) ) {
                echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wf_order_import_export' ) . '</strong><br />';
                echo esc_html( $file['error'] ) . '</p>';
                return false;
            }
            $this->id = (int) $file['id'];
            return true;
        } else {
            if ( file_exists( ABSPATH . $_POST['file_url'] ) ){
                $this->file_url = esc_attr( $_POST['file_url'] );
                return true;
            } else {
                echo '<p><strong>' . __( 'Sorry, there has been an error.', 'wf_order_import_export' ) . '</strong></p>';
                return false;
            }
        }
        return false;
    }

    public function coupon_exists( $title, $ID = '', $post_name = '' ){
        global $wpdb;
        // Post Title Check
        $post_title = stripslashes( sanitize_post_field( 'post_title', $title, 0, 'db' ) );
        $query = "SELECT ID FROM $wpdb->posts WHERE post_type = 'shop_coupon' AND post_status IN ( 'publish', 'private', 'draft', 'pending', 'future' )";
        $args = array();
        if ( ! empty ( $post_name ) ){
            $query .= ' AND post_name = %s';
            $args[] = $post_name;
        }
        if ( ! empty ( $args ) ) {
            $posts_that_exist = $wpdb->get_col( $wpdb->prepare( $query, $args ) );
            if ( $posts_that_exist ) {
                foreach( $posts_that_exist as $post_exists ) {
                    if ( $ID == $post_exists ) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Create new posts based on import information
     */
    public function process_coupon( $post) {
        $settings = get_option('woocommerce_' . wf_all_imp_exp_ID . '_settings', null);
        $enable_ftp_ie = isset($settings['enable_ftp_ie']) ? $settings['enable_ftp_ie'] : '';
        $sku_checkbox = !empty($_POST['sku_checkbox']) || !empty($_GET['sku_checkbox']) ? true : false;
        if($enable_ftp_ie){
            $sku_checkbox = isset($settings['sku_checkbox']) ? $settings['sku_checkbox'] : '';
        }

        require_once(dirname(__DIR__) . '/class-wt-ie-helper.php');
        $ie_helper_object = new WT_ie_helper();    
        $processing_coupon_id    = absint( $post['post_id'] );
        $processing_coupon       = get_post( $processing_coupon_id );
        $processing_coupon_title = $processing_coupon ? $processing_coupon->post_title : '';
        $processing_coupon_sku   = $processing_coupon ? $processing_coupon->sku : '';
        $merging                 = ! empty( $post['merging'] );

        if ( ! empty( $post['post_title'] ) ) {
                $processing_coupon_title = $post['post_title'];
        }
        $post['post_type'] = 'shop_coupon';

        if ( ! empty( $processing_coupon_id ) && isset( $this->processed_posts[ $processing_coupon_id ] ) ) {
            $this->add_import_result( 'skipped', __( 'Coupon already processed', 'wf_order_import_export' ), $processing_coupon_id, $processing_coupon_title );
            $this->hf_coupon_log_data_change( 'coupon-csv-import', __('> Coupon ID already processed. Skipping.', 'wf_order_import_export'), true );
            unset( $post );
            return;
        }

        if ( ! empty ( $post['post_status'] ) && $post['post_status'] == 'auto-draft' ) {
            $this->add_import_result( 'skipped', __( 'Skipping auto-draft', 'wf_order_import_export' ), $processing_coupon_id, $processing_coupon_title);
            $this->hf_coupon_log_data_change( 'coupon-csv-import', __('> Skipping auto-draft.', 'wf_order_import_export'), true );
            unset( $post );
            return;
        }
        $is_post_exist_in_db = get_post_type( $processing_coupon_id );
        if ( ! $merging ) {
            if ( $this->coupon_exists( $processing_coupon_title, $processing_coupon_id, $post['post_name'] ) ) {
                if(!$processing_coupon_id) { 
                    $usr_msg = 'Coupon with same title already exists.';
                }else{
                    $usr_msg = 'Coupon already exists.'; 
                }
                $this->add_import_result( 'skipped', __( $usr_msg, 'wf_order_import_export' ), $processing_coupon_id, $processing_coupon_title );
                $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __('> &#8220;%s&#8221;'.$usr_msg, 'wf_order_import_export'), esc_html($processing_coupon_title) ), true );
                unset( $post );
                return;
            }

            if ( $processing_coupon_id && is_string( get_post_status( $processing_coupon_id ) ) && ($is_post_exist_in_db == $post['post_type'] ) ) {
                $this->add_import_result( 'skipped', __( 'Coupon with same ID already exists.', 'wf_order_import_export' ), $processing_coupon_id, get_the_title( $processing_coupon_id ) );
                $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __('> &#8220;%s&#8221; ID already exists.', 'wf_order_import_export'), esc_html( $processing_coupon_id ) ), true );
                unset( $post );
                return;
            }

            if ( $processing_coupon_id && is_string( get_post_status( $processing_coupon_id ) ) && ($is_post_exist_in_db !== $post['post_type'] ) ) {
                $this->add_import_result( 'skipped', __( 'Importing coupon(ID) conflicts with an existing post.', 'wf_order_import_export' ), $processing_coupon_id, get_the_title( $processing_coupon_id ) );
                $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __('> &#8220;%s&#8221; ID already exists.', 'wf_order_import_export'), esc_html( $processing_coupon_id ) ), true );
                unset( $post );
                return;
            }
        }      
        if ( $merging && $processing_coupon_id && !empty($is_post_exist_in_db) && ($is_post_exist_in_db !== $post['post_type'] )){
            $this->add_import_result( 'skipped', __( 'Importing coupon(ID) conflicts with an existing post which is not a coupon.', 'wf_order_import_export' ), $processing_coupon_id, $processing_coupon_title );
            $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __('> &#8220;%s&#8221; is not a coupon.', 'wf_order_import_export'), esc_html($processing_coupon_id) ), true );
            unset( $post );
            return;
        }
        if ( $merging && !empty($is_post_exist_in_db) ){
            $post_id = $processing_coupon_id;
            $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __('> Merging coupon ID %s.', 'wf_order_import_export'), $post_id ), true );
            $postdata = array('ID' => $post_id);
//                if ( $this->merge_empty_cells )
//                {
//                    if ( isset( $post['post_content'] ) ) {
//                        $postdata['post_content'] = $post['post_content'];
//                    }
//                    if ( isset( $post['post_excerpt'] ) ) {
//                        $postdata['post_excerpt'] = $post['post_excerpt'];
//                    }
//
//                } else {
            if ( ! empty( $post['post_content'] ) ) {
                $postdata['post_content'] = $post['post_content'];
            }
            if ( ! empty( $post['post_excerpt'] ) ) {
                $postdata['post_excerpt'] = $post['post_excerpt'];
            }
//                }
            if ( ! empty( $post['post_title'] ) ) {
               $postdata['post_title'] = $post['post_title'];
            }
            if ( ! empty( $post['post_author'] ) ) {
                $postdata['post_author'] = absint( $post['post_author'] );
            }
            if ( ! empty( $post['post_date'] ) ) {
                $postdata['post_date'] = date("Y-m-d H:i:s", strtotime( $post['post_date'] ) );
            }
            if ( ! empty( $post['post_date_gmt'] ) ) {
                $postdata['post_date_gmt'] = date("Y-m-d H:i:s", strtotime( $post['post_date_gmt'] ) );
            }
            if ( ! empty( $post['post_name'] ) ) {
                $postdata['post_name'] = $post['post_name'];
            }
            if ( ! empty( $post['post_status'] ) ) {
                $postdata['post_status'] = $post['post_status'];
            }
            if ( sizeof( $postdata ) > 1 ) {
                $result = wp_update_post( $postdata );
                if ( ! $result ){
                    $this->add_import_result( 'failed', __( 'Failed to update coupon', 'wf_order_import_export' ), $post_id, $processing_coupon_title );
                    $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __('> Failed to update coupon %s', 'wf_order_import_export'), $post_id ), true );
                    unset( $post );
                    return;
                } else {
                    $this->hf_coupon_log_data_change( 'coupon-csv-import', __( '> Merged post data: ', 'wf_order_import_export' ) . print_r( $postdata, true ) );
                }
            }
        } else {
            $merging = FALSE;
            $post_parent = $post['post_parent'];
            $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __('> Inserting %s', 'wf_order_import_export'), esc_html( $processing_coupon_title ) ), true );
            $postdata = array(
                'import_id'      => $post['post_id'],
                'post_author'    => $post['post_author'] ? absint( $post['post_author'] ) : get_current_user_id(),
                'post_date'      => ( $post['post_date'] ) ? date( 'Y-m-d H:i:s', strtotime( $post['post_date'] )) : '',
                'post_date_gmt'  => ( $post['post_date_gmt'] ) ? date( 'Y-m-d H:i:s', strtotime( $post['post_date_gmt'] )) : '',
                'post_content'   => $post['post_excerpt'],
                'post_excerpt'   => $post['post_excerpt'],
                'post_title'     => $post['post_title'],
                'post_name'      => ( $post['post_name'] ) ? $post['post_name'] : sanitize_title( $post['post_title'] ),
                'post_status'    => ( $post['post_status'] ) ? $post['post_status'] : 'publish',
                'post_type'      => 'shop_coupon',
            );

            $post_id = wp_insert_post( $postdata, true );

            if ( is_wp_error( $post_id ) ) {
                $this->add_import_result( 'failed', __( 'Failed to import coupon', 'wf_order_import_export' ), $processing_coupon_id, $processing_coupon_title );
                $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __( 'Failed to import coupon &#8220;%s&#8221;', 'wf_order_import_export' ), esc_html($processing_coupon_title) ) );
                unset( $post );
                return;
            } else {
                $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __('> Inserted - coupon ID is %s.', 'wf_order_import_export'), $post_id ) );
            }
        }
        unset( $postdata );
        if ( empty( $processing_coupon_id ) ) {
            $processing_coupon_id = (int) $post_id;
        }
        $this->processed_posts[ intval( $processing_coupon_id ) ] = (int) $post_id;
        if ( ! empty( $post['postmeta'] ) && is_array( $post['postmeta'] ) ) {
            foreach ( $post['postmeta'] as $meta ) {
                $key = apply_filters( 'import_post_meta_key', $meta['key'] );
                if ( $key ) {
                    if($key == 'product_ids' && $sku_checkbox){
                        continue;                                                
                        //update_post_meta( $post_id, $key, explode(",",$meta['value'] ) );
                    }elseif($key == 'product_ids' && !$sku_checkbox){
                        $pro_ids = explode("|",$meta['value']);
                        $new_pro_ids = array();
                        foreach ($pro_ids as $id_val){
                            $product_exist = get_post_type($id_val);
                            if ($product_exist == 'product' || $product_exist == 'product_variation'){
                                $new_pro_ids[] = $id_val;
                            }
                        }
                        update_post_meta( $post_id, 'product_ids', implode(",",$new_pro_ids ) );
                    }elseif($key == 'product_SKUs' && !$sku_checkbox){
                        continue;
                    }elseif($key == 'exclude_product_ids' && $sku_checkbox){
                        continue;
                    }elseif ($key == 'exclude_product_ids' && !$sku_checkbox) {
                        $ex_prod_ids = explode("|",$meta['value']);
                        $new_ex_pro_ids = array();
                        foreach ($ex_prod_ids as $ex_id_val){
                            $ex_product_exist = get_post_type($ex_id_val);
                            if ($ex_product_exist == 'product' || $ex_product_exist == 'product_variation'){
                                $new_ex_pro_ids[] = $ex_id_val;
                            }
                        }
                        update_post_meta( $post_id, 'exclude_product_ids', implode(",",$new_ex_pro_ids ) );
                    }elseif($key == 'exclude_product_SKUs' && !$sku_checkbox){
                        continue;
                    }elseif($key == 'product_categories' || $key == 'exclude_product_categories'){
                        update_post_meta( $post_id, $key, explode(",", str_replace(' ', '', $meta['value']) ) );
                    }elseif($key == 'product_SKUs' && $sku_checkbox){
                        $prod_skus = explode("|",$meta['value'] );
                        $prod_id = array();
                        foreach ($prod_skus as $sku_val){
                            $prod_id[] = $ie_helper_object->xa_wc_get_product_id_by_sku($sku_val);
                        }
                        update_post_meta( $post_id, 'product_ids', implode(",",$prod_id ) );
                    }elseif($key == 'exclude_product_SKUs' && $sku_checkbox){
                        $ex_prod_skus = explode("|",$meta['value'] );
                        $ex_prod_id = array();
                        foreach ($ex_prod_skus as $ex_sku_val){
                            $ex_prod_id[] = $ie_helper_object->xa_wc_get_product_id_by_sku($ex_sku_val);
                        }
                        update_post_meta( $post_id, 'exclude_product_ids', implode(",",$ex_prod_id ) );
                    }elseif($key == 'customer_email'){ 
                        $data= explode(',', $meta['value']);
                        update_post_meta( $post_id, $key,( $data ) );
                    }else{
                        update_post_meta( $post_id, $key, maybe_unserialize( $meta['value'] ) );
                    }
                }
            }
            unset( $post['postmeta'] );
        }
        if ( $merging ){
            $this->add_import_result( 'merged', 'Coupon updated successfully', $post_id, $processing_coupon_title );
            $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __('> Finished merging post ID %s.', 'wf_order_import_export'), $post_id ) );
        } else {
            $this->add_import_result( 'imported', 'Coupon Import successful', $post_id, $processing_coupon_title );
            $this->hf_coupon_log_data_change( 'coupon-csv-import', sprintf( __('> Finished importing post ID %s.', 'wf_order_import_export'), $post_id ) );
        }
        unset( $post );
    }

    /**
     * Log a row's import status
     */
    protected function add_import_result( $status, $reason, $post_id = '', $post_title = '' ) {
        $this->import_results[] = array(
                'post_title' => $post_title,
                'post_id'    => $post_id,
                'status'     => $status,
                'reason'     => $reason
        );
    }
    
    private function handle_ftp(){
        $enable_ftp_ie         	= !empty( $_POST['enable_ftp_ie'] ) ? true : false;
        if($enable_ftp_ie == false){ 
            $settings_in_db = get_option( 'wf_coupon_tracking_importer_ftp', null );
            $settings_in_db['enable_ftp_ie'] = false;
            update_option( 'wf_coupon_tracking_importer_ftp', $settings_in_db );
            return false;
        }
        $ftp_server		= ! empty( $_POST['ftp_server'] ) ? sanitize_text_field($_POST['ftp_server']) : '';
        $ftp_server_path	= ! empty( $_POST['ftp_server_path'] ) ? sanitize_text_field($_POST['ftp_server_path']) : '';
        $ftp_user		= ! empty( $_POST['ftp_user'] ) ? wp_unslash($_POST['ftp_user']) : '';
        $ftp_password		= ! empty( $_POST['ftp_password'] ) ? wp_unslash($_POST['ftp_password']) : '';
        $ftp_port		= ! empty( $_POST['ftp_port'] ) ? absint($_POST['ftp_port']) : 21;
        $use_ftps         	= ! empty( $_POST['use_ftps'] ) ? true : false;
        $use_pasv         	= ! empty( $_POST['use_pasv'] ) ? true : false;

        $settings = array();
        $settings[ 'ftp_server' ]	= $ftp_server;
        $settings[ 'ftp_user' ]		= $ftp_user;
        $settings[ 'ftp_password' ]	= $ftp_password;
        $settings[ 'ftp_port' ]		= $ftp_port;
        $settings[ 'use_ftps' ]		= $use_ftps;
        $settings[ 'use_pasv' ]		= $use_pasv;
        $settings[ 'enable_ftp_ie' ]	= $enable_ftp_ie;
        $settings[ 'ftp_server_path' ]	= $ftp_server_path;
        
//		$local_file = 'wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import.csv';
        $wp_upload_dir = wp_upload_dir();
        $local_file = $wp_upload_dir['path'].'/cup-temp-import.csv';
        $server_file = $ftp_server_path;

        update_option( 'wf_coupon_tracking_importer_ftp', $settings );

        $error_message = "";
        $success = false;
        
        // if have SFTP Add-on for Import Export for WooCommerce 
        if (class_exists('class_wf_sftp_import_export')) {
            $sftp_import = new class_wf_sftp_import_export();
            if (!$sftp_import->connect($ftp_server, $ftp_user, $ftp_password, $ftp_port)) {
                $error_message = "Not able to connect to the server please check <b>sFTP Server Host / IP</b> and <b>Port number</b>. \n";

            }
            if (empty($server_file)) {
                $error_message = "Please Completely fill the sFTP Details. \n";
            } else {
                $file_contents = $sftp_import->get_contents($server_file);
                if (!empty($file_contents)) {
                    file_put_contents( $local_file, $file_contents);
                    $error_message = "";
                    $success = true;
                } else {
                    $error_message = "Failed to Download Specified file in sFTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>$local_file</b> .\n";

                }
            }
        } else {
            $ftp_conn = $use_ftps ? @ftp_ssl_connect($ftp_server, $ftp_port) : @ftp_connect($ftp_server, $ftp_port);

            if ($ftp_conn == false) {
                $error_message = "Not able to connect to the server please check <b>FTP Server Host / IP</b> and <b>Port number</b>. \n";
            }
            if (empty($error_message)) {
                if (@ftp_login($ftp_conn, $ftp_user, $ftp_password) == false) {
                    $error_message = "Connected to FTP Server.<br/>But, not able to login please check <b>FTP User Name</b> and <b>Password.</b>\n";
                }
            }
            if ($use_pasv)
                ftp_pasv($ftp_conn, TRUE);
            if (empty($error_message)) {
            if (@ftp_get($ftp_conn, $local_file, $server_file, FTP_BINARY)) {
                    $error_message = "";
                    $success = true;
                } else {
                    $error_message = "Failed to Download Specified file in FTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.\n";
                }
            }
            @ftp_close($ftp_conn);

        }
        
        if($success){
            $this->file_url = $local_file;
        }else{
            die(__($error_message,'wf_order_import_export'));
        }	
        return true;
    }

    // Display import page title
    public function header() {
        echo '<div><div class="icon32" id="icon-woocommerce-importer"><br></div>';
    }

    // Close div.wrap
    public function footer() {
        echo '</div>';
    }

    /**
     * Display introductory text and file upload form
     */
    public function greet() {
        $action     = 'admin.php?import=coupon_csv&amp;step=1';
        $bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
        $size       = size_format( $bytes );
        $upload_dir = wp_upload_dir();
        $ftp_settings = get_option( 'wf_coupon_tracking_importer_ftp');
        include( 'views-coupon/html-wf-import-greeting.php' );
    }

    /**
     * Added to http_request_timeout filter to force timeout at 60 seconds during import
     * @return int 60
     */
    public function bump_request_timeout( $val ) {
        return 60;
    }
    
    public function hf_coupon_log_data_change ($content = 'coupon-csv-import',$data='') {
        if (WC()->version < '2.7.0') {
            $this->log->add($content,$data);
        } else {
            $context = array( 'source' => $content );
            $this->log->log("debug", $data ,$context);
        }
    }
}