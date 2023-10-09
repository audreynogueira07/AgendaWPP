<?php
/*
Plugin Name: AgendaWPP
Description: Plugin para agendar datas e enviar mensagem via WhatsApp.
Version: 1.0
Author: <a href="https://www.linkedin.com/in/audreynnogueira">Audrey Nogueira</a>
*/

// Cria a tabela no banco de dados ao ativar o plugin
function ws_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ws_agendamentos';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'ws_create_table');

// Registrar scripts e estilos
function ws_enqueue_scripts() {
    wp_enqueue_style('daterangepicker', plugin_dir_url(__FILE__) . 'css/daterangepicker.css');
    wp_enqueue_script('moment', plugin_dir_url(__FILE__) . 'js/moment.min.js', array('jquery'), null, true);
    wp_enqueue_script('daterangepicker', plugin_dir_url(__FILE__) . 'js/daterangepicker.js', array('jquery', 'moment'), null, true);
    wp_enqueue_script('ws-script', plugin_dir_url(__FILE__) . 'js/ws-script.js', array('daterangepicker'), null, true);

    wp_localize_script('ws-script', 'ws_params', array(
        'ws_whatsapp_number' => get_option('ws_whatsapp_number'),
        'ws_whatsapp_message' => get_option('ws_whatsapp_message', 'Mensagem padrão')
    ));
}
add_action('wp_enqueue_scripts', 'ws_enqueue_scripts');

// Shortcode para exibir o botão e o calendário
function ws_shortcode() {
    ob_start();
    ?>
    <style>
        #schedule-button {
            background-color: #0073aa;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        #schedule-button:hover {
            background-color: #005a87;
        }
        #date-range {
            padding: 12px;
            border: 2px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
    </style>
    
    <input type="text" id="date-range" readonly>
    <button id="schedule-button">Agendar</button>
    <?php
    return ob_get_clean();
}
add_shortcode('whatsapp_scheduler', 'ws_shortcode');


// Adiciona uma página de opções ao menu principal
function ws_add_options_page() {
    add_menu_page(
        'Configurações do Agendamento',
        'AgendaWPP',
        'manage_options',
        'agendamento',
        'ws_render_options_page',
        'dashicons-calendar'  // Ícone de calendário
    );
}
add_action('admin_menu', 'ws_add_options_page');

// Renderiza a página de opções
function ws_render_options_page() {
    $ws_whatsapp_message = get_option('ws_whatsapp_message', 'Olá, quero agendar {date_start} até {date_end}');
    ?>
    <div class="wrap">
        <h1>Configurações do Agendamento</h1>
        <p>Para usar o formulário de agendamento no seu site, insira o seguinte shortcode onde desejar: <code>[whatsapp_scheduler]</code></p>
        <form method="post" action="options.php">
            <?php
            settings_fields('ws_options_group');
            do_settings_sections('agendamento');
            ?>
            <h3>Mensagem para WhatsApp</h3>
            <textarea name="ws_whatsapp_message" rows="4" cols="50"><?php echo esc_attr($ws_whatsapp_message); ?></textarea>
            <p class="description">
              Use {date_start} para a data de início e {date_end} para a data de término.
            </p>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
// Registra as configurações
function ws_register_settings() {
    register_setting('ws_options_group', 'ws_whatsapp_number');
    register_setting('ws_options_group', 'ws_whatsapp_message');

    add_settings_section(
        'ws_general_section',
        'Configurações Gerais',
        'ws_general_section_callback',
        'agendamento'
    );

    add_settings_field(
        'ws_whatsapp_number',
        'Número do WhatsApp',
        'ws_render_whatsapp_number_field',
        'agendamento',
        'ws_general_section'
    );
    add_settings_field(
        'ws_whatsapp_message',
        'ws_render_whatsapp_message_field',
        'agendamento',
        'ws_general_section'
    );
}
add_action('admin_init', 'ws_register_settings');

// Callback para a seção geral
function ws_general_section_callback() {
    echo '<p>Configurações gerais para o plugin de Agendamento.</p>';
}

// Renderiza o campo do número do WhatsApp
function ws_render_whatsapp_number_field() {
    $ws_whatsapp_number = get_option('ws_whatsapp_number');
    echo '<input type="text" name="ws_whatsapp_number" value="' . esc_attr($ws_whatsapp_number) . '" />';
}

// Renderiza o campo da mensagem do WhatsApp
function ws_render_whatsapp_message_field() {
    $ws_whatsapp_message = get_option('ws_whatsapp_message', 'Mensagem padrão');
    echo '<input type="text" name="ws_whatsapp_message" value="' . esc_attr($ws_whatsapp_message) . '" />';
}

?>
