<?php
/**
 * WordPress Importer class for managing the import process of a CSV file
 *
 * @package WordPress
 * @subpackage Importer
 */
if (!class_exists('WP_Importer'))
    return;

class wf_subcription_orderImpExpCsv_Order_Import extends WP_Importer {
    var $id;
    var $file_url;
    var $delimiter;
    var $profile;
    var $merge_empty_cells;
    var $processed_posts = array();
    var $merged = 0;
    var $skipped = 0;
    var $imported = 0;
    var $errored = 0;
    var $link_wt_import_key = 0;
    var $link_using_sku = 0;
    // Results
    var $import_results = array();

    /**
     * Constructor
     */
    public function __construct() {
        if (WC()->version < '2.7.0') {
            $this->log = new WC_Logger();
        } else {
            $this->log = wc_get_logger();
        }
        $this->import_page = 'woocommerce_wf_subscription_order_csv';
        $this->file_url_import_enabled = apply_filters('woocommerce_csv_product_file_url_import_enabled', true);
    }

    /**
     * Function to write in the woocommerce log file
     */
    public function hf_log_data_change($content = 'hf-subscription-csv-import', $data = '') {
        if (WC()->version < '2.7.0') {
            $this->log->add($content, $data);
        } else {
            $context = array('source' => $content);
            $this->log->log("debug", $data, $context);
        }
    }

    public static $membership_plans = null;
    public static $all_virtual = true;
    public static $user_meta_fields = array(
        '_billing_first_name', // Billing Address Info
        '_billing_last_name',
        '_billing_company',
        '_billing_address_1',
        '_billing_address_2',
        '_billing_city',
        '_billing_state',
        '_billing_postcode',
        '_billing_country',
        '_billing_email',
        '_billing_phone',
        '_shipping_first_name', // Shipping Address Info
        '_shipping_last_name',
        '_shipping_company',
        '_shipping_address_1',
        '_shipping_address_2',
        '_shipping_city',
        '_shipping_state',
        '_shipping_postcode',
        '_shipping_country',
    );

