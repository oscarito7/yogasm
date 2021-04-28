<?php
/**
 * WordPress Importer class for managing the import process of a CSV file
 *
 * @package WordPress
 * @subpackage Importer
 */
if (!class_exists('WP_Importer'))
    return;

class WF_OrderImpExpCsv_Order_Import extends WP_Importer {

    var $id;
    var $file_url;
    var $delimiter;
    var $profile;
    var $merge_empty_cells;
    var $merge;
    var $wtcreateuser;
    var $status_mail;
    var $processed_posts = array();
    var $merged = 0;
    var $skipped = 0;
    var $imported = 0;
    var $errored = 0;
    // Results
    var $import_results = array();
    var $ord_link_using_sku;

    /**
     * Constructor
     */
    public function __construct() {
        if (WF_OrderImpExpCsv_Common_Utils::is_woocommerce_prior_to('2.7')) {
            $this->log = new WC_Logger();
        } else {
            $this->log = wc_get_logger();
        }
        $this->import_page = 'woocommerce_wf_order_csv';
        $this->file_url_import_enabled = apply_filters('woocommerce_csv_product_file_url_import_enabled', true);
    }

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
        if (!empty($_POST['profile'])) {
            $this->profile = sanitize_text_field($_POST['profile']);
        } else if (!empty($_GET['profile'])) {
            $this->profile = sanitize_text_field($_GET['profile']);
        }
        if (!$this->profile)
            $this->profile = '';
        if (!empty($_POST['merge_empty_cells']) || !empty($_GET['merge_empty_cells'])) {
            $this->merge_empty_cells = 1;
        } else {
            $this->merge_empty_cells = 0;
        }
        if (!empty($_POST['merge']) || !empty($_GET['merge'])) {
            $this->merge = 1;
        } else {
            $this->merge = 0;
        }
        if(!empty($_POST['status_mail']) || !empty($_GET['status_mail'])){
            $this->status_mail = 1;
        } else {
            $this->status_mail = 0;
        }
        if (!empty($_POST['wtcreateuser']) || !empty($_GET['wtcreateuser'])) {
            $this->wtcreateuser = 1;
        } else {
            $this->wtcreateuser = 0;
        }
        if(!empty($_POST['ord_link_using_sku']) || !empty($_GET['ord_link_using_sku'])){
            $this->ord_link_using_sku = 1;
        } else {
            $this->ord_link_using_sku = 0;
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
                exit;
                break;
            case 2 :
                $this->header();
                check_admin_referer('import-woocommerce');
                $this->id = absint($_POST['import_id']);
                if ($this->file_url_import_enabled)
                    $this->file_url = esc_attr($_POST['import_url']);
                if ($this->id)
                    $file = get_attached_file($this->id);
                else if ($this->file_url_import_enabled)
                    $file = ABSPATH . $this->file_url;
                if ($this->hf_mime_content_type($file) === 'application/xml' || $this->hf_mime_content_type($file) === 'text/xml')
                    $file = $this->xml_import($file);
                $file = str_replace("\\", "/", $file);
                $tab = (isset($_GET['tab']) && !empty($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : 'import';
                if ($file) {
                    include_once(dirname(__FILE__) . '/../views/html-wf-common-header.php');
                    ?>
                    <ul class="subsubsub" style="margin-left: 15px;">
                        <li><a href="<?php echo admin_url('admin.php?page=wf_woocommerce_order_im_ex') ?>" class=""><?php _e('Export', 'wf_order_import_export'); ?></a> | </li>
                        <li><a href="<?php echo admin_url('admin.php?import=woocommerce_wf_order_csv') ?>" class="current"><?php _e('Import', 'wf_order_import_export'); ?></a> </li>
                    </ul>
                    <br/><br/>
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
                                    action:             'woocommerce_csv_order_import_request',
                                    file:               '<?php echo addslashes($file); ?>',
                                    mapping:            '<?php echo json_encode(wc_clean($_POST['map_from'])); ?>',
                                    profile:            '<?php echo $this->profile; ?>',
                                    eval_field:         '<?php echo stripslashes(json_encode(wc_clean($_POST['eval_field']), JSON_HEX_APOS)) ?>',
                                    delimiter:          '<?php echo $this->delimiter; ?>',
                                    merge_empty_cells:  '<?php echo $this->merge_empty_cells; ?>',
                                    merge:              '<?php echo $this->merge; ?>',
                                    wtcreateuser:       '<?php echo $this->wtcreateuser; ?>',
                                    status_mail:        '<?php echo $this->status_mail; ?>',
                                    ord_link_using_sku: '<?php echo $this->ord_link_using_sku; ?>',
                                    start_pos:          start_pos,
                                    end_pos:            end_pos,
                                    wt_nonce:   '<?php echo wp_create_nonce(WF_ORDER_IMP_EXP_ID) ?>'
                                    
                                };
                                data.eval_field = $.parseJSON(data.eval_field);
                                return $.ajax({
                                    url:        '<?php echo add_query_arg(array('import_page' => $this->import_page, 'step' => '3', 'merge' => !empty($_GET['merge']) ? '1' : '0', 'status_mail' => !empty($_GET['status_mail']) ? '1' : '0', 'ord_link_using_sku' => !empty($_GET['ord_link_using_sku']) ? '1' : '0','wtcreateuser' => !empty($_GET['wtcreateuser']) ? '1' : '0'), admin_url('admin-ajax.php')); ?>',
                                    data:       data,
                                    type:       'POST',
                                    success:    function(response) {
                                        if (response) {
                                            try {             // Get the valid JSON only from the returned string
                                                if (response.indexOf("<!--WC_START-->") >= 0)
                                                    response = response.split("<!--WC_START-->")[1]; // Strip off before after WC_START
                                                if (response.indexOf("<!--WC_END-->") >= 0)
                                                    response = response.split("<!--WC_END-->")[0]; // Strip off anything after WC_END
                                                // Parse
                                                var results = $.parseJSON(response);
                                                //console.log(results);
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
                                        $('body').trigger('woocommerce_csv_order_import_request_complete');
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
                                    ?>rows.push([ <?php echo $position; ?>, '' ]); <?php
                                    $import_count ++;
                                }
                                fclose($handle);
                            }
                            ?>
                            var data = rows.shift();
                            var regen_count = 0;
                            import_rows( data[0], data[1] );
                            $('body').on( 'woocommerce_csv_order_import_request_complete', function() {
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
                                    action: 'woocommerce_csv_order_import_request',
                                    file: '<?php echo $file; ?>',
                                    processed_posts: processed_posts,
                                    wt_nonce:   '<?php echo wp_create_nonce(WF_ORDER_IMP_EXP_ID) ?>'
                                };
                                $.ajax({
                                    url: '<?php echo add_query_arg(array('import_page' => $this->import_page, 'step' => '4', 'merge' => !empty($_GET['merge']) ? 1 : 0, 'status_mail' => !empty($_GET['status_mail']) ? 1 : 0 , 'ord_link_using_sku' => !empty($_GET['ord_link_using_sku']) ? 1 : 0,'wtcreateuser' => !empty($_GET['wtcreateuser']) ? 1 : 0), admin_url('admin-ajax.php')); ?>',
                                    data:       data,
                                    type:       'POST',
                                    success:    function( response ) {
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
                if (!wp_verify_nonce($nonce,WF_ORDER_IMP_EXP_ID) || !WF_Order_Import_Export_CSV::hf_user_permission()) {
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
                $mapping = json_decode(stripslashes($_POST['mapping']), true);
                $profile = isset($_POST['profile']) ? wc_clean($_POST['profile']) : '';
                $eval_field = wc_clean($_POST['eval_field']);
                $start_pos = isset($_POST['start_pos']) ? absint($_POST['start_pos']) : 0;
                $end_pos = isset($_POST['end_pos']) ? absint($_POST['end_pos']) : '';
                if ($profile !== '') {
                    $profile_array = get_option('wf_order_csv_imp_exp_mapping');
                    $profile_array[$profile] = array($mapping, $eval_field);
                    update_option('wf_order_csv_imp_exp_mapping', $profile_array);
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
                if (!wp_verify_nonce($nonce,WF_ORDER_IMP_EXP_ID) || !WF_Order_Import_Export_CSV::hf_user_permission()) {
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
                $this->processed_posts = isset($_POST['processed_posts']) ? wc_clean($_POST['processed_posts']) : array();
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

    public function createCsv($xml, $f) {
        foreach ($xml->children() as $item) {
            $row_data = array_values((array) $item);
            fputcsv($f, $row_data, ',', '"');
        }
    }

    public function xml_import($file) {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($file);
        if ($xml) {
            $file = plugin_dir_path($file) . "export_" .(int) (microtime(true) * 1000000000). ".csv"; //change file name for multiple xml import via sftp
            $header = array_keys((array) $xml->children()->children());
            $fp = fopen($file, 'w');
            fputcsv($fp, $header, ',', '"');
            $this->createCsv($xml, $fp);
            fclose($fp);
        } else {
            echo '<div class="error notice"><p>This XML File Is Not Valid</p></div>';
        }
        return $file;
    }

    public function hf_mime_content_type($filename) {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        $value = explode('.', $filename);
        $ext = strtolower(array_pop($value));
        if (function_exists('mime_content_type')) {
            $mimetype = mime_content_type($filename);
            return $mimetype;
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } elseif (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Display pre-import options
     */
    public function import_options() {
        $j = 0;
        if ($this->id)
            $file = get_attached_file($this->id);
        else if ($this->file_url_import_enabled)
            $file =  ABSPATH . $this->file_url;
        else
            return;
        if ($this->hf_mime_content_type($file) === 'application/xml' || $this->hf_mime_content_type($file) === 'text/xml')
            $file = $this->xml_import($file);
        // Set locale
        $enc = mb_detect_encoding($file, 'UTF-8, ISO-8859-1', true);
        if ($enc)
            setlocale(LC_ALL, 'en_US.' . $enc);
        @ini_set('auto_detect_line_endings', true);
        // Get headers
        if (( $handle = fopen($file, "r") ) !== FALSE) {
            $row = $raw_headers = array();
            $header = fgetcsv($handle, 0, $this->delimiter);
            if(substr($header[0],0,3)==chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'))){
                 $header[0]= str_replace('"','',substr($header[0],3));
            }
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
        $mapping_from_db = get_option('wf_order_csv_imp_exp_mapping');
        if ($this->profile !== '' && !empty($_GET['clearmapping'])) {
            unset($mapping_from_db[$this->profile]);
            update_option('wf_order_csv_imp_exp_mapping', $mapping_from_db);
            $this->profile = '';
        }
        if ($this->profile !== '')
            $mapping_from_db = $mapping_from_db[$this->profile];
        $saved_mapping = null;
        $saved_evaluation = null;
        if ($mapping_from_db && is_array($mapping_from_db) && count($mapping_from_db) == 2 && empty($_GET['clearmapping'])) {
            $reset_action = 'admin.php?clearmapping=1&amp;profile=' . $this->profile . '&amp;import=' . $this->import_page . '&amp;step=1&amp;merge=' . $this->merge . '&amp;status_mail='. $this->status_mail . '&amp;ord_link_using_sku='.$this->ord_link_using_sku . '&amp;wtcreateuser=' . $this->wtcreateuser . '&amp;file_url=' . $this->file_url . '&amp;delimiter=' . $this->delimiter . '&amp;merge_empty_cells=' . $this->merge_empty_cells . '&amp;file_id=' . $this->id . '';
            $reset_action = esc_attr(wp_nonce_url($reset_action, 'import-upload'));
            echo '<h3>' . __('Columns are pre-selected using the Mapping file: "<b style="color:gray">' . $this->profile . '</b>".  <a href="' . $reset_action . '"> Delete</a> this mapping file.', 'wf_order_import_export') . '</h3>';
            $saved_mapping = $mapping_from_db[0];
            $saved_evaluation = $mapping_from_db[1];
        }
        include( 'views/html-wf-import-options.php' );
    }

    /**
     * The main controller for the actual import stage.
     */
    public function import() {
        global $woocommerce, $wpdb;
        wp_suspend_cache_invalidation(true);
        $this->hf_order_log_data_change('hf-order-csv-import', '---');
        $this->hf_order_log_data_change('hf-order-csv-import', __('Processing orders...', 'wf_order_import_export'));
        $merging = 1;
        $record_offset = 0;
        foreach ($this->parsed_data as $key => &$item) {
            $order = $this->parser->parse_orders($item, $this->raw_headers, $merging, $record_offset, $this->ord_link_using_sku);
            if (!is_wp_error($order))
                $this->process_orders($order['shop_order'][0]);
            else
                $this->add_import_result('failed', $order->get_error_message(), 'Not parsed', json_encode($item), '-');
            unset($item, $order);
        }
        $this->hf_order_log_data_change('hf-order-csv-import', __('Finished processing Orders.', 'wf_order_import_export'));
        wp_suspend_cache_invalidation(false);
    }

    /**
     * Parses the CSV file and prepares us for the task of processing parsed data
     *
     * @param string $file Path to the CSV file for importing
     */
    public function import_start($file, $mapping, $start_pos, $end_pos, $eval_field) {
        $memory = size_format((WC()->version < '2.7.0') ? woocommerce_let_to_num(ini_get('memory_limit')) : wc_let_to_num(ini_get('memory_limit')));
        $wp_memory = size_format((WC()->version < '2.7.0') ? woocommerce_let_to_num(WP_MEMORY_LIMIT) : wc_let_to_num(WP_MEMORY_LIMIT));
        $this->hf_order_log_data_change('hf-order-csv-import', '---[ New Import ] PHP Memory: ' . $memory . ', WP Memory: ' . $wp_memory);
        $this->hf_order_log_data_change('hf-order-csv-import', __('Parsing order CSV.', 'wf_order_import_export'));
        $this->parser = new WF_CSV_Parser_Ord('shop_order');
        list( $this->parsed_data, $this->raw_headers, $position ) = $this->parser->parse_data($file, $this->delimiter, $mapping, $start_pos, $end_pos, $eval_field);
        $this->hf_order_log_data_change('hf-order-csv-import', __('Finished parsing order CSV.', 'wf_order_import_export'));
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
        if (empty($_POST['file_url']) && (!empty($_FILES['import']['name']) )) {
            $file = wp_import_handle_upload();
            if (isset($file['error'])) {
                echo '<p><strong>' . __('Sorry, there has been an error.', 'wf_order_import_export') . '</strong><br />';
                echo esc_html($file['error']) . '</p>';
                return false;
            }
            $this->id = (int) $file['id'];
            return true;
        } elseif (isset($_POST['file_url'])) {
            $this->file_url = esc_attr($_POST['file_url']);
            return true;
        } elseif (!empty($_POST['ord_import_from_url'])) {
            if (filter_var($_POST['ord_import_from_url'], FILTER_VALIDATE_URL)) {
                $this->file_url = $this->get_data_from_url($_POST['ord_import_from_url']);
                if(!$this->file_url){                    
                    return false;
                }
                return true;
            } else {
                echo '<p><strong>' . __('Sorry, The entered URL is not valid.', 'wf_order_import_export') . '</strong></p>';
                return false;
            }
        } else {
            echo '<p><strong>' . __('Sorry, there has been an error.', 'wf_order_import_export') . '</strong></p>';
            return false;
        }
        return false;
    }
    
    public function get_data_from_url($url) {
        set_time_limit(0); // avoiding time out issue.
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );  
        if (strpos(substr($url, 0, 7), 'ftp://') !== false) { // the given url is an ftp url
            function get_password_and_host_from_url($url) {
                $vsar = explode('@', $url);
                list($host) = explode('/', end($vsar));  // get host name, here list holds the first element of an array 
                $path = substr(end($vsar), strlen($host));
                $port = (substr($url, 0, 4)=='sftp'? 22 : 21);
                array_pop($vsar); // removes last element of array
                $v2 = implode('@', $vsar);
                $v3 = explode(':', $v2);
                array_shift($v3);
                array_shift($v3);
                $password = $v3[0];
                return array($password, $host, $path, $port);
            }
            function get_string_between($string, $start, $end) {
                $string = ' ' . $string;
                $ini = strpos($string, $start);
                if ($ini == 0)
                    return '';
                $ini += strlen($start);
                $len = strpos($string, $end, $ini) - $ini;
                return substr($string, $ini, $len);
            } 
            $username = get_string_between($url, '://', ':');
            list($passsword, $host, $path, $port) = get_password_and_host_from_url($url);
            return $this->handle_ftp_for_url($username, $passsword, $host, $path,$port);
        }
        if (ini_get('allow_url_fopen')) {
            $file_contents = @file_get_contents($url, false, stream_context_create($arrContextOptions));
        } else {
            echo '<p><strong>' . __('Sorry, allow_url_fopen not activated. Please setup in php.ini', 'wf_order_import_export') . '</strong></p>';
            return false;
        }
        if (empty($file_contents)) {
            echo '<p><strong>' . __('Sorry, there has been an error.', 'wf_order_import_export') . '</strong></p>';
            return false;
        }
        $wp_upload_dir = wp_upload_dir();
        $wp_upload_path = $wp_upload_dir['path'];
        $local_file = $wp_upload_path . '/woocommerce-order-import-from-url.csv.txt';
        file_put_contents($local_file, $file_contents);
        return esc_attr(str_replace(ABSPATH, "", $local_file));
    }
    
    private function handle_ftp_for_url($username_via_url='',$passsword_via_url='',$host_via_url='',$path_via_url='',$port_via_url=21) {
        if(empty($username_via_url) && empty($passsword_via_url) && empty($host_via_url) && empty($path_via_url)){
            return false;
        }
        $ftp_server = !empty($host_via_url) ? $host_via_url : '';
        $ftp_server_path = !empty($path_via_url) ? $path_via_url : '';
        $ftp_user = !empty($username_via_url) ? $username_via_url : '';
        $ftp_password = !empty($passsword_via_url) ? $passsword_via_url : '';
        $ftp_port =  $port_via_url;
        $use_ftps = TRUE;
        $local_file = 'wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import.csv';
        $server_file = $ftp_server_path;
        $error_message = "";
        $success = false;
        // if have SFTP Add-on for Import Export for WooCommerce 
        if (class_exists('class_wf_sftp_import_export')) {
            $sftp_import = new class_wf_sftp_import_export();
            if (!$sftp_import->connect($ftp_server, $ftp_user, $ftp_password, $ftp_port)) {
                $error_message = "Not able to connect to the server please check <b>FTP Server Host / IP</b> and <b>Port number</b>. \n";
            }
            if (empty($server_file)) {
                $error_message = "Please Complete fill the FTP Details. \n";
            } else {
                $file_contents = $sftp_import->get_contents($server_file);
                if (!empty($file_contents)) {
                    file_put_contents(ABSPATH . $local_file, $file_contents);
                    $error_message = "";
                    $success = true;
                } else {
                    $error_message = "Failed to Download Specified file in FTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>plugins/order-import-export-for-woocommerce-pro/temp-import.csv</b> .\n";
                }
            }
        } else {
            $ftp_conn = $use_ftps ? @ftp_ssl_connect($ftp_server, $ftp_port) : @ftp_connect($ftp_server, $ftp_port);
            if ($ftp_conn == false) {
                $error_message = "Not able to connect to the server please check <b>FTP Server Host / IP</b> and <b>Port number</b>. \n";
            } else {
                if (!@ftp_login($ftp_conn, $ftp_user, $ftp_password)) {
                    $error_message = "Connected to FTP Server.<br/>But, not able to login please check <b>FTP User Name</b> and <b>Password.</b>\n";
                }
            }
            if (empty($error_message)) {
                if ($use_ftps) {
                    ftp_pasv($ftp_conn, TRUE);
                }
                if (@ftp_get($ftp_conn, ABSPATH . $local_file, $server_file, FTP_BINARY)) {
                    $error_message = "";
                    $success = true;
                } else {
                    $error_message = "Failed to Download Specified file in FTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>plugins/order-import-export-for-woocommerce-pro/temp-import.csv</b> .\n";
                }
            }
            if ($ftp_conn != false) {
                ftp_close($ftp_conn);
            }
        }
        if ($success) {
            return $local_file;
        } else {
            die($error_message);
        }
        return true;
    }

    public function order_exists($orderID) {
        global $wpdb;
        $query = "SELECT ID FROM $wpdb->posts WHERE post_type = 'shop_order' AND post_status IN ( 'wc-pending', 'wc-processing', 'wc-completed', 'wc-on-hold', 'wc-failed' , 'wc-refunded', 'wc-cancelled')";
        $query = apply_filters('wt_orderimpexpcsv_import_order_exists_query', $query);
        $posts_are_exist = $wpdb->get_col($query);
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
    private function process_orders($post) {
        if (empty($post)) {
            $this->add_import_result('skipped', __('Order Skipped, please check the log file', 'wf_order_import_export'), 0, 0, 0);
            unset($post);
            return;
        }
        global $wpdb;
        $this->imported = $this->merged = 0;
        $merging = (!empty($_GET['merge'])) ? 1 : 0;
        // Plan a dry run
        $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] ? true : false;
        $email_customer = false; // set this as settings for choosing weather to mail details for newly created customers.
        $meta_array = array();
        if (!empty($post['postmeta'])) {
            foreach ($post['postmeta'] as $meta) {
                $meta_array[$meta['key']] = $meta['value'];
            }
        }
        $is_order_exist = $this->order_exists($post['order_id']);
        $user_id = $this->hf_check_customer($meta_array, $email_customer, $this->wtcreateuser);
        if (is_wp_error($user_id)) {
            $this->hf_order_log_data_change('hf-order-csv-import', __($user_id->get_error_message(), 'wf_order_import_export'));
            $this->add_import_result('skipped', __($user_id->get_error_message(), 'wf_order_import_export'), $post['order_number'], $post['order_number'], $post['order_number']);
            $skipped++;
            unset($post);
            return;
        } elseif (empty($user_id)) {
            $user_id = 0;
        }
        $this->hf_order_log_data_change('hf-order-csv-import', __('Processing orders.', 'wf_order_import_export'));
        //Check class-wc-checkout.php for reference
        $order_data = array(
            'import_id' => $post['order_id'], //Suggest import to keep the given ID
            'post_type' => 'shop_order',
            'ping_status' => 'closed',
            'post_author' => 1,
            'post_password' => uniqid('order_'), // Protects the post just in case
        );
        if(!empty($post['date'])){
            $order_data['post_date'] = date('Y-m-d H:i:s', $post['date']);
            $order_data['post_date_gmt'] = date('Y-m-d H:i:s', $post['date']);
            $order_data['post_title'] = 'Order &ndash; ' . date('F j, Y @ h:i A', $post['date']);
        }
        if(!empty($post['status'])){
            $order_data['post_status'] = 'wc-' . preg_replace('/^wc-/', '', $post['status']);
        }
        if(!empty($post['order_comments'])){
            $order_data['post_excerpt'] = $post['order_comments'];
        }
        if (!$dry_run) {
            //check whether download permissions need to be granted
            $add_download_permissions = false;
            // Check if post exists when importing
            $new_added = false;
            $order_id = $post['order_id'];
            if ($order_id && is_string(get_post_status($order_id)) && (get_post_type($order_id) !== 'shop_order' )) {
                $usr_msg = 'Importing order(ID) conflicts with an existing post.';
                $this->add_import_result('skipped', __($usr_msg, 'wf_order_import_export'), $post['order_id'], get_the_title($post['order_id']));
                $this->hf_order_log_data_change('hf-order-csv-import', __('> &#8220;%s&#8221;' . $usr_msg, 'wf_order_import_export'), esc_html($post['order_id']), true);
                unset($post);
                return;
            }
            if (!$merging && $is_order_exist) {
                $usr_msg = 'Order with same ID already exists';
                $this->add_import_result('skipped', __($usr_msg, 'wf_order_import_export'), $order_id, $order_data['post_title'], $order_id);
                $this->hf_order_log_data_change('hf-order-csv-import', sprintf(__('> &#8220;%s&#8221;' . $usr_msg, 'wf_order_import_export'), esc_html($order_id), true));
                unset($post);
                return;
            } else {
                if ($is_order_exist) {
                    $order_data['ID'] = $post['order_id'];
                    if(class_exists('HF_Subscription')){
                       remove_all_actions('save_post');
                    }
                    if($this->status_mail != TRUE){
                        wp_update_post($order_data);
                    } else {
                        unset($order_data['post_status']);
                        wp_update_post($order_data);
                        $order = wc_get_order($order_data['ID']);
                        if($order){
                            $order->update_status($post['status']);
                        }                                                
                    }
                    $order_id = $post['order_id'];
                } else {
                    if(class_exists('HF_Subscription')){
                       remove_all_actions('save_post');
                    }
                    $order_id = wp_insert_post($order_data);
                    $new_added = true;
                    if (is_wp_error($order_id)) {
                        $this->errored++;
                        $new_added = false;
                        //$this->add_import_result('failed', __($order_id->get_error_message() , 'wf_order_import_export'), $post['order_number'], $order_data['post_title'], $post['order_number']);
                        $this->hf_order_log_data_change('hf-order-csv-import', __('> Error inserting %s: %s', 'wf_order_import_export'), $post['order_number'], $order_id->get_error_message(), true);
                    }
                }
            }
            //empty update to bump up the post_modified date to today's date (otherwise it would match the post_date, which isn't quite right)
            //wp_update_post( array( 'ID' => $order_id ) );
            // handle special meta fields
            $order_currency = (isset($post['order_currency']) && $post['order_currency']) ? $post['order_currency'] : get_woocommerce_currency();
            update_post_meta($order_id, '_order_key', apply_filters('woocommerce_generate_order_key', uniqid('order_')));
            update_post_meta($order_id, '_order_currency', $order_currency);
            update_post_meta($order_id, '_prices_include_tax', get_option('woocommerce_prices_include_tax'));
            update_post_meta($order_id, '_order_number', $post['order_number']);
            update_post_meta($order_id, '_wt_import_key', apply_filters('wt_importing_order_reference_key', $post['wt_import_key'], $post)); // for future reference, this holds the order number which in the csv.

            if ($user_id) {  // update postmeta after cerate new user by customer_email or billing_email 
                $post['postmeta'][] = array('key' => '_customer_user', 'value' => $user_id);
            }
            
            $shipping_tax = 0;
            $shipping_method = '';
            // add order postmeta
            foreach ($post['postmeta'] as $meta) {
                $meta_processed = false;
                if($meta['key'] == '_order_shipping_tax'){
                    $shipping_tax = $meta['value'];
                }
                if($meta['key'] == '_shipping_method'){
                    $shipping_method = $meta['value'];
                }
                if (( 'Download Permissions Granted' == $meta['key'] || '_download_permissions_granted' == $meta['key'] ) && $meta['value']) {
                    $add_download_permissions = true;
                    $meta_processed = true;
                }
                if (( '_customer_user' == $meta['key'])) {
                    update_post_meta($order_id, $meta['key'], $user_id);
                    $meta_processed = true;
                }
                if (!$meta_processed && !empty($meta['value'])) {
                    update_post_meta($order_id, $meta['key'], maybe_unserialize($meta['value']));
                }
                // set the paying customer flag on the user meta if applicable
                if ('_customer_id' == $meta['key'] && $user_id && in_array($post['status'], array('processing', 'completed', 'refunded'))) {
                    update_user_meta($user_id, "paying_customer", 1);
                }
            }

            // handle order items
            $order_items = array();
            $order_item_meta = null;
            if ($merging && $is_order_exist && !empty($post['order_items'])) {
                $wpdb->query($wpdb->prepare("DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON itemmeta.order_item_id = items.order_item_id WHERE items.order_id = %d and items.order_item_type = 'line_item'", $order_id));
            }
            if ($merging && $is_order_exist && !empty($post['order_shipping'])) {
                $wpdb->query($wpdb->prepare("DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items ON itemmeta.order_item_id = items.order_item_id WHERE items.order_id = %d and items.order_item_type = 'shipping'", $order_id));
            }
            $order = wc_get_order($order_id);
            $_order_item_meta = array();
            foreach ($post['order_items'] as $item) {
                $product = null;
                $variation_item_meta = array();
                $product_title = __('Unknown Product', 'wf_order_import_export');
                if ($item['product_id']) {
                    $product = wc_get_product($item['product_id']);
                    if($product){
                        $product_title = ($product->get_title()!='') ? $product->get_title() :__('Unknown Product', 'wf_order_import_export') ;
                    }
                    // handle variations
                    if ($product && ( $product->is_type('variable') || $product->is_type('variation') || $product->is_type('subscription_variation') ) && method_exists($product, 'get_variation_id')) {
                        foreach ($product->get_variation_attributes() as $key => $value) {
                            $variation_item_meta[] = array('meta_name' => esc_attr(substr($key, 10)), 'meta_value' => $value);  // remove the leading 'attribute_' from the name to get 'pa_color' for instance
                        }                        
                    }
                }
                // order item
                $order_items[] = array(
                    //'order_item_name' => $product ? $product->get_title() : (!empty($item['unknown_product_name']) ? $item['unknown_product_name'] : __('Unknown Product', 'wf_order_import_export')),
                    'order_item_name' => !empty($item['product_name']) ? $item['product_name'] : ($product_title),
                    'order_item_type' => 'line_item',
                );
                $var_id = 0;
                if ($product) {
                    if (WC()->version < '2.7.0') {
                        $var_id = ($product->product_type === 'variation') ? $product->variation_id : 0;
                    } else {
                        $var_id = $product->is_type('variation') ? $product->get_id() : 0;
                    }
                }
                // standard order item meta
                $_order_item_meta = array(
                    '_qty' => (int) $item['qty'],
                    '_tax_class' => '', // Tax class (adjusted by filters)
                    '_product_id' => $item['product_id'],
                    '_variation_id' => $var_id,
                    '_line_subtotal' => number_format((float) $item['sub_total'], 2, '.', ''), // Line subtotal (before discounts)
                    '_line_subtotal_tax' => number_format((float) $item['tax'], 2, '.', ''), // Line tax (before discounts)
                    '_line_total' => number_format((float) $item['total'], 2, '.', ''), // Line total (after discounts)
                    '_line_tax' => number_format((float) $item['tax'], 2, '.', ''), // Line Tax (after discounts)
                );
                if(!empty($item['tax_data'])){
                    $_order_item_meta['_line_tax_data'] = $item['tax_data'];
                }
                // add any product variation meta
                foreach ($variation_item_meta as $meta) {
                    $_order_item_meta[$meta['meta_name']] = $meta['meta_value'];
                }
                // include any arbitrary order item meta
                $_order_item_meta = array_merge($_order_item_meta, $item['meta']);
                $order_item_meta[] = $_order_item_meta;
            }
            foreach ($order_items as $key => $order_item) {
                $order_item_id = wc_add_order_item($order_id, $order_item);
                if ($order_item_id) {
                    foreach ($order_item_meta[$key] as $meta_key => $meta_value) {
                        wc_add_order_item_meta($order_item_id, $meta_key, maybe_unserialize($meta_value));
                    }
                }
            }
            // create the shipping order items
            foreach ($post['order_shipping'] as $order_shipping) {
                $shipping_order_item = array(
                    'order_item_name' => ($order_shipping['title']) ? $order_shipping['title'] : $shipping_method,
                    'order_item_type' => 'shipping',
                );
                $shipping_order_item_id = wc_add_order_item($order_id, $shipping_order_item);
                if ($shipping_order_item_id) {
                    wc_add_order_item_meta($shipping_order_item_id, 'cost', $order_shipping['cost']);
                    wc_add_order_item_meta($shipping_order_item_id, 'total_tax', $shipping_tax);
                }
            }
            if (!empty($post['shipping_items'])) {
                foreach ($post['shipping_items'] as $key => $value) {
                    if ($shipping_order_item_id) {
                        wc_add_order_item_meta($shipping_order_item_id, $key, $value);
                    } else {
                        $shipping_order_item_id = wc_add_order_item($order_id, $shipping_order_item);
                        wc_add_order_item_meta($shipping_order_item_id, $key, $value);
                    }
                }
            }
            // create the fee order items
            if (!empty($post['fee_items'])) {
                if ($merging && $is_order_exist) {
                    $fee_str = 'fee';
                    $wpdb->query($wpdb->prepare("DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id and items.order_id = %d and items.order_item_type = %s", $order_id, $fee_str));
                }
                foreach ($post['fee_items'] as $key => $fee_item) {
                    $fee_order_item = array(
                        'order_item_name' => $fee_item['name'],
                        'order_item_type' => "fee"
                    );
                    $fee_order_item_id = wc_add_order_item($order_id, $fee_order_item);
                    if ($fee_order_item_id) {
                        wc_add_order_item_meta($fee_order_item_id, '_line_tax', $fee_item['tax']);
                        wc_add_order_item_meta($fee_order_item_id, '_line_total', $fee_item['total']);
                        wc_add_order_item_meta($fee_order_item_id, '_fee_amount', $fee_item['total']);
                        wc_add_order_item_meta($fee_order_item_id, '_line_tax_data', $fee_item['tax_data']);
                    }
                }
            }
            // create the tax order items
            if (!empty($post['tax_items'])) {
                if ($merging && $is_order_exist) {
                    $tax_str = 'tax';
                    $wpdb->query($wpdb->prepare("DELETE items,itemmeta FROM {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id and items.order_id = %d and items.order_item_type = %s", $order_id, $tax_str));
                }
                foreach ($post['tax_items'] as $tax_item) {
                    $tax_order_item = array(
                        'order_item_name' => $tax_item['title'],
                        'order_item_type' => "tax",
                    );
                    $tax_order_item_id = wc_add_order_item($order_id, $tax_order_item);
                    if ($tax_order_item_id) {
                        wc_add_order_item_meta($tax_order_item_id, 'rate_id', $tax_item['rate_id']);
                        wc_add_order_item_meta($tax_order_item_id, 'label', $tax_item['label']);
                        wc_add_order_item_meta($tax_order_item_id, 'compound', $tax_item['compound']);
                        wc_add_order_item_meta($tax_order_item_id, 'tax_amount', $tax_item['tax_amount']);
                        wc_add_order_item_meta($tax_order_item_id, 'shipping_tax_amount', $tax_item['shipping_tax_amount']);
                    }
                }
            }
            
            
            
            
             /*           //importing coupon items

             if (!empty($post['coupon_items'])) {
                if ($merging && $is_order_exist) {
                    $applied_coupons = $order->get_used_coupons();
                    if (!empty($applied_coupons)) {
                        foreach ($applied_coupons as $coupon) {
                            $order->remove_coupon($coupon);
                        }
                    }
                }
                $coupon_item = array();
                foreach ($post['coupon_items'] as $coupon) {
                    $_citem_meta = explode('|', $coupon);
                    $coupon_code = array_shift($_citem_meta);
                    $coupon_code = substr($coupon_code, strpos($coupon_code, ":") + 1);
                    $discount_amount = array_shift($_citem_meta);
                    $discount_amount = substr($discount_amount, strpos($discount_amount, ":") + 1);
                    if (WF_OrderImpExpCsv_Common_Utils::is_woocommerce_prior_to('2.7')) {
                        $mypost = get_page_by_title( $coupon_code, '' , 'shop_coupon' );
                        $id = (isset($mypost->ID) ? $mypost->ID : '');                        
                    } else {
                        $id = wc_get_coupon_id_by_code($coupon_code);
                    }
                    if ($id && $merging && $is_order_exist) {
                        $order->apply_coupon($coupon_code);
                    } else {
                        $coupon_item['order_item_name'] = $coupon_code;
                        $coupon_item['order_item_type'] = 'coupon';
                        $order_item_id = wc_add_order_item($order_id, $coupon_item);
                        wc_add_order_item_meta($order_item_id, 'discount_amount', $discount_amount);
                    }
                }
            }*/
            
            
            

            //importing coupon items
            if (!empty($post['coupon_items'])) {

                if (WF_OrderImpExpCsv_Common_Utils::is_woocommerce_prior_to('2.7')) {

                    if ($merging && $is_order_exist) {
                        $applied_coupons = $order->get_used_coupons();
                        if (!empty($applied_coupons)) {
                            $order->remove_order_items('coupon');
                        }
                    }

                    $coupon_item = array();
                    foreach ($post['coupon_items'] as $coupon) {

                        $_citem_meta = explode('|', $coupon);
                        $coupon_code = array_shift($_citem_meta);
                        $coupon_code = substr($coupon_code, strpos($coupon_code, ":") + 1);

                        $discount_amount = array_shift($_citem_meta);
                        $discount_amount = substr($discount_amount, strpos($discount_amount, ":") + 1);

                        $mypost = get_page_by_title($coupon_code, '', 'shop_coupon');
                        $id = (isset($mypost->ID) ? $mypost->ID : '');
  
                        if ($id && $merging && $is_order_exist) {
                            $order->add_coupon($coupon_code, $discount_amount);
                        } else {
                            $coupon_item['order_item_name'] = $coupon_code;
                            $coupon_item['order_item_type'] = 'coupon';
                            $order_item_id = wc_add_order_item($order_id, $coupon_item);
                            wc_add_order_item_meta($order_item_id, 'discount_amount', $discount_amount);
                        }
                    }
                } else {

                    if ($merging && $is_order_exist) {
                        $applied_coupons = $order->get_used_coupons();
                        if (!empty($applied_coupons)) {
                            foreach ($applied_coupons as $coupon) {
                                $order->remove_coupon($coupon);
                            }
                        }
                    }
                    $coupon_item = array();
                    foreach ($post['coupon_items'] as $coupon) {
                        $_citem_meta = explode('|', $coupon);
                        $coupon_code = array_shift($_citem_meta);
                        $coupon_code = substr($coupon_code, strpos($coupon_code, ":") + 1);
                        $discount_amount = array_shift($_citem_meta);
                        $discount_amount = substr($discount_amount, strpos($discount_amount, ":") + 1);

                        $id = wc_get_coupon_id_by_code($coupon_code);

                        if ($id && $merging && $is_order_exist) {
                            $order->apply_coupon($coupon_code);
                        } else {
                            $coupon_item['order_item_name'] = $coupon_code;
                            $coupon_item['order_item_type'] = 'coupon';
                            $order_item_id = wc_add_order_item($order_id, $coupon_item);
                            wc_add_order_item_meta($order_item_id, 'discount_amount', $discount_amount);
                        }
                    }
                }
            }

            // importing refund items
            if (!empty($post['refund_items'])) {
                if ($merging && $is_order_exist) {
                    $refund = 'shop_order_refund';
                    $wpdb->query($wpdb->prepare("DELETE po,pm FROM $wpdb->posts AS po INNER JOIN $wpdb->postmeta AS pm ON po.ID = pm.post_id WHERE post_parent = %d and post_type = %s", $order_id, $refund));
                }
                foreach ($post['refund_items'] as $refund) {
                    $single_refund = explode('|', $refund);
                    $amount = array_shift($single_refund);
                    $amount = substr($amount, strpos($amount, ":") + 1);
                    $reason = array_shift($single_refund);
                    $reason = substr($reason, strpos($reason, ":") + 1);
                    $date = array_shift($single_refund);
                    $date = substr($date, strpos($date, ":") + 1);

                    $args = array(
                        'amount' => $amount,
                        'reason' => $reason,
                        'date_created' => $date,
                        'order_id' => $order_id,
                    );
                    remove_all_actions('woocommerce_order_status_refunded_notification');
                    remove_all_actions('woocommerce_order_partially_refunded_notification');
                    remove_action('woocommerce_order_status_refunded', array('WC_Emails', 'send_transactional_email'));
                    remove_action('woocommerce_order_partially_refunded', array('WC_Emails', 'send_transactional_email'));
                    remove_action('woocommerce_order_fully_refunded', array('WC_Emails', 'send_transactional_email'));
                    wc_create_refund($args);
                }
            }

            // Grant downloadalbe product permissions
            if ($add_download_permissions) {
                wc_downloadable_product_permissions($order_id);
            }
            // add order notes
            if(!empty($post['notes'])){
                add_filter('woocommerce_email_enabled_customer_note', '__return_false');
                if ($merging && $is_order_exist) {
                    $wpdb->query($wpdb->prepare("DELETE comments,meta FROM {$wpdb->prefix}comments comments LEFT JOIN {$wpdb->prefix}commentmeta meta ON comments.comment_ID = meta.comment_id WHERE comments.comment_post_ID = %d",$order_id));
                }
            foreach ($post['notes'] as $order_note) {
                $note = explode('|', $order_note);
                $con = array_shift($note);
                $con = substr($con, strpos($con, ":") + 1);
                $date = array_shift($note);
                $date = substr($date, strpos($date, ":") + 1);
                $cus = array_shift($note);
                $cus = substr($cus, strpos($cus, ":") + 1);
                $system = array_shift($note);
                $added_by = substr($system, strpos($system, ":") + 1);
                if($added_by == 'system'){
                    $added_by_user = FALSE;
                }else{
                    $added_by_user = TRUE;
                }
                if($cus == '1'){
                    $comment_id = $order->add_order_note($con,1,1);
                } else {
                    $comment_id = $order->add_order_note($con,0,$added_by_user);
                }
                wp_update_comment(array('comment_ID' => $comment_id,'comment_date' => $date));
            }
            }
            // record the product sales
            (WC()->version < '2.7.0') ? $order->record_product_sales() : wc_update_total_sales_counts($_order_item_meta);
        } // ! dry run
        // was an original order number provided?
        if (!empty($post['order_number_formatted'])) {
            if (!$dry_run) {
                //Provide custom order number functionality , also allow 3rd party plugins to provide their own custom order number facilities
                do_action('woocommerce_set_order_number', $order, $post['order_number'], $post['order_number_formatted']);
                $order->add_order_note(sprintf(__("Original order #%s", 'wf_order_import_export'), $post['order_number_formatted']));
            }
            $this->processed_posts[$post['order_number_formatted']] = $post['order_number_formatted'];
        }
        if ($merging && !$new_added)
            $out_msg = 'Order updated successfully';
        else
            $out_msg = 'Order Imported Successfully.';
        $this->add_import_result('imported', __($out_msg, 'wf_order_import_export'), $order_id, isset($order_data['post_title']) ? $order_data['post_title'] : '', $order_id);
        $this->hf_order_log_data_change('hf-order-csv-import', sprintf(__('> &#8220;%s&#8221;' . $out_msg, 'wf_order_import_export'), esc_html($order_id), true));
        $this->imported++;
        $this->hf_order_log_data_change('hf-order-csv-import', __('> Finished importing order %s', 'wf_order_import_export'), $dry_run ? "" : $order->get_order_number() );
        $this->hf_order_log_data_change('hf-order-csv-import', __('Finished processing orders.', 'wf_order_import_export'));
        do_action('wt_orderimpexpcsv_order_import_after', $order_id, $post);
        unset($post);
    }

    public function hf_check_customer($data, $email_customer = false, $create_user) {
        $customer_email = (!empty($data['_customer_email']) ) ? $data['_customer_email'] : '';
        $found_customer = false;
        $username = (!empty($data['_customer_username']) ) ? $data['_customer_username'] : '';
        if (!empty($customer_email)) {
            if (is_email($customer_email) && false !== email_exists($customer_email)) {
                $found_customer = email_exists($customer_email);
                return $found_customer;
            } elseif (is_email($customer_email) && true == $create_user) {
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
                if (!empty($data['_customer_password'])) {
                    $password = $data['_customer_password'];
                } else {
                    $password = wp_generate_password(12, true);
                }
                $found_customer = wp_create_user($username, $password, $customer_email);
                if (!is_wp_error($found_customer)){
                    // update user meta data
                    foreach (self::$user_meta_fields as $key){
                        switch ($key) {
                            case '_billing_email':
                                // user billing email if set in csv otherwise use the user's account email
                                $meta_value = (!empty($data[$key])) ? $data[$key] : $customer_email;
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                break;

                            case '_billing_first_name':
                                $meta_value = (!empty($data[$key])) ? $data[$key] : $username;
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                update_user_meta($found_customer, 'first_name', $meta_value);
                                break;

                            case '_billing_last_name':
                                $meta_value = (!empty($data[$key])) ? $data[$key] : '';
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
                                $meta_value = (!empty($data[$key])) ? $data[$key] : '';

                                if (empty($meta_value)) {
                                    $n_key = str_replace('shipping', 'billing', $key);
                                    $meta_value = (!empty($data[$n_key])) ? $data[$n_key] : '';
                                }
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                                break;

                            default:
                                $meta_value = (!empty($data[$key])) ? $data[$key] : '';
                                $key = substr($key, 1);
                                update_user_meta($found_customer, $key, $meta_value);
                        }
                    }
                    $wp_user_object = new WP_User($found_customer);
                    $wp_user_object->set_role('customer');
                    // send user registration email if admin as chosen to do so
                    if ($email_customer && function_exists('wp_new_user_notification')) {
                        $previous_option = get_option('woocommerce_registration_generate_password');
                        // force the option value so that the password will appear in the email
                        update_option('woocommerce_registration_generate_password', 'yes');
                        do_action('woocommerce_created_customer', $found_customer, array('user_pass' => $password), true);
                        update_option('woocommerce_registration_generate_password', $previous_option);
                    }
                }
            } else {
                $found_customer = 0;
            }
        } else {
            $found_customer = 0;
        }
        return $found_customer;
    }

    /**
     * Log a row's import status
     */
    protected function add_import_result($status, $reason, $post_id = '', $post_title = '', $order_number = '') {
        $this->import_results[] = array(
            'post_title' => $post_id ? sprintf('<a href="%s">%s</a>', get_edit_post_link($post_id), $post_title) : $post_title,
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
        if ($enable_ftp_ie == false){
            $settings_in_db = get_option('hf_order_importer_ftp',null);
            $settings_in_db['enable_ftp_ie'] = false;
            update_option('hf_order_importer_ftp', $settings_in_db);
            return false;
        }
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

        $local_file = 'wp-content/plugins/order-import-export-for-woocommerce-pro/temp-import.csv';
//        $wp_upload_dir = wp_upload_dir();
//        $local_file = $wp_upload_dir['path'].'/ord-temp-import.csv';
        $server_file = $ftp_server_path;
        update_option('hf_order_importer_ftp', $settings);
        
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
                    file_put_contents(ABSPATH . $local_file, $file_contents);
                    $error_message = "";
                    $success = true;
                } else {
                    $error_message = "Failed to Download Specified file in sFTP Server File Path.<br/><br/><b>Possible Reasons</b><br/><b>1.</b> File path may be invalid.<br/><b>2.</b> Maybe File / Folder Permission missing for specified file or folder in path.<br/><b>3.</b> Write permission may be missing for file <b>plugins/order-import-export-for-woocommerce-pro/temp-import.csv</b> .\n";

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
                if (@ftp_get($ftp_conn,ABSPATH . $local_file, $server_file, FTP_BINARY)) {
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
    }

    // Close div.wrap
    public function footer() {
        echo '</div>';
    }

    /**
     * Display introductory text and file upload form
     */
    public function greet() {
        $action = 'admin.php?import=woocommerce_wf_order_csv&amp;step=1';
        $bytes = apply_filters('import_upload_size_limit', wp_max_upload_size());
        $size = size_format($bytes);
        $upload_dir = wp_upload_dir();
        $ftp_settings = get_option('hf_order_importer_ftp');
        include( 'views/html-wf-import-greeting.php' );
    }

    /**
     * Added to http_request_timeout filter to force timeout at 60 seconds during import
     * @return int 60
     */
    public function bump_request_timeout($val) {
        return 60;
    }

    public function hf_order_log_data_change($content = 'order-csv-import', $data = '') {
        if (WC()->version < '2.7.0') {
            $this->log->add($content, $data);
        } else {
            $context = array('source' => $content);
            $this->log->log("debug", $data, $context);
        }
    }

}
