<?php

class getsaleSettingsPage {
    public $options;
    public $settings_page_name = 'getsale_settings';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        $this->options = get_option('getsale_option_name');
    }

    public function add_plugin_page() {
        add_options_page('Settings Admin', 'getsale', 'manage_options', $this->settings_page_name, array($this, 'create_admin_page'));
    }

    public function create_admin_page() {
        $this->options = get_option('getsale_option_name');

        if ((isset($this->options['getsale_email'])) && ('' !== $this->options['getsale_email'])) {
            $email = $this->options['getsale_email'];
        } else $email = get_option('admin_email');

        ?>
        <script type="text/javascript">
            <?php include('main.js'); ?>
        </script>
        <div id="getsale_site_url" style="display: none"><?php echo get_site_url(); ?></div>
        <div class="wrap">
            <div id="wrapper">
                <form id="settings_form" method="post"
                      action="<?php echo $_SERVER['REQUEST_URI'] ?>">
                    <h1>Плагин getsale eCommerce</h1>
                    <?php
                    echo_before_text();
                    settings_fields('getsale_option_group');
                    do_settings_sections('getsale_settings');
                    ?>
                    <input type="submit" name="submit_btn" value="Cохранить изменения">
                </form>
            </div>
        </div>
        <?php
    }

    public function page_init() {
        register_setting('getsale_option_group', 'getsale_option_name', array($this, 'sanitize'));

        add_settings_section('setting_section_id', '', // Title
            array($this, 'print_section_info'), $this->settings_page_name);

        add_settings_field('email', 'Email', array($this, 'getsale_email_callback'), $this->settings_page_name, 'setting_section_id');

        add_settings_field('getsale_api_key', 'Ключ API', array($this, 'getsale_api_key_callback'), $this->settings_page_name, 'setting_section_id');

        add_settings_field('getsale_reg_error', 'getsale_reg_error', array($this, 'getsale_reg_error_callback'), $this->settings_page_name, 'setting_section_id');

        add_settings_field('getsale_project_id', 'getsale_project_id', array($this, 'getsale_project_id_callback'), $this->settings_page_name, 'setting_section_id');
    }

    public function sanitize($input) {
        $new_input = array();

        if (isset($input['getsale_email'])) $new_input['getsale_email'] = $input['getsale_email'];

        if (isset($input['getsale_project_id'])) $new_input['getsale_project_id'] = $input['getsale_project_id'];

        if (isset($input['getsale_api_key'])) $new_input['getsale_api_key'] = $input['getsale_api_key'];

        if (isset($input['getsale_reg_error'])) $new_input['getsale_reg_error'] = $input['getsale_reg_error'];

        return $new_input;
    }

    public function print_section_info() {
    }

    public function getsale_email_callback() {
        printf('<input type="text" id="getsale_email" name="getsale_option_name[getsale_email]" value="%s" title="Введите в данном поле Email, указанный при регистрации на сайте http://getsale.io"/>', isset($this->options['getsale_email']) ? esc_attr($this->options['getsale_email']) : '');
    }

    public function getsale_api_key_callback() {
        printf('<input type="text" id="getsale_api_key" name="getsale_option_name[getsale_api_key]" value="%s" title="Введите в данном поле Ключ API, полученный на сайте http://getsale.io" />', isset($this->options['getsale_api_key']) ? esc_attr($this->options['getsale_api_key']) : '');
    }

    public function getsale_reg_error_callback() {
        printf('<input type="text" id="getsale_reg_error" name="getsale_option_name[getsale_reg_error]" value="%s" />', isset($this->options['getsale_reg_error']) ? esc_attr($this->options['getsale_reg_error']) : '');
    }

    public function getsale_project_id_callback() {
        printf('<input type="text" id="getsale_project_id" name="getsale_option_name[getsale_project_id]" value="%s" />', isset($this->options['getsale_project_id']) ? esc_attr($this->options['getsale_project_id']) : '');
    }
}

function echo_before_text() {
    echo '<div id="before_install" style="display:none;">
Плагин Getsale успешно установлен!<br/>
Для начала работы плагина необходимо ввести Ключ API, полученный в личном кабинете на сайте <a href="http://getsale.io">GetSale.io</a>
</div>
<div class="wrap" id="after_install" style="display:none;">
<p><b>GetSale</b> — профессиональный инструмент для создания popup-окон.</p>
<p>GetSale поможет вашему сайту нарастить контактную базу лояльных клиентов, информировать посетителей о предстоящих акциях, распродажах, раздавать промокоды, скидки и многое другое, что напрямую повлияет на конверсии покупателей и рост продаж.</p>
</div>
</div>
<script type="text/javascript">
    window.onload = function ()
    {
        if (document.location.search == "?option=com_installer&view=install") {
            document.getElementById("before_install").style.display = "block"
        } else document.getElementById("after_install").style.display = "block"
    }
</script>';
}

function regbyApi($regDomain, $email, $key, $url) {
    $domain = $regDomain;
    if (($domain == '') OR ($email == '') OR ($key == '') OR ($url == '')) {
        return;
    }
    $ch = curl_init();
    $jsondata = json_encode(array('email' => $email, 'key' => $key, 'url' => $url, 'cms' => 'wordpress'));

    $options = array(CURLOPT_HTTPHEADER => array('Content-Type:application/json', 'Accept: application/json'), CURLOPT_URL => $domain . "/api/registration.json", CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $jsondata, CURLOPT_RETURNTRANSFER => true,);

    curl_setopt_array($ch, $options);
    $json_result = json_decode(curl_exec($ch));
    curl_close($ch);
    if (isset($json_result->status)) {
        if (($json_result->status == 'OK') && (isset($json_result->payload))) {
        } elseif ($json_result->status = 'error') {
        }
    }
    return $json_result;
}

function getsale_scripts_method() {
    $options = get_option('getsale_option_name');
    if ($options['getsale_project_id'] !== '') {
        wp_register_script('getsale_handle', plugins_url('js/main.js', dirname(__FILE__)), array('jquery'));

        $datatoBePassed = array('project_id' => $options['getsale_project_id']);
        wp_localize_script('getsale_handle', 'getsale_vars', $datatoBePassed);

        wp_enqueue_script('getsale_handle');
    }
}

function getsale_scripts_add() {
    $options = get_option('getsale_option_name');
    if ($options['getsale_project_id'] !== '') {
        wp_register_script('getsale_add', plugins_url('js/add.js', dirname(__FILE__)), array('jquery'));
        wp_enqueue_script('getsale_add');
    }
}

function getsale_scripts_del() {
    $options = get_option('getsale_option_name');
    if ($options['getsale_project_id'] !== '') {
        wp_register_script('getsale_add', plugins_url('js/del.js', dirname(__FILE__)), array('jquery'));
        wp_enqueue_script('getsale_add');
    }
}

function getsale_set_default_code() {
    $options = get_option('getsale_option_name');
    if (is_bool($options)) {
        $options = array();
        $options['getsale_email'] = '';
        $options['getsale_api_key'] = '';
        $options['getsale_project_id'] = '';
        $options['getsale_reg_error'] = '';
        update_option('getsale_option_name', $options);
    }
}