    /**
     * Registered callback function for the WordPress Importer
     *
     * Manages the three separate stages of the CSV import process
     */
    public function dispatch() {
        global $woocommerce, $wpdb;
        if (!empty($_POST['delimiter'])) {
            $this->delimiter = stripslashes(trim($_POST['delimiter']));
        } else if (!empty($_GET['delimiter'])) {
            $this->delimiter = stripslashes(trim($_GET['delimiter']));
        }
        if (!$this->delimiter)
            $this->delimiter = ',';
        if(!empty($_POST['link_wt_import_key']) || !empty($_GET['link_wt_import_key'])){
            $this->link_wt_import_key = 1;
        } else {
            $this->link_wt_import_key = 0;
        }
        if(!empty($_POST['link_using_sku']) || !empty($_GET['link_using_sku'])){
            $this->link_using_sku = 1;
        } else {
            $this->link_using_sku = 0;
        }
        if (!empty($_POST['profile'])) {
            $this->profile = stripslashes(trim($_POST['profile']));
        } else if (!empty($_GET['profile'])) {
            $this->profile = stripslashes(trim($_GET['profile']));
        }
        if (!$this->profile)
            $this->profile = '';
        if (!empty($_POST['merge_empty_cells']) || !empty($_GET['merge_empty_cells'])) {
            $this->merge_empty_cells = 1;
        } else {
            $this->merge_empty_cells = 0;
        }
        $step = empty($_GET['step']) ? 0 : absint($_GET['step']) ;
        switch ($step) {
            case 0 :
                $this->header();
                $this->greet();
                break;
            case 1 :
                $this->header();
                check_admin_referer('import-upload');
                if (!empty($_GET['file_url']))
                    $this->file_url = esc_attr($_GET['file_url']);
                if (!empty($_GET['file_id']))
                    $this->id = absint($_GET['file_id']);
                if (!empty($_GET['clearmapping']) || $this->handle_upload())
                    $this->import_options();
                else
                    _e('Error with handle_upload!', 'wf_order_import_export');
                break;
            case 2 :
                $this->header();
                check_admin_referer('import-woocommerce');
                $this->id = absint($_POST['import_id']) ;
                if ($this->file_url_import_enabled)
                    $this->file_url = esc_attr($_POST['import_url']);
                if ($this->id)
                    $file = get_attached_file($this->id);
                else if ($this->file_url_import_enabled)
                    $file =  $this->file_url;
                $file = str_replace("\\", "/", $file);
                $tab = (isset($_GET['tab']) && !empty($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'subscription');
                if ($file) {
                    include_once(dirname(__FILE__) . '/../views/html-wf-common-header.php');
                    ?>
                    <div class="tool-box ordimpexp-bg-white ordimpexp-p-20p">
                        <table id="import-progress" class="widefat_importer widefat">
                            <thead>
                                <tr>
                                    <th class="status">&nbsp;</th>
                                    <th class="row"><?php _e('Row', 'wf_order_import_export'); ?></th>
                                    <th><?php _e('OrderID', 'wf_order_import_export'); ?></th>
                                    <th><?php _e('Order Status', 'wf_order_import_export'); ?></th>
                                    <th class="reason"><?php _e('Status Msg', 'wf_order_import_export'); ?></th>
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
                            if (! window.console) { window.console = function(){}; }
                            var processed_posts = [];
                            var i = 1;
                            var done_count = 0;
                            function import_rows(start_pos, end_pos) {
                                var data = {
                                    action:     'woocommerce_csv_subscription_order_import_request',
                                    file:       '<?php echo addslashes($file); ?>',
                                    mapping:    '<?php echo json_encode(wc_clean($_POST['map_from'])); ?>',
                                    profile:    '<?php echo $this->profile; ?>',
                                    eval_field: '<?php echo stripslashes(json_encode(wc_clean($_POST['eval_field']), JSON_HEX_APOS)) ?>',
                                    delimiter:  '<?php echo $this->delimiter; ?>',
                                    link_wt_import_key: '<?php echo $this->link_wt_import_key; ?>',
                                    link_using_sku: '<?php echo $this->link_using_sku ?>',
                                    merge_empty_cells: '<?php echo $this->merge_empty_cells; ?>',
                                    start_pos:  start_pos,
                                    end_pos:    end_pos,
                                    wt_nonce:   '<?php echo wp_create_nonce(wf_subscription_order_imp_exp_ID) ?>'
                                };
                                data.eval_field = $.parseJSON(data.eval_field);
                                return $.ajax({
                                    url:        '<?php echo add_query_arg(array('import_page' => $this->import_page, 'step' => '3', 'merge' => !empty($_GET['merge']) ? '1' : '0','link_wt_import_key' => $this->link_wt_import_key,'link_using_sku' => $this->link_using_sku), admin_url('admin-ajax.php')); ?>',
                                    data:       data,
                                    type:       'POST',
                                    success:    function(response) {
                                        if (response) {
                                            try {
                                                // Get the valid JSON only from the returned string
                                                if (response.indexOf("<!--WC_START-->") >= 0)
                                                    response = response.split("<!--WC_START-->")[1]; // Strip off before after WC_START 
                                                if (response.indexOf("<!--WC_END-->") >= 0)
                                                    response = response.split("<!--WC_END-->")[0]; // Strip off anything after WC_END
                                                // Parse
                                                var results = $.parseJSON(response);
                                                if (results.error) {
                                                    $('#import-progress tbody').append('<tr id="row-' + i + '" class="error"><td class="status" colspan="5">' + results.error + '</td></tr>');
                                                    i++;
                                                } else if (results.import_results && $(results.import_results).size() > 0) {                                                 
                                                    $.each(results.processed_posts, function(index, value) {
                                                        processed_posts.push(value);
                                                    });
                                                    $(results.import_results).each(function(index, row) {
                                                        $('#import-progress tbody').append('<tr id="row-' + i + '" class="' + row['status'] + '"><td><mark class="result" title="' + row['status'] + '">' + row['status'] + '</mark></td><td class="row">' + i + '</td><td>' + row['order_number'] + '</td><td>' + row['post_id'] + ' - ' + row['post_title'] + '</td><td class="reason">' + row['reason'] + '</td></tr>');
                                                        i++;
                                                    });
                                                }
                                            } catch (err) {}
                                        } else {
                                            $('#import-progress tbody').append('<tr class="error"><td class="status" colspan="5">' + '<?php _e('AJAX Error', 'wf_order_import_export'); ?>' + '</td></tr>');
                                        }
                                        var w = $(window);
                                        var row = $("#row-" + (i - 1));
                                        if (row.length) {
                                            w.scrollTop(row.offset().top - (w.height() / 2));
                                        }
                                        done_count++;
                                        $('body').trigger('woocommerce_csv_subscription_order_import_request_complete');
                                    }
                                });
                            }
                            var rows = [];
                            <?php
                            $limit = apply_filters('woocommerce_csv_import_limit_per_request', 10);
                            $enc = mb_detect_encoding($file, 'UTF-8, ISO-8859-1', true);
                            if ($enc)
                                setlocale(LC_ALL, 'en_US.' . $enc);
                            @ini_set('auto_detect_line_endings', true);
                            $count = 0;
                            $previous_position = 0;
                            $position = 0;
                            $import_count = 0;
                            // Get CSV positions
                            if (( $handle = fopen($file, "r") ) !== FALSE) {
                                while (( $postmeta = fgetcsv($handle, 0, $this->delimiter) ) !== FALSE) {
                                    $count++;
                                    if ($count >= $limit) {
                                        $previous_position = $position;
                                        $position = ftell($handle);
                                        $count = 0;
                                        $import_count ++;
                                        // Import rows between $previous_position $position
                                        ?>rows.push([ <?php echo $previous_position; ?>, <?php echo $position; ?> ]); <?php
                                    }
                                }
                                // Remainder
                                if ($count > 0) {
                                    ?>rows.push( [ <?php echo $position; ?>, '' ] ); <?php
                                    $import_count ++;
                                }
                                fclose($handle);
                            }
                            ?>
                            var data = rows.shift();
                            var regen_count = 0;
                            import_rows( data[0], data[1] );
                            $('body').on( 'woocommerce_csv_subscription_order_import_request_complete', function() {
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
                                    action: 'woocommerce_csv_subscription_order_import_request',
                                    file: '<?php echo $file; ?>',
                                    processed_posts: processed_posts,
                                    wt_nonce:   '<?php echo wp_create_nonce(wf_subscription_order_imp_exp_ID) ?>'
                                };
                                $.ajax({
                                    url: '<?php echo add_query_arg(array('import_page' => $this->import_page, 'step' => '4', 'merge' => !empty($_GET['merge']) ? 1 : 0,'link_wt_import_key' => !empty($_GET['link_wt_import_key']) ? '1' : '0','link_using_sku' => !empty($_GET['link_using_sku']) ? '1' : '0'), admin_url('admin-ajax.php')); ?>',
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
                    echo '<p class="error">' . __('Error finding uploaded file!', 'wf_order_import_export') . '</p>';
                }
                break;
            case 3 :
                // Check access
                $nonce = (isset($_POST['wt_nonce']) ? sanitize_text_field($_POST['wt_nonce']) : '');                
                if (!wp_verify_nonce($nonce,wf_subscription_order_imp_exp_ID) || !wf_subscription_order_import_export_CSV::hf_user_permission()) {
                    wp_die(__('Access Denied', 'wf_order_import_export'));
                }
                $file      = stripslashes( $_POST['file'] ); // Validating given path is valid path, not a URL
                if (filter_var($file, FILTER_VALIDATE_URL)) {
                    die();
                }
                add_filter('http_request_timeout', array($this, 'bump_request_timeout'));
                if (function_exists('gc_enable'))
                    gc_enable();
                @set_time_limit(0);
                @ob_flush();
                @flush();
                $wpdb->hide_errors();
                $file = stripslashes($_POST['file']);
                $mapping = json_decode(stripslashes(wc_clean($_POST['mapping'])), true);
                $profile = isset($_POST['profile']) ? wc_clean($_POST['profile']) : '';
                $eval_field = wc_clean($_POST['eval_field']);
                $start_pos = isset($_POST['start_pos']) ? absint($_POST['start_pos']) : 0;
                $end_pos = isset($_POST['end_pos']) ? absint($_POST['end_pos']) : '';
                if ($profile !== '') {
                    $profile_array = get_option('wf_subcription_order_csv_imp_exp_mapping');
                    $profile_array[$profile] = array($mapping, $eval_field);
                    update_option('wf_subcription_order_csv_imp_exp_mapping', $profile_array);
                }
                $position = $this->import_start($file, $mapping, $start_pos, $end_pos, $eval_field);
                $this->import();
                $this->import_end();
                $results = array();
                $results['import_results'] = $this->import_results;
                $results['processed_posts'] = $this->processed_posts;
                echo "<!--WC_START-->";
                echo json_encode($results);
                echo "<!--WC_END-->";
                exit;
                break;
            case 4 :
                // Check access
                $nonce = (isset($_POST['wt_nonce']) ? sanitize_text_field($_POST['wt_nonce']) : '');                
                if (!wp_verify_nonce($nonce,wf_subscription_order_imp_exp_ID) || !wf_subscription_order_import_export_CSV::hf_user_permission()) {
                    wp_die(__('Access Denied', 'wf_order_import_export'));
                }
                $file = stripslashes($_POST['file']);                
                add_filter('http_request_timeout', array($this, 'bump_request_timeout'));
                if (function_exists('gc_enable'))
                    gc_enable();
                @set_time_limit(0);
                @ob_flush();
                @flush();
                $wpdb->hide_errors();
                $this->processed_posts = isset($_POST['processed_posts']) ? array_map('intval',$_POST['processed_posts']) : array();

                _e('Step 1...', 'wf_order_import_export') . ' ';

                wp_defer_term_counting(true);
                wp_defer_comment_counting(true);

                _e('Step 2...', 'wf_order_import_export') . ' ';

                echo 'Step 3...' . ' '; // Easter egg

                _e('Finalizing...', 'wf_order_import_export') . ' ';

                // SUCCESS
                _e('Finished. Import complete.', 'wf_order_import_export');

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
    public function format_data_from_csv($data, $enc) {
        return ( $enc == 'UTF-8' ) ? $data : utf8_encode($data);
    }

    /**
     * Display pre-import options
     */
    public function import_options() {
        $j = 0;
        if ($this->id)
            $file = get_attached_file($this->id);
        else if ($this->file_url_import_enabled)
            $file =  $this->file_url;
        else
            return;
        // Set locale
        $enc = mb_detect_encoding($file, 'UTF-8, ISO-8859-1', true);
        if ($enc)
            setlocale(LC_ALL, 'en_US.' . $enc);
        @ini_set('auto_detect_line_endings', true);
        // Get headers
        if (( $handle = fopen($file, "r") ) !== FALSE) {
            $row = $raw_headers = array();
            $header = fgetcsv($handle, 0, $this->delimiter);
            while (( $postmeta = fgetcsv($handle, 0, $this->delimiter) ) !== FALSE) {
                foreach ($header as $key => $heading) {
                    if (!$heading)
                        continue;
                    $s_heading = $heading;
                    $row[$s_heading] = ( isset($postmeta[$key]) ) ? $this->format_data_from_csv($postmeta[$key], $enc) : '';
                    $raw_headers[$s_heading] = $heading;
                }
                break;
            }
            fclose($handle);
        }
        $mapping_from_db = get_option('wf_subcription_order_csv_imp_exp_mapping');
        if ($this->profile !== '' && !empty($_GET['clearmapping'])) {
            unset($mapping_from_db[$this->profile]);
            update_option('wf_subcription_order_csv_imp_exp_mapping', $mapping_from_db);
            $this->profile = '';
        }
        if ($this->profile !== '')
            $mapping_from_db = $mapping_from_db[$this->profile];
        $saved_mapping = null;
        $saved_evaluation = null;
        if ($mapping_from_db && is_array($mapping_from_db) && count($mapping_from_db) == 2 && empty($_GET['clearmapping'])) {
            $reset_action = 'admin.php?clearmapping=1&amp;profile=' . $this->profile . '&amp;import=' . $this->import_page . '&amp;step=1&amp;merge=' . (!empty($_GET['merge']) ? 1 : 0 ) . '&amp;file_url=' . $this->file_url . '&amp;delimiter=' . $this->delimiter . '&amp;link_wt_import_key=' . $this->link_wt_import_key . '&amp;link_using_sku=' . $this->link_using_sku . '&amp;merge_empty_cells=' . $this->merge_empty_cells . '&amp;file_id=' . $this->id . '';
            $reset_action = esc_attr(wp_nonce_url($reset_action, 'import-upload'));
            echo '<h3>' . __('Columns are pre-selected using the Mapping file: "<b style="color:gray">' . $this->profile . '</b>".  <a href="' . $reset_action . '"> Delete</a> this mapping file.', 'wf_order_import_export') . '</h3>';
            $saved_mapping = $mapping_from_db[0];
            $saved_evaluation = $mapping_from_db[1];
        }
        $merge = (!empty($_GET['merge']) && $_GET['merge']) ? 1 : 0;
        include( 'views-subscription/html-wf-import-options.php' );
    }

    /**
     * The main controller for the actual import stage.
     */
    public function import() {
        global $woocommerce, $wpdb;
        wp_suspend_cache_invalidation(true);
        $this->hf_log_data_change('hf-subscription-csv-import', '---');
        $this->hf_log_data_change('hf-subscription-csv-import', __('Processing orders.', 'wf_order_import_export'));
        $merging = 1;
        $record_offset = 0;
        foreach ($this->parsed_data as $key => &$item) {
            $order = $this->parser->parse_subscription_orders($item, $this->raw_headers, $merging, $record_offset);
            if (!is_wp_error($order)){
                if(class_exists('HF_Subscription')){
                    $this->process_subscription_orders($order['hf_shop_subscription']);;
                }
                else{
                    $this->process_subscription_orders($order['shop_subscription']);
                }
            }
            else
                $this->add_import_result('failed', $order->get_error_message(), 'Not parsed', json_encode($item), '-');
            unset($item, $order);
        }
        $this->hf_log_data_change('hf-subscription-csv-import', __('Finished processing Orders.', 'wf_order_import_export'));
        wp_suspend_cache_invalidation(false);
    }

    /**
     * Parses the CSV file and prepares us for the task of processing parsed data
     *
     * @param string $file Path to the CSV file for importing
     */
    public function import_start($file, $mapping, $start_pos, $end_pos, $eval_field) {
        $memory = size_format((WC()->version < '2.7.0') ? woocommerce_let_to_num(ini_get('memory_limit')) : wc_let_to_num(ini_get('memory_limit')) );
        $wp_memory = size_format((WC()->version < '2.7.0') ? woocommerce_let_to_num(WP_MEMORY_LIMIT) : wc_let_to_num(WP_MEMORY_LIMIT) );
        $this->hf_log_data_change('hf-subscription-csv-import', '---[ New Import ] PHP Memory: ' . $memory . ', WP Memory: ' . $wp_memory);
        $this->hf_log_data_change('hf-subscription-csv-import', __('Parsing subscription CSV.', 'wf_order_import_export'));
        if(class_exists('HF_Subscription')){
            $this->parser = new WF_CSV_Subscription_Parser('hf_shop_subscription');
        } else {
            $this->parser = new WF_CSV_Subscription_Parser('shop_subscription');
        }
        list( $this->parsed_data, $this->raw_headers, $position ) = $this->parser->parse_data($file, $this->delimiter, $mapping, $start_pos, $end_pos, $eval_field);
        $this->hf_log_data_change('hf-subscription-csv-import', __('Finished parsing subscriptionss CSV.', 'wf_order_import_export'));
        unset($import_data);
        wp_defer_term_counting(true);
        wp_defer_comment_counting(true);
        return $position;
    }

    /**
     * Performs post-import cleanup of files and the cache
     */
    public function import_end() {
        //wp_cache_flush(); Stops output in some hosting environments
        foreach (get_taxonomies() as $tax) {
            delete_option("{$tax}_children");
            _get_term_hierarchy($tax);
        }
        wp_defer_term_counting(false);
        wp_defer_comment_counting(false);
        do_action('import_end');
    }

    /**
     * Handles the CSV upload and initial parsing of the file to prepare for
     * displaying author import options
     *
     * @return bool False if error uploading or invalid file, true otherwise
     */
    public function handle_upload() {
        if ($this->handle_ftp()) {
            return true;
        }
        if (empty($_POST['file_url'])) {
            $file = wp_import_handle_upload();
            if (isset($file['error'])) {
                echo '<p><strong>' . __('Sorry, there has been an error.', 'wf_order_import_export') . '</strong><br />';
                echo esc_html($file['error']) . '</p>';
                return false;
            }
            $this->id = (int) $file['id'];
            return true;
        } else {
            if (file_exists(ABSPATH . $_POST['file_url'])) {
                $this->file_url = esc_attr($_POST['file_url']);
                return true;
            } else {
                echo '<p><strong>' . __('Sorry, there has been an error.', 'wf_order_import_export') . '</strong></p>';
                return false;
            }
        }
        return false;
    }

    public function subscription_order_exists($orderID) {
        global $wpdb;
        if(class_exists('HF_Subscription')){
            $args = 'hf_shop_subscription';
        } else {
            $args = 'shop_subscription';
        }
        $posts_are_exist = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status IN ( 'wc-pending-cancel','wc-expired','wc-switched','wc-cancelled','wc-on-hold','wc-active','wc-pending')", $args));
        if ($posts_are_exist) {
            foreach ($posts_are_exist as $exist_id) {
                $found = false;
                if ($exist_id == $orderID) {
                    $found = TRUE;
                }
                if ($found)
                    return TRUE;
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Create new posts based on import information
     */
    private function process_subscription_orders($data) {
        global $wpdb;
        $this->imported = $this->merged = 0;
        $merging = (!empty($_GET['merge'])) ? 1 : 0;
        $link_wt_import_key = (!empty($_POST['link_wt_import_key']) || !empty($_GET['link_wt_import_key'])) ? 1 : 0;
        $link_using_sku = (!empty($_POST['link_using_sku']) || !empty($_GET['link_using_sku'])) ? 1 : 0;
        $add_memberships = ( isset($_POST['add_memberships']) ) ? sanitize_text_field($_POST['add_memberships']) : FALSE;
        $this->hf_log_data_change('hf-subscription-csv-import', __('Process start..', 'wf_order_import_export'));
        $this->hf_log_data_change('hf-subscription-csv-import', __('Processing subscriptions...', 'wf_order_import_export'));
        $email_customer = false; // set this as settings for choosing weather to mail details for newly created customers.
        $user_id = $this->hf_check_customer($data, $email_customer);
        if (is_wp_error($user_id)) {
            $this->hf_log_data_change('hf-subscription-csv-import', sprintf(__($user_id->get_error_message(), 'wf_order_import_export')));
            $this->add_import_result('skipped', __($user_id->get_error_message(), 'wf_order_import_export'), $data['subscription_id'], $data['subscription_id'], $data['subscription_id']);
            $skipped++;
            unset($data);
            return;
        } elseif (empty($user_id)) {
            $this->hf_log_data_change('hf-subscription-csv-import', sprintf(__('An error occurred with the customer information provided.', 'wf_order_import_export')));
            $this->add_import_result('skipped', __('An error occurred with the customer information provided.', 'wf_order_import_export'), $data['subscription_id'], $data['subscription_id'], $data['subscription_id']);
            $skipped++;
            unset($data);
            return;
        }
        //check whether download permissions need to be granted
        $add_download_permissions = false;
        // Check if post exists when importing
        $new_added = false;
        $is_order_exist = $this->subscription_order_exists($data['subscription_id']);
        $subscription_id = $data['subscription_id'];
        if ($subscription_id && is_string(get_post_status($subscription_id)) && (get_post_type($subscription_id) !== 'shop_subscription') && (get_post_type($subscription_id) !== 'hf_shop_subscription')) {
            $usr_msg = 'Importing subscription(ID) conflicts with an existing post.';
            $this->add_import_result('skipped', __($usr_msg, 'wf_order_import_export'), $subscription_id, get_the_title($subscription_id));
            $this->hf_log_data_change('hf-subscription-csv-import', __('> &#8220;%s&#8221;' . $usr_msg, 'wf_order_import_export'), esc_html($subscription_id), true);
            unset($data);
            return;
        }
        if (!$merging && $is_order_exist) {
            $usr_msg = 'Subscription with same ID already exists.';
            $this->add_import_result('skipped', __($usr_msg, 'wf_order_import_export'), $subscription_id, $subscription_id, $subscription_id);
            $this->hf_log_data_change('hf-subscription-csv-import', sprintf(__('> &#8220;%s&#8221;' . $usr_msg, 'wf_order_import_export'), esc_html($subscription_id)), true);
            unset($data);
            return;
        } else {
            if ((!empty($data['post_parent'])) && $link_wt_import_key) { //Check whether post_parent (Parent order ID) is an order or not
                $data['post_parent'] = self::wt_get_order_with_import_key($data['post_parent']);
            }else{ //Check whether post_parent (Parent order ID) is an order or not
                $temp_parent_order_exist = wc_get_order($data['post_parent']);
                $data['post_parent'] = ( $temp_parent_order_exist && $temp_parent_order_exist->get_type() == 'shop_order' ) ? $data['post_parent'] : '';
            }

            if ($is_order_exist) {   //Execute this when subscription already exist
                if(class_exists('HF_Subscription')){
                    $subscription = $this->hf_create_subscription(array(
                    'ID' => $data['subscription_id'],
                    'customer_id' => $user_id,
                    'order_id' => $data['post_parent'], //If order id is 0 it won't affect the existing parent order for particular subscription
                    'import_id' => $data['subscription_id'], //Suggest import to keep the given ID
                    'start_date' => $data['dates_to_update']['start'],
                    'status' => $data['subscription_status'],
                    'billing_interval' => (!empty($data['billing_interval']) ) ? $data['billing_interval'] : 1,
                    'billing_period' => (!empty($data['billing_period']) ) ? $data['billing_period'] : '',
                    'created_via' => 'importer',
                    'customer_note' => (!empty($data['customer_note']) ) ? $data['customer_note'] : '',
                    'currency' => (!empty($data['order_currency']) ) ? $data['order_currency'] : '',
                        ), TRUE
                    );
                }else{
                    $subscription = $this->hf_create_subscription(array(
                        'ID' => $data['subscription_id'],
                        'customer_id' => $user_id,
                        'order_id' => $data['post_parent'], //If order id is 0 it won't affect the existing parent order for particular subscription
                        'import_id' => $data['subscription_id'], //Suggest import to keep the given ID
                        'start_date' => $data['dates_to_update']['start'],
                        'status' => $data['subscription_status'], //OCSEIPFW-217
                        'billing_interval' => (!empty($data['billing_interval']) ) ? $data['billing_interval'] : 1,
                        'billing_period' => (!empty($data['billing_period']) ) ? $data['billing_period'] : '',
                        'created_via' => 'importer',
                        'customer_note' => (!empty($data['customer_note']) ) ? $data['customer_note'] : '',
                        'currency' => (!empty($data['order_currency']) ) ? $data['order_currency'] : '',
                    ), TRUE);
                }
                $new_added = false;
                if (is_wp_error($subscription)) {
                    $this->errored++;
                    $new_added = false;
                    $this->add_import_result('skipped', __('Error inserting', 'wf_order_import_export'), $data['subscription_id'], $data['subscription_id'], $data['subscription_id']);
                    $this->hf_log_data_change('hf-subscription-csv-import', sprintf(__('> Error inserting %s: %s', 'wf_order_import_export'), $post['order_number'], $subscription->get_error_message()), true);
                    unset($data);
                    return;
                }
            } else {
                if(class_exists('HF_Subscription')){
                    $subscription = $this->hf_create_subscription(array(
                    'customer_id' => $user_id,
                    'order_id' => $data['post_parent'],
                    'import_id' => $data['subscription_id'], //Suggest import to keep the given ID
                    'start_date' => $data['dates_to_update']['start'],
                    'status' => $data['subscription_status'],
                    'billing_interval' => (!empty($data['billing_interval']) ) ? $data['billing_interval'] : 1,
                    'billing_period' => (!empty($data['billing_period']) ) ? $data['billing_period'] : '',
                    'created_via' => 'importer',
                    'customer_note' => (!empty($data['customer_note']) ) ? $data['customer_note'] : '',
                    'currency' => (!empty($data['order_currency']) ) ? $data['order_currency'] : '',
                        )
                    );
                }else{
                    $subscription = $this->hf_create_subscription(array(
                        'customer_id' => $user_id,
                        'order_id' => $data['post_parent'],
                        'import_id' => $data['subscription_id'], //Suggest import to keep the given ID
                        'start_date' => $data['dates_to_update']['start'],
                        'billing_interval' => (!empty($data['billing_interval']) ) ? $data['billing_interval'] : 1,
                        'billing_period' => (!empty($data['billing_period']) ) ? $data['billing_period'] : '',
                        'created_via' => 'importer',
                        'customer_note' => (!empty($data['customer_note']) ) ? $data['customer_note'] : '',
                        'currency' => (!empty($data['order_currency']) ) ? $data['order_currency'] : '',
                            )
                    );
                }
                $new_added = true;
                if (is_wp_error($subscription)) {
                    $this->errored++;
                    $new_added = false;

                    $this->add_import_result('skipped', __('Error inserting', 'wf_order_import_export'), $data['subscription_id'], $data['subscription_id'], $data['subscription_id']);
                    $this->hf_log_data_change('hf-subscription-csv-import', sprintf(__($subscription->get_error_message(), 'wf_order_import_export'), esc_html($data['subscription_id'])), true);
                    unset($data);
                    return;
                }
            }
        }
        
        if (!empty($data['renewal_orders'])) {
            $renewal_orders = explode('|', $data['renewal_orders']);
            if($link_wt_import_key){
                foreach ($renewal_orders as $order_id) {
                    $current_order_ids[] = self::wt_get_order_with_import_key($order_id);                
                }
            } else {
                foreach ($renewal_orders as $order_id){
                    $order = WC()->order_factory->get_order( $order_id );
                    if(is_object($order)){
                        $current_order_ids[] = $order_id;
                    }
                }
            }
            $current_order_ids = array_filter($current_order_ids);
            if(!empty($current_order_ids) && !class_exists('HF_Subscription')){
                update_option('_transient_wcs-related-orders-to-'.( (WC()->version >= '2.7.0') ? $subscription->get_id() : $subscription->id), $current_order_ids);
                foreach ($current_order_ids as $id){
                    update_post_meta($id, '_subscription_renewal', (WC()->version >= '2.7.0') ? $subscription->get_id() : $subscription->id);
                }
            } else {
                foreach ($current_order_ids as $id){
                    update_post_meta($id, '_subscription_renewal', (WC()->version >= '2.7.0') ? $subscription->get_id() : $subscription->id);
                }
            }
        }
        
        foreach ($data['post_meta'] as $meta_data) {
            switch ($meta_data['key']){
                case '_coupon_items':
                    break;
                case '_download_permissions':
                    $add_download_permissions = TRUE;
                    $data['download_permissions'] = $meta_data['value'];
                    update_post_meta(( (WC()->version >= '2.7.0') ? $subscription->get_id() : $subscription->id),'_download_permissions_granted' , $meta_data['value']);
                    break;
                default:
                    update_post_meta(( (WC()->version >= '2.7.0') ? $subscription->get_id() : $subscription->id), $meta_data['key'], $meta_data['value']);
            }
        }
        // Grant downloadalbe product permissions
//        if ($add_download_permissions) {
//            WCS_Download_Handler::grant_download_permissions($subscription);
//        } 
        
        try {
            $subscription->update_dates($data['dates_to_update']);
            if(!class_exists('HF_Subscription')){
                $subscription->update_status($data['subscription_status']);
            }
        } catch (Exception $e) {
            $this->add_import_result('skipped', __($e->getMessage(), 'wf_order_import_export'), $data['subscription_id'], $data['subscription_id'], $data['subscription_id']);
            $this->hf_log_data_change('hf-subscription-csv-import', sprintf(__($e->getMessage(), 'wf_order_import_export'), esc_html($data['subscription_id'])), true);
            unset($data);
            return;
        }
        
        
        $result['items'] = isset($result['items']) ? $result['items'] : '';
        if (!empty($data['order_items'])) {
            if ($merging && $is_order_exist) {
                $wpdb->query($wpdb->prepare("DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON itemmeta.order_item_id = items.order_item_id WHERE items.order_id = %d and items.order_item_type = 'line_item'", $subscription_id));
            }
            if (is_numeric($data['order_items'])) {
                $product_id = absint($data['order_items']);
                $result['items'] = self::add_product($data, $subscription, array('product_id' => $product_id),$link_using_sku);
                if ($add_memberships) {
                    self::maybe_add_memberships($user_id, ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id), $product_id);
                }
            } else {
                foreach ($data['order_items'] as $order_item) {
                    $result['items'] .= self::add_product($data, $subscription, $order_item,$link_using_sku) . '<br/>';

                    if ($add_memberships) {
                        self::maybe_add_memberships($user_id, ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id), $item_data['product_id']);
                    }
                }
            }
        }
        
        if(!empty($data['shipping_method'])){
            if ($merging && $is_order_exist) {
                $wpdb->query($wpdb->prepare("DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON itemmeta.order_item_id = items.order_item_id WHERE items.order_id = %d and items.order_item_type = 'shipping'", $subscription_id));
            }
            $shipping_item = explode('|', $data['shipping_method']);
            $method_id = array_shift($shipping_item);
            $method_id = substr($method_id, strpos($method_id, ":") + 1);
            $method_title = array_shift($shipping_item);
            $method_title = substr($method_title, strpos($method_title, ":") + 1);
            $total = array_shift($shipping_item);
            $total = substr($total, strpos($total, ":") + 1);
            $shipping_order_item = array(
                'order_item_name' => ($method_title) ? $method_title : $method_id,
                'order_item_type' => 'shipping',
            );

            $shipping_order_item_id = wc_add_order_item((WC()->version >= '2.7.0') ? $subscription->get_id() : $subscription->id, $shipping_order_item);

            if ($shipping_order_item_id) {
                wc_add_order_item_meta($shipping_order_item_id, 'method_id', $method_id);
                wc_add_order_item_meta($shipping_order_item_id, 'cost', $total);
            }
        }
        
        if(!empty($data['shipping_items']) && !empty($data['shipping_method'])){
            foreach ($data['shipping_items'] as $shipping_item){
                if ($shipping_order_item_id) {
                    wc_add_order_item_meta($shipping_order_item_id,$shipping_item['item'], $shipping_item['value']);
                }
                else {
                    $shipping_order_item_id = wc_add_order_item((WC()->version >= '2.7.0') ? $subscription->get_id() : $subscription->id, $shipping_order_item);
                    wc_add_order_item_meta($shipping_order_item_id,$shipping_item['item'], $shipping_item['value']);
                }
            }
        }
        
        if(!empty($data['fee_items'])){
            if ($merging && $is_order_exist) {
                $fee_str = 'fee';
                $wpdb->query($wpdb->prepare("DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id and items.order_id = %d and items.order_item_type = %s", $subscription_id, $fee_str));
            }
            $fee_items = explode(';', $data['fee_items']);
            foreach ($fee_items as $item){
                $fee_item = explode('|', $item);
                $name = array_shift($fee_item);
                $name = substr($name, strpos($name, ":") + 1);
                $total = array_shift($fee_item);
                $total = substr($total, strpos($total, ":") + 1);
                $tax = array_shift($fee_item);
                $tax = substr($tax, strpos($tax, ":") + 1);
                $tax_class = array_shift($fee_item);
                $tax_class = substr($tax_class, strpos($tax_class, ":") + 1);
                $fee_order_item = array(
                    'order_item_name' => $name ? $name : '',
                    'order_item_type' => 'fee',
                );
                $fee_order_item_id = wc_add_order_item((WC()->version >= '2.7.0') ? $subscription->get_id() : $subscription->id, $fee_order_item);
                if($fee_order_item_id){
                    wc_add_order_item_meta($fee_order_item_id, '_line_total', $total);
                    wc_add_order_item_meta($fee_order_item_id, '_line_tax', $tax);
                    wc_add_order_item_meta($fee_order_item_id, '_tax_class', $tax_class);
                }
            }
        }
        
        $chosen_tax_rate_id = 0;
        if (!empty($data['tax_items'])) {
            if ($merging && $is_order_exist) {
                $tax_str = 'tax';
                $wpdb->query($wpdb->prepare("DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id and items.order_id = %d and items.order_item_type = %s", $subscription_id, $tax_str));
            }
            foreach ($data['tax_items'] as $tax_item) {
                $tax_order_item = array(
                    'order_item_name' => $tax_item['title'],
                    'order_item_type' => "tax",
                );
                $tax_order_item_id = wc_add_order_item((WC()->version >= '2.7.0') ? $subscription->get_id() : $subscription->id, $tax_order_item);
                if ($tax_order_item_id) {
                    wc_add_order_item_meta($tax_order_item_id, 'rate_id', $tax_item['rate_id']);
                    wc_add_order_item_meta($tax_order_item_id, 'label', $tax_item['label']);
                    wc_add_order_item_meta($tax_order_item_id, 'compound', $tax_item['compound']);
                    wc_add_order_item_meta($tax_order_item_id, 'tax_amount', $tax_item['tax_amount']);
                    wc_add_order_item_meta($tax_order_item_id, 'shipping_tax_amount', $tax_item['shipping_tax_amount']);
                }
            }
            //$chosen_tax_rate_id = self::add_taxes($subscription, $data);
        }
         
        if (!empty($data['coupon_items'])) {
            if ($merging && $is_order_exist) {
                $applied_coupons = $subscription->get_used_coupons();
                if (!empty($applied_coupons)) {
                    foreach ($applied_coupons as $coupon) {
                        $subscription->remove_coupon($coupon);
                    }
                }
            }
            self::add_coupons($subscription, $data);
        }
        
        if (!empty($data['order_notes'])) {
            add_filter('woocommerce_email_enabled_customer_note', '__return_false');
            if ($merging && $is_order_exist) {
                $wpdb->query($wpdb->prepare("DELETE comments,meta FROM {$wpdb->prefix}comments comments LEFT JOIN {$wpdb->prefix}commentmeta meta ON comments.comment_ID = meta.comment_id WHERE comments.comment_post_ID = %d",$subscription_id));
            }
            $order_notes = explode(';', $data['order_notes']);

            foreach ($order_notes as $order_note) {
                $subscription->add_order_note($order_note);
            }
        }
        
        // only show the following warnings on the import when the subscription requires shipping
        if (!self::$all_virtual) {
            if (!empty($missing_shipping_addresses)) {
                $result['warning'][] = esc_html__('The following shipping address fields have been left empty: ' . rtrim(implode(', ', $missing_shipping_addresses), ',') . '. ', 'wf_order_import_export');
            }
            if (!empty($missing_billing_addresses)) {
                $result['warning'][] = esc_html__('The following billing address fields have been left empty: ' . rtrim(implode(', ', $missing_billing_addresses), ',') . '. ', 'wf_order_import_export');
            }
            if (empty($shipping_method)) {
                $result['warning'][] = esc_html__('Shipping method and title for the subscription have been left as empty. ', 'wf_order_import_export');
            }
        }
        if (( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id)) {
            $this->processed_posts[( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id)] = ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id);
            $data['subscription_id'] = ( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id);
        }
        if (!empty($data['subscription_id'])) {
            $this->processed_posts[$data['subscription_id']] = $data['subscription_id'];
        }
        if ($merging && !$new_added)
            $out_msg = 'Subscription updated successfully';
        else
            $out_msg = 'Subscription Imported Successfully.';
        $this->add_import_result('imported', __($out_msg, 'wf_order_import_export'), $data['subscription_id'], $result['items'], $data['subscription_id']);
        $this->hf_log_data_change('hf-subscription-csv-import', sprintf(__('> &#8220;%s&#8221;' . $out_msg, 'wf_order_import_export'), esc_html($data['subscription_id'])), true);
        $this->imported++;
        $this->hf_log_data_change('hf-subscription-csv-import', sprintf(__('> Finished importing order %s', 'wf_order_import_export'), $data['subscription_id']));
        $this->hf_log_data_change('hf-subscription-csv-import', __('Finished processing orders.', 'wf_order_import_export'));
        unset($data);
    }

    /**
     * Log a row's import status
     */
    protected function add_import_result($status, $reason, $post_id = '', $post_title = '', $order_number = '') {
        $this->import_results[] = array(
            'post_title' => $post_title,
            'post_id' => $post_id,
            'order_number' => $order_number,
            'status' => $status,
            'reason' => $reason
        );
    }

    /**
     * Decide what the maximum file size for downloaded attachments is.
     * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
     *
     * @return int Maximum attachment file size to import
     */
    public function max_attachment_size() {
        return apply_filters('import_attachment_size_limit', 0);
    }

    //handle FTP section
    private function handle_ftp() {
        $enable_ftp_ie = !empty($_POST['enable_ftp_ie']) ? true : false;
        if ($enable_ftp_ie == false)
            return false;
        $ftp_server = !empty($_POST['ftp_server']) ? sanitize_text_field($_POST['ftp_server']) : '';
        $ftp_server_path = !empty($_POST['ftp_server_path']) ? sanitize_text_field($_POST['ftp_server_path']) : '';
        $ftp_user = !empty($_POST['ftp_user']) ? wp_unslash($_POST['ftp_user']) : '';
        $ftp_password = !empty($_POST['ftp_password']) ? wp_unslash($_POST['ftp_password']) : '';
        $ftp_port = !empty($_POST['ftp_port']) ? absint($_POST['ftp_port']) : 21;
        $use_ftps = !empty($_POST['use_ftps']) ? true : false;
        $use_pasv = !empty($_POST['use_pasv']) ? true : false;
        $settings = array();
        $settings['ftp_server'] = $ftp_server;
        $settings['ftp_user'] = $ftp_user;
        $settings['ftp_password'] = $ftp_password;
        $settings['ftp_port'] = $ftp_port;
        $settings['use_ftps'] = $use_ftps;
        $settings['use_pasv'] = $use_pasv;
        $settings['enable_ftp_ie'] = $enable_ftp_ie;
        $settings['ftp_server_path'] = $ftp_server_path;
//        $local_file = 'wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import.csv';
        $wp_upload_dir = wp_upload_dir();
        $local_file = $wp_upload_dir['path'].'/sub-temp-import.csv';
        $server_file = $ftp_server_path;
        update_option('hf_subscription_order_importer_ftp', $settings);
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

        if ($success) {
            $this->file_url = $local_file;
        } else {
            die(__($error_message,'wf_order_import_export'));
        }
        return true;
    }

    // Display import page title
    public function header() {
        echo '<div><div class="icon32" id="icon-woocommerce-importer"><br></div>';
        echo '<h2>' . ( empty($_GET['merge']) ? __('Import', 'wf_order_import_export') : __('Merge Orders', 'wf_order_import_export') ) . '</h2>';
    }

    // Close div.wrap
    public function footer() {
        echo '</div>';
    }

    /**
     * Display introductory text and file upload form
     */
    public function greet() {
        $action = 'admin.php?import=woocommerce_wf_subscription_order_csv&amp;step=1&amp;merge=' . (!empty($_GET['merge']) ? 1 : 0 ). '&amp;link_wt_import_key=' . $this->link_wt_import_key. '&amp;link_using_sku=' . $this->link_using_sku;
        $bytes = apply_filters('import_upload_size_limit', wp_max_upload_size());
        $size = size_format($bytes);
        $upload_dir = wp_upload_dir();
        $ftp_settings = get_option('hf_subscription_order_importer_ftp');
        include( 'views-subscription/html-wf-import-greeting.php' );
    }

    /**
     * Added to http_request_timeout filter to force timeout at 60 seconds during import
     * @return int 60
     */
    public function bump_request_timeout($val) {
        return 60;
    }

    public function hf_check_customer($data, $email_customer = false) {
        $customer_email = (!empty($data['customer_email']) ) ? $data['customer_email'] : '';
        $username = (!empty($data['customer_username']) ) ? $data['customer_username'] : '';
        $customer_id = (!empty($data['customer_id']) ) ? $data['customer_id'] : '';
        if (!empty($data['_customer_password'])) {
            $password = $data['_customer_password'];
            $password_generated = false;
        } else {
            $password = wp_generate_password(12, true);
            $password_generated = true;
        }
        $found_customer = false;
        if (!empty($customer_email)) {
            if (is_email($customer_email) && false !== email_exists($customer_email)) {
                $found_customer = email_exists($customer_email);
            } elseif (!empty($username) && false !== username_exists($username)) {
                $found_customer = username_exists($username);
            } elseif (is_email($customer_email)) {
                // Not in test mode, create a user account for this email
                if (empty($username)) {
                    $maybe_username = explode('@', $customer_email);
                    $maybe_username = sanitize_user($maybe_username[0]);
                    $counter = 1;
                    $username = $maybe_username;
                    while (username_exists($username)) {
                        $username = $maybe_username . $counter;
                        $counter++;
                    }
                }
                $found_customer = wp_create_user($username, $password, $customer_email);
                if (!is_wp_error($found_customer)) {
                    // update user meta data
                    foreach (self::$user_meta_fields as $key) {
                        switch ($key) {
                            case '_billing_email':
                                // user billing email if set in csv otherwise use the user's account email
                                $meta_value = (!empty($data['post_meta'][$key]) ) ? $data['post_meta'][$key] : $customer_email;
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                break;
                            case '_billing_first_name':
                                $meta_value = (!empty($data['post_meta'][$key]) ) ? $data['post_meta'][$key] : $username;
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                update_user_meta($found_customer, 'first_name', $meta_value);
                                break;
                            case '_billing_last_name':
                                $meta_value = (!empty($data['post_meta'][$key]) ) ? $data['post_meta'][$key] : '';
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                update_user_meta($found_customer, 'last_name', $meta_value);
                                break;
                            case '_shipping_first_name':
                            case '_shipping_last_name':
                            case '_shipping_address_1':
                            case '_shipping_address_2':
                            case '_shipping_city':
                            case '_shipping_postcode':
                            case '_shipping_state':
                            case '_shipping_country':
                                // Set the shipping address fields to match the billing fields if not specified in CSV
                                $meta_value = (!empty($data['post_meta'][$key]) ) ? $data['post_meta'][$key] : '';

                                if (empty($meta_value)) {
                                    $n_key = str_replace('shipping', 'billing', $key);
                                    $meta_value = (!empty($data['post_meta'][$n_key]) ) ? $data['post_meta'][$n_key] : '';
                                }
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                break;

                            default:
                                $meta_value = (!empty($data['post_meta'][$key]) ) ? $data['post_meta'][$key] : '';
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                        }
                    }
                    $this->hf_make_user_active($found_customer);
                    // send user registration email if admin as chosen to do so
                    if ($email_customer && function_exists('wp_new_user_notification')) {
                        $previous_option = get_option('woocommerce_registration_generate_password');
                        // force the option value so that the password will appear in the email
                        update_option('woocommerce_registration_generate_password', 'yes');

                        do_action('woocommerce_created_customer', $found_customer, array('user_pass' => $password), true);

                        update_option('woocommerce_registration_generate_password', $previous_option);
                    }
                }
            }
        } else {
            $found_customer = new WP_Error('hf_invalid_customer', sprintf(__('User could not be created without Email.', 'wf_order_import_export'), $customer_id));
        }
        return $found_customer;
    }

    public function hf_make_user_active($user_id) {
        $this->hf_update_users_role($user_id, 'default_subscriber_role');
    }

    /**
     * Update a user's role to a special subscription's role
     * @param int $user_id The ID of a user
     * @param string $role_new The special name assigned to the role by Subscriptions,
     * one of 'default_subscriber_role', 'default_inactive_role' or 'default_cancelled_role'
     * @return WP_User The user with the new role.
     * @since 2.0
     */
    public function hf_update_users_role($user_id, $role_new) {
        $user = new WP_User($user_id);
        // Never change an admin's role to avoid locking out admins testing the plugin
        if (!empty($user->roles) && in_array('administrator', $user->roles)) {
            return;
        }
        // Allow plugins to prevent Subscriptions from handling roles
        if (!apply_filters('woocommerce_subscriptions_update_users_role', true, $user, $role_new)) {
            return;
        }
        $roles = $this->hf_get_new_user_role_names($role_new);
        $role_new = $roles['new'];
        $role_old = $roles['old'];
        if (!empty($role_old)) {
            $user->remove_role($role_old);
        }
        $user->add_role($role_new);
        do_action('woocommerce_subscriptions_updated_users_role', $role_new, $user, $role_old);
        return $user;
    }

    /**
     * Gets default new and old role names if the new role is 'default_subscriber_role'. Otherwise returns role_new and an
     * empty string.
     *
     * @param $role_new string the new role of the user
     * @return array with keys 'old' and 'new'.
     */
    public function hf_get_new_user_role_names($role_new) {
        $default_subscriber_role = get_option(WC_Subscriptions_Admin::$option_prefix . '_subscriber_role');
        $default_cancelled_role = get_option(WC_Subscriptions_Admin::$option_prefix . '_cancelled_role');
        $role_old = '';
        if ('default_subscriber_role' == $role_new) {
            $role_old = $default_cancelled_role;
            $role_new = $default_subscriber_role;
        } elseif (in_array($role_new, array('default_inactive_role', 'default_cancelled_role'))) {
            $role_old = $default_subscriber_role;
            $role_new = $default_cancelled_role;
        }
        return array(
            'new' => $role_new,
            'old' => $role_old,
        );
    }

    /**
     * Create a new subscription
     *
     * Returns a new WC_Subscription object on success which can then be used to add additional data.
     *
     * @return WC_Subscription | WP_Error A WC_Subscription on success or WP_Error object on failure
     * @since  2.0
     */
    function hf_create_subscription($args = array(), $subscription_exist = false) {
        $order = ( isset($args['order_id']) ) ? wc_get_order($args['order_id']) : null;
        if (!empty($args['order_id']) && ( WC()->version > '2.7' )) {
            $order_wp_post = get_post($args['order_id']);
        } elseif (!empty($order)) {
            $order_wp_post = $order->post;
        }
        if (!empty($order_wp_post) && isset($order_wp_post->post_date)) {
            $default_start_date = ( '0000-00-00 00:00:00' != $order_wp_post->post_date_gmt ) ? $order_wp_post->post_date_gmt : get_gmt_from_date($order_wp_post->post_date);
        } else {
            $default_start_date = current_time('mysql', true);
        }

        $subscription_data = array();
        // validate the start_date field
        if (!is_string($args['start_date']) || false === $this->hf_is_datetime_mysql_format($args['start_date'])) {
            return new WP_Error('woocommerce_subscription_invalid_start_date_format', _x('Invalid date. The date must be a string and of the format: "Y-m-d H:i:s".', 'Error message while creating a subscription', 'woocommerce-subscriptions'));
        } else if (strtotime($args['start_date']) > current_time('timestamp', true)) {
            return new WP_Error('woocommerce_subscription_invalid_start_date', _x('Subscription start date must be before current day.', 'Error message while creating a subscription', 'woocommerce-subscriptions'));
        }
        // check customer id is set
        if (empty($args['customer_id']) || !is_numeric($args['customer_id']) || $args['customer_id'] <= 0) {
            return new WP_Error('woocommerce_subscription_invalid_customer_id', _x('Invalid subscription customer_id.', 'Error message while creating a subscription', 'woocommerce-subscriptions'));
        }
        // check the billing period
        if (empty($args['billing_period']) || !in_array(strtolower($args['billing_period']), array_keys($this->hf_get_subscription_period_strings()))) {
            return new WP_Error('woocommerce_subscription_invalid_billing_period', __('Invalid subscription billing period given.', 'woocommerce-subscriptions'));
        }
        // check the billing interval
        if (empty($args['billing_interval']) || !is_numeric($args['billing_interval']) || absint($args['billing_interval']) <= 0) {
            return new WP_Error('woocommerce_subscription_invalid_billing_interval', __('Invalid subscription billing interval given. Must be an integer greater than 0.', 'woocommerce-subscriptions'));
        }
        $subscription_data['import_id'] = $args['import_id'];
        $subscription_data['customer_id'] = $args['customer_id']; // handle here perfectly-need discuss
        if(class_exists('HF_Subscription')){
            $subscription_data['post_type'] = 'hf_shop_subscription';
        } else {
            $subscription_data['post_type'] = 'shop_subscription';
        }
        $subscription_data['post_status'] = 'wc-' . apply_filters('woocommerce_default_subscription_status', 'pending');
        $subscription_data['ping_status'] = 'closed';
        $subscription_data['post_author'] = 1;
        $subscription_data['post_password'] = uniqid('order_');
        // translators: Order date parsed by strftime
        $post_title_date = strftime(_x('%b %d, %Y @ %I:%M %p', 'Used in subscription post title. "Subscription renewal order - <this>"', 'woocommerce-subscriptions'));
        // translators: placeholder is order date parsed by strftime
        $subscription_data['post_title'] = sprintf(_x('Subscription &ndash; %s', 'The post title for the new subscription', 'woocommerce-subscriptions'), $post_title_date);
        $subscription_data['post_date_gmt'] = $args['start_date'];
        $subscription_data['post_date'] = get_date_from_gmt($args['start_date']);
        if ($args['order_id'] > 0) {
            $subscription_data['post_parent'] = ($args['order_id']);
        }
        if (!is_null($args['customer_note']) && !empty($args['customer_note'])) {
            $subscription_data['post_excerpt'] = $args['customer_note'];
        }
        if (!empty($args['status'])) {
            if (!in_array('wc-' . $args['status'], array_keys($this->hf_get_subscription_statuses()))) {
                return new WP_Error('woocommerce_invalid_subscription_status', __('Invalid subscription status given.', 'woocommerce-subscriptions'));
            }
            $subscription_data['post_status'] = 'wc-' . $args['status'];
        }
        if ($subscription_exist) {
            $subscription_data['ID'] = $args['ID'];
            $subscription_data['import_id'] = $args['import_id'];
            if(class_exists('HF_Subscription')){
                $subscription_data['post_type'] = 'hf_shop_subscription';
            }else{
                $subscription_data['post_type'] = 'shop_subscription';
            }
            $subscription_data['post_status'] = $subscription_data['post_status'];
            $subscription_data['ping_status'] = 'closed';
            $subscription_data['post_author'] = $subscription_data['customer_id'];
            $subscription_data['post_password'] = uniqid('order_');
            // translators: Order date parsed by strftime
            $post_title_date = strftime(_x('%b %d, %Y @ %I:%M %p', 'Used in subscription post title. "Subscription renewal order - <this>"', 'woocommerce-subscriptions'));
            // translators: placeholder is order date parsed by strftime
            $subscription_data['post_title'] = sprintf(_x('Subscription &ndash; %s', 'The post title for the new subscription', 'woocommerce-subscriptions'), $post_title_date);
            $subscription_data['post_date_gmt'] = $args['start_date'];
            $subscription_data['post_date'] = get_date_from_gmt($args['start_date']);
            $subscription_id = wp_update_post(apply_filters('woocommerce_update_subscription_data', $subscription_data, $args), true);
        } else {
            $subscription_id = wp_insert_post(apply_filters('woocommerce_new_subscription_data', $subscription_data, $args), true);
        }
        if (is_wp_error($subscription_id)) {
            return $subscription_id;
        }
        // Default order meta data.
        update_post_meta($subscription_id, '_order_currency', $args['currency']);
        update_post_meta($subscription_id, '_created_via', sanitize_text_field($args['created_via']));
        // add/update the billing
        update_post_meta($subscription_id, '_billing_period', $args['billing_period']);
        update_post_meta($subscription_id, '_billing_interval', absint($args['billing_interval']));
        update_post_meta($subscription_id, '_customer_user', $args['customer_id']);
        if(class_exists('HF_Subscription')){
            return new HF_Subscription($subscription_id);
        }else{
            return new WC_Subscription($subscription_id);
        }
    }

    /**
     * Return an array statuses used to describe when a subscriptions has been marked as ending or has ended.
     *
     * @return array
     * @since 2.0
     */
    public function hf_get_subscription_ended_statuses() {
        return apply_filters('hf_subscription_ended_statuses', array('cancelled', 'trash', 'expired', 'switched', 'pending-cancel'));
    }


    /**
     * Add membership plans to imported subscriptions if applicable
     *
     * @since 1.0
     * @param int $user_id
     * @param int $subscription_id
     * @param int $product_id
     */
    public static function maybe_add_memberships($user_id, $subscription_id, $product_id) {
        if (function_exists('wc_memberships_get_membership_plans')) {
            if (!self::$membership_plans) {
                self::$membership_plans = wc_memberships_get_membership_plans();
            }
            foreach (self::$membership_plans as $plan) {
                if ($plan->has_product($product_id)) {
                    $plan->grant_access_from_purchase($user_id, $product_id, $subscription_id);
                }
            }
        }
    }

    /**
     * Adds the line item to the subscription
     *
     * @since 1.0
     * @param WC_Subscription $subscription
     * @param array $data
     * @return string
     */
    public static function add_product($details, $subscription, $data, $link_using_sku) {
        $item_args = array();
        $item_args['qty'] = isset($data['qty']) ? $data['qty'] : 1;
        if($link_using_sku || empty($data['product_id'])){
            require_once(dirname(__DIR__) . '/class-wt-ie-helper.php');
            $ie_helper_object = new WT_ie_helper();
            $product_id = $ie_helper_object->xa_wc_get_product_id_by_sku($data['sku']);
            $data['product_id'] = $product_id;
        }
        if (!isset($data['product_id'])) {
            throw new Exception(__('The product is not found.', 'wf_order_import_export'));
        }
        $_product = wc_get_product($data['product_id']);
        if (!$_product) {
            $order_item = array(
                'order_item_name' => (!empty($data['name']) ) ? $data['name'] : __('Unknown Product', 'wf_order_import_export'),
                'order_item_type' => 'line_item',
            );
            $_order_item_meta = array(
                '_qty' => $item_args['qty'] ,
                '_tax_class' => '', // Tax class (adjusted by filters)
                '_product_id' => '',
                '_variation_id' => '',
                '_line_subtotal' => !empty($data['total']) ? $data['total'] : 0, // Line subtotal (before discounts)
                '_line_subtotal_tax' => 0, // Line tax (before discounts)
                '_line_total' => !empty($data['sub_total']) ? $data['sub_total'] : 0, // Line total (after discounts)
                '_line_tax' => 0, // Line Tax (after discounts)
            );
            
            $line_item_name = (!empty($data['name']) ) ? $data['name'] : __('Unknown Product', 'wf_order_import_export');
            $product_string = $line_item_name;
        } else {
            $line_item_name = (!empty($data['name']) ) ? $data['name'] : $_product->get_title();

           $product_id = (WC()->version >= '2.7.0') ? $_product->get_id() : $_product->id;// solve issue with the hyperlink when the variation product present in the subscription and linked using the Link using the SKU option.
             if( get_post_type($product_id) == 'product_variation'){    
               $product_id = wp_get_post_parent_id($product_id);
               $data['product_id'] = $product_id;//parent id added
            }

            $product_string = sprintf('<a href="%s">%s</a>', get_edit_post_link((WC()->version >= '2.7.0') ? $product_id : $_product->id), $line_item_name);
    
            $order_item = array(
                'order_item_name' => $line_item_name,
                'order_item_type' => 'line_item',
            );
            $var_id = 0;
            if (WC()->version < '2.7.0') {
                $var_id = ($_product->product_type === 'variation') ? $_product->variation_id : 0;
            } else {
                $var_id = $_product->is_type('variation') ? $_product->get_id() : 0;
            }
            
            $_order_item_meta = array(
                '_qty' => $item_args['qty'] ,
                '_tax_class' => '', // Tax class (adjusted by filters)
                '_product_id' => $data['product_id'],
                '_variation_id' => $var_id,
                '_line_subtotal' => !empty($data['total']) ? $data['total'] : 0, // Line subtotal (before discounts)
                '_line_subtotal_tax' => !empty($data['tax']) ? $data['tax'] : 0, // Line tax (before discounts)
                '_line_total' => !empty($data['sub_total']) ? $data['sub_total'] : 0, // Line total (after discounts)
                '_line_tax' => !empty($data['tax']) ? $data['tax'] : 0, // Line Tax (after discounts)
                '_line_tax_data' => $data['tax_data']
            );
            
//            foreach (array('total', 'tax', 'subtotal', 'subtotal_tax') as $line_item_data) {
//                switch ($line_item_data) {
//                    case 'total' :
//                        $default = (!empty($data['total']) ) ? $data['total'] : WC_Subscriptions_Product::get_price($data['product_id']);
//                        break;
//                    case 'subtotal' :
//                        $default = (!empty($data['sub_total']) ) ? $data['sub_total'] : WC_Subscriptions_Product::get_price($data['product_id']);
//                        break;
//                    
//                    default :
//                        $default = 0;
//                }
//                $item_args['totals'][$line_item_data] = (!empty($data[$line_item_data]) ) ? $data[$line_item_data] : $default;
//            }
            // Add this site's variation meta data if no line item meta data was specified in the CSV

//            if (empty($data['meta']) && $_product->variation_data) {
//                $item_args['variation'] = array();
//
//                foreach ($_product->variation_data as $attribute => $variation) {
//                    $item_args['variation'][$attribute] = $variation;
//                }
//                $product_string .= ' [#' . $data['product_id'] . ']';
//            }
            if (self::$all_virtual && !$_product->is_virtual()) {
                self::$all_virtual = false;
            }
//            if (!empty($item_args['totals']['tax']) && !empty($chosen_tax_rate_id)) {
//                $item_args['totals']['tax_data']['total'] = array($chosen_tax_rate_id => $item_args['totals']['tax']);
//                $item_args['totals']['tax_data']['subtotal'] = array($chosen_tax_rate_id => $item_args['totals']['tax']);
//            }
//            $item_id = $subscription->add_product($_product, $item_args['qty'], $item_args);
            // Set the name used in the CSV if it's different to the product's current title (which is what WC_Abstract_Order::add_product() uses)
//            if (!empty($data['name']) && $_product->get_title() != $data['name']) {
//                wc_update_order_item($item_id, array('order_item_name' => $data['name']));
//            }
            // Add any meta data for the line item
//            if (!empty($data['meta'])) {
//                foreach (explode('+', $data['meta']) as $meta) {
//                    $meta = explode('=', $meta);
//                    wc_update_order_item_meta($item_id, $meta[0], $meta[1]);
//                }
//            }
//            if (!$item_id) {
//                throw new Exception(__('An unexpected error occurred when trying to add product "%s" to your subscription. The error was caught and no subscription for this row will be created. Please fix up the data from your CSV and try again.', 'wf_order_import_export'));
//            }
            if (!empty($details['download_permissions']) && ( 'true' == $details['download_permissions'] || 1 == (int) $details['download_permissions'] )) {
                self::save_download_permissions($subscription, $_product, $item_args['qty']);
            }
        }
        
        $order_item_id = wc_add_order_item(( ( WC()->version >= '2.7.0' ) ? $subscription->get_id() : $subscription->id), $order_item);
        
        if ($order_item_id) {
            foreach ($_order_item_meta as $meta_key => $meta_value) {
                wc_add_order_item_meta($order_item_id, $meta_key, maybe_unserialize($meta_value));
            }
        }
        return $product_string;
    }

    /**
     * Save download permission to the subscription.
     *
     * @since 1.0
     * @param WC_Subscription $subscription
     * @param WC_Product $product
     * @param int $quantity
     */
    public static function save_download_permissions($subscription, $product, $quantity = 1) {
        if ($product && $product->exists() && $product->is_downloadable()) {
            $downloads = $product->get_downloads();
            $product_id = isset($product->variation_id) ? $product->variation_id : ((WC()->version >= '2.7.0') ? $product->get_id() : $product->id);
            foreach (array_keys($downloads) as $download_id) {
                wc_downloadable_file_permission($download_id, $product_id, $subscription, $quantity);
            }
        }
    }

    /**
     * Add coupon line item to the subscription. The discount amount used is based on priority list.
     *
     * @since 1.0
     * @param WC_Subscription $subscription
     * @param array $data
     */
    public static function add_coupons($subscription, $data) {
        $coupon_items = explode(';', $data['coupon_items']);
        if (!empty($coupon_items)) {
            foreach ($coupon_items as $coupon_item) {
                $coupon_data = array();
                foreach (explode('|', $coupon_item) as $item) {
                    list( $name, $value ) = explode(':', $item);
                    $coupon_data[trim($name)] = trim($value);
                }
                $coupon_code = isset($coupon_data['code']) ? $coupon_data['code'] : '';
                $coupon = new WC_Coupon($coupon_code);
                if (!$coupon) {
                    throw new Exception(sprintf(esc_html__('Could not find coupon with code "%s" in your store.', 'wf_order_import_export'), $coupon_code));
                } elseif (isset($coupon_data['amount'])) {
                    $discount_amount = floatval($coupon_data['amount']);
                } else {
                    $discount_amount = ( WC()->version >= '2.7.0' ) ? $coupon->get_amount() : $coupon->discount_amount;
                }
                if (WC()->version >= '2.7.0') {
                    $cpn = new WC_Order_Item_Coupon();
                    $cpn->set_code($coupon_code);
                    $cpn->set_discount($discount_amount);
                    $cpn->save();
                    $subscription->add_item($cpn);
                    $coupon_id = $cpn->get_id();
                } else {
                    $coupon_id = $subscription->add_coupon($coupon_code, $discount_amount);
                }
                if (!$coupon_id) {
                    throw new Exception(sprintf(esc_html__('Coupon "%s" could not be added to subscription.', 'wf_order_import_export'), $coupon_code));
                }
            }
        }
    }

    /**
     * PHP on Windows does not have strptime function. Therefore this is what we're using to check
     * whether the given time is of a specific format.
     * @param  string $time the mysql time string
     * @return boolean      true if it matches our mysql pattern of YYYY-MM-DD HH:MM:SS
     */
    public function hf_is_datetime_mysql_format($time) {
        if (!is_string($time)) {
            return false;
        }
        if (function_exists('strptime')) {
            $valid_time = $match = ( false !== strptime($time, '%Y-%m-%d %H:%M:%S') ) ? true : false;
        } else {
            // parses for the pattern of YYYY-MM-DD HH:MM:SS, but won't check whether it's a valid timedate
            $match = preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $time);
            // parses time, returns false for invalid dates
            $valid_time = strtotime($time);
        }
        // magic number -2209078800 is strtotime( '1900-01-00 00:00:00' ). Needed to achieve parity with strptime
        return ( $match && false !== $valid_time && -2209078800 <= $valid_time ) ? true : false;
    }
    /**
     * Return translated associative array of all possible subscription periods.
     * @param int (optional) An interval in the range 1-6
     * @param string (optional) One of day, week, month or year. If empty, all subscription ranges are returned.
     */
    public function hf_get_subscription_period_strings($number = 1, $period = '') {
        $translated_periods = apply_filters('woocommerce_subscription_periods', array(
            // translators: placeholder is number of days. (e.g. "Bill this every day / 4 days")
            'day' => sprintf(_nx('day', '%s days', $number, 'Subscription billing period.', 'woocommerce-subscriptions'), $number),
            // translators: placeholder is number of weeks. (e.g. "Bill this every week / 4 weeks")
            'week' => sprintf(_nx('week', '%s weeks', $number, 'Subscription billing period.', 'woocommerce-subscriptions'), $number),
            // translators: placeholder is number of months. (e.g. "Bill this every month / 4 months")
            'month' => sprintf(_nx('month', '%s months', $number, 'Subscription billing period.', 'woocommerce-subscriptions'), $number),
            // translators: placeholder is number of years. (e.g. "Bill this every year / 4 years")
            'year' => sprintf(_nx('year', '%s years', $number, 'Subscription billing period.', 'woocommerce-subscriptions'), $number),
                )
        );

        return (!empty($period) ) ? $translated_periods[$period] : $translated_periods;
    }
    
    public static function wt_get_order_with_import_key($id){
        global $wpdb;
        
        $order_id = $wpdb->get_var($wpdb->prepare(
            "SELECT po.ID FROM {$wpdb->posts} AS po
            INNER JOIN {$wpdb->postmeta} AS pm
            ON po.ID = pm.post_id
            WHERE po.post_type = 'shop_order'
            AND pm.meta_key = '_wt_import_key'
            AND pm.meta_value = %d",$id
        ));
        return $order_id;
    }

    /**
     * Return an array of subscription status types, similar to @see wc_get_order_statuses()
     * @return array
     */
    public function hf_get_subscription_statuses() {
        $subscription_statuses = array(
            'wc-pending' => _x('Pending', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-active' => _x('Active', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-on-hold' => _x('On hold', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-cancelled' => _x('Cancelled', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-switched' => _x('Switched', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-expired' => _x('Expired', 'Subscription status', 'woocommerce-subscriptions'),
            'wc-pending-cancel' => _x('Pending Cancellation', 'Subscription status', 'woocommerce-subscriptions'),
        );
        return apply_filters('hf_subscription_statuses', $subscription_statuses);
    }

    /**
     * Import tax lines
     * @param WC_Subscription $subscription
     * @param array $data
     */
    public static function add_taxes($subscription, $data) {
        global $wpdb;
        $tax_items = explode(';', $data['tax_items']);
        $chosen_tax_rate_id = 0;
        if (!empty($tax_items)) {
            foreach ($tax_items as $tax_item) {
                $tax_data = array();

                if (false !== strpos($tax_item, ':')) {
                    foreach (explode('|', $tax_item) as $item) {
                        list( $name, $value ) = explode(':', $item);
                        $tax_data[trim($name)] = trim($value);
                    }
                } elseif (1 == count($tax_items)) {
                    if (is_numeric($tax_item)) {
                        $tax_data['rate_id'] = $tax_item;
                    } else {
                        $tax_data['code'] = $tax_item;
                    }
                }

                if (!empty($tax_data['rate_id'])) {
                    $tax_rate = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %s", $tax_data['rate_id']));
                } elseif (!empty($tax_data['code'])) {
                    $tax_rate = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_name = %s ORDER BY tax_rate_priority LIMIT 1", $tax_data['code']));
                } else {
                    $result['warning'][] = esc_html__(sprintf('Missing tax code or ID from column: %s', $data['tax_items']), 'wf_order_import_export');
                }

                if (!empty($tax_rate)) {

                    $tax_rate = array_pop($tax_rate);
                    if (WC()->version > '2.7.0') {
                        foreach ($data['post_meta'] as $key_main => $valuemain) {
                            if ($valuemain['key'] == '_order_shipping_tax')
                                $temp_order_shipping_tax = $valuemain['value'];
                            if ($valuemain['key'] == '_order_tax')
                                $temp_order_tax_total = $valuemain['value'];
                        }
                        $tax = new WC_Order_Item_Tax();
                        $tax->set_props(array(
                            'rate_id' => $tax_rate->tax_rate_id,
                            'tax_total' => (!empty($temp_order_tax_total) ? $temp_order_tax_total : 0 ),
                            'shipping_tax_total' => (!empty($temp_order_shipping_tax) ? $temp_order_shipping_tax : 0 ),
                        ));
                        $tax->set_rate($tax_rate->tax_rate_id);
                        $tax->set_order_id($subscription->get_id());
                        $tax->save();
                        $subscription->add_item($tax);
                        $tax_id = $tax->get_id();
                    }
                    else {
                        $tax_id = $subscription->add_tax($tax_rate->tax_rate_id, (!empty($data['order_shipping_tax']) ) ? $data['order_shipping_tax'] : 0, (!empty($data['order_tax']) ) ? $data['order_tax'] : 0 );
                    }
                    if (!$tax_id) {
                        $result['warning'][] = esc_html__('Tax line item could not properly be added to this subscription. Please review this subscription.', 'wf_order_import_export');
                    } else {
                        $chosen_tax_rate_id = $tax_rate->tax_rate_id;
                    }
                } else {
                    $result['warning'][] = esc_html__(sprintf('The tax code "%s" could not be found in your store.', $tax_data['code']), 'wf_order_import_export');
                }
            }
        }
        return $chosen_tax_rate_id;
    }
}
