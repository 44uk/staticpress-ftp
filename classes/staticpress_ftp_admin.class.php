<?php
class StaticPress_FTPAdmin {
    const TEXT_DOMAIN  = 'staticpress_ftp';
    const OPTION_KEY   = 'staticpress_ftp';
    const OPTION_PAGE  = 'staticpress_ftp';
    const NONCE_ACTION = 'ftp_update_options';
    const NONCE_NAME   = '_wpnonce_ftp_update_options';

    private $options = array();
    private $plugin_basename;
    private $admin_hook, $admin_action;

    static public function option_keys(){
        return array(
            'disabled' => __('Disabled', self::TEXT_DOMAIN),
            'host' => __('Host', self::TEXT_DOMAIN),
            'user' => __('User', self::TEXT_DOMAIN),
            'pass' => __('Pass', self::TEXT_DOMAIN),
            'pasv' => __('use PASV', self::TEXT_DOMAIN),
            'ssl'  => __('use SSL' , self::TEXT_DOMAIN),
        );
    }

    static public function get_options(){
        $options = get_option(self::OPTION_KEY);
        foreach (array_keys(self::option_keys()) as $key) {
            if (! isset($options[$key]) || is_wp_error($options[$key])) {
                $options[$key] = '';
            }
        }
        return $options;
    }

    function __construct(){
        $this->options = $this->get_options();
        $this->plugin_basename = StaticPress_FTP::plugin_basename();
        add_action('StaticPress::options_save', array($this, 'options_save'));
        add_action('StaticPress::options_page', array($this, 'options_page'));
    }

    /*
     * Add Admin Menu
     */
    public function options_save(){
        $option_keys   = $this->option_keys();
        $this->options = $this->get_options();

        $iv = new InputValidator('POST');
        $iv->set_rules(self::NONCE_NAME, 'required');

        if (
            ! is_wp_error($iv->input(self::NONCE_NAME))
            && check_admin_referer(self::NONCE_ACTION, self::NONCE_NAME)
        ) {
            $fields = array_keys($option_keys);
            foreach ($fields as $field) {
                switch ($field) {
                case 'host':
                case 'user':
                    $iv->set_rules($field, array('trim','esc_html','required'));
                    break;
                default:
                    $iv->set_rules($field, array('trim','esc_html'));
                    break;
                }
            }

            $options = $iv->input($fields);
            $err_message = '';
            foreach ($option_keys as $key => $field) {
                if (is_wp_error($options[$key])) {
                    $error_data = $options[$key];
                    $err = '';
                    foreach ($error_data->errors as $errors) {
                        foreach ($errors as $error) {
                            $err .= (!empty($err) ? '<br />' : '') . __('Error! : ', self::TEXT_DOMAIN);
                            $err .= sprintf(
                                __(str_replace($key, '%s', $error), self::TEXT_DOMAIN),
                                $field
                            );
                        }
                    }
                    $err_message .= (!empty($err_message) ? '<br />' : '') . $err;
                }

                if (! isset($options[$key]) || is_wp_error($options[$key])) {
                    $options[$key] = '';
                }
            }

            if (function_exists('dbgx_trace_var')) {
                dbgx_trace_var($options);
            }

            if ($this->options !== $options) {
                update_option(self::OPTION_KEY, $options);
                printf(
                    '<div id="message" class="updated fade"><p><strong>%s</strong></p></div>'."\n",
                    empty($err_message) ? __('Done!', self::TEXT_DOMAIN) : $err_message
                );
                $this->options = $options;
            }
            unset($options);
        }
    }

    public function options_page(){
        $option_keys   = $this->option_keys();
        $this->options = $this->get_options();
        $title     = __('StaticPress FTP Options', self::TEXT_DOMAIN);
        $sub_title = __('Server Settings', self::TEXT_DOMAIN);
?>
<div class="wrap">
    <h2><?php echo esc_html( $title ) ?></h2>
    <form method="post" action="<?php echo $this->admin_action ?>">
        <?php echo wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME, true, false) ?>
        <table class="wp-list-table fixed"><tbody>
            <tr><td colspan="2"><h3><?php echo esc_html($sub_title) ?></h3></td></tr>
            <?php foreach ($option_keys as $field => $label){ $this->input_field($field, $label); } ?>
        </tbody></table>
        <?php submit_button() ?>
    </form>
</div>
<?php
    }

    private function input_field($field, $label, $args = array()){
        extract($args);
        $label = sprintf('<th><label for="%1$s">%2$s</label></th>', $field, $label);
        switch ($field) {
        case 'disabled':
        case 'pasv':
        case 'ssl':
            $is_checked  = (bool) $this->options[$field];
            $input_field = sprintf('<td><input type="checkbox" name="%1$s" %2$s id="%1$s" size="100" /></td>',
                $field,
                ($is_checked ? 'checked="checked"' : '')
            );
            break;
        default:
            $input_field = sprintf('<td><input type="text" name="%1$s" value="%2$s" id="%1$s" size="100" /></td>',
                $field,
                esc_attr($this->options[$field])
            );
            break;
        }
        echo "<tr>{$label}{$input_field}</tr>";
    }
}
