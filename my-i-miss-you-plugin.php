<?php
/*
Plugin Name: I Miss You
Description: Плагин для изменения заголовка и фавикона страницы, когда пользователь не просматривает ваш сайт.
Version: 1.6
Author: RuCoder
*/

// Подключаем jQuery
function imissyou_enqueue_scripts() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'imissyou_enqueue_scripts');

// Добавляем страницу настроек в меню
function imissyou_add_admin_menu() {
    add_options_page('Настройки I Miss You', 'I Miss You', 'manage_options', 'imiss_you', 'imissyou_options_page');
}
add_action('admin_menu', 'imissyou_add_admin_menu');

// Регистрация настроек
function imissyou_settings_init() {
    register_setting('imissyou', 'imissyou_options');

    add_settings_section(
        'imissyou_section_developers',
        __('Настройки плагина', 'wordpress'),
        null,
        'imissyou'
    );

    add_settings_field(
        'imissyou_text',
        __('Текст возврата', 'wordpress'),
        'imissyou_text_render',
        'imissyou',
        'imissyou_section_developers'
    );

    add_settings_field(
        'imissyou_favicon',
        __('URL фавикона', 'wordpress'),
        'imissyou_favicon_render',
        'imissyou',
        'imissyou_section_developers'
    );

    add_settings_field(
        'imissyou_mode',
        __('Выберите режим', 'wordpress'),
        'imissyou_mode_render',
        'imissyou',
        'imissyou_section_developers'
    );

    add_settings_field(
        'imissyou_blinking_text',
        __('Мерцающий текст', 'wordpress'),
        'imissyou_blinking_text_render',
        'imissyou',
        'imissyou_section_developers'
    );
}
add_action('admin_init', 'imissyou_settings_init');

function imissyou_text_render() {
    $options = get_option('imissyou_options');
    ?>
    <input type='text' name='imissyou_options[imissyou_text]' value='<?php echo isset($options['imissyou_text']) ? esc_attr($options['imissyou_text']) : ''; ?>'>
    <p class="description">Этот текст будет отображаться, когда пользователь покинет сайт.</p>
    <?php
}

function imissyou_favicon_render() {
    $options = get_option('imissyou_options');
    ?>
    <input type='text' name='imissyou_options[imissyou_favicon]' value='<?php echo isset($options['imissyou_favicon']) ? esc_attr($options['imissyou_favicon']) : ''; ?>'>
    <p class="description">URL вашего фавикона. Например: https://example.com/favicon.ico. Фавикон также будет меняться, когда вкладка неактивна.</p>
    <?php
}

function imissyou_mode_render() {
    $options = get_option('imissyou_options');
    ?>
    <select name='imissyou_options[imissyou_mode]'>
        <option value='none' <?php selected($options['imissyou_mode'], 'none'); ?>>Выберите режим</option>
        <option value='return' <?php selected($options['imissyou_mode'], 'return'); ?>>Текст возврата</option>
        <option value='blinking' <?php selected($options['imissyou_mode'], 'blinking'); ?>>Мерцающий текст</option>
    </select>
    <?php
}

function imissyou_blinking_text_render() {
    $options = get_option('imissyou_options');
    ?>
    <input type='text' name='imissyou_options[imissyou_blinking_text]' value='<?php echo isset($options['imissyou_blinking_text']) ? esc_attr($options['imissyou_blinking_text']) : ''; ?>'>
    <p class="description">Этот текст будет использоваться для мерцания в заголовке страницы.</p>
    <?php
}

function imissyou_options_page() {
    ?>
    <form action='options.php' method='post'>
        <h2>Настройки плагина I Miss You</h2>
        <?php
        settings_fields('imissyou');
        do_settings_sections('imissyou');
        submit_button();
        ?>
    </form>
    <h3>Описание режимов отображения текста:</h3>
    <ul>
        <li><strong>Текст возврата:</strong> Заголовок страницы будет изменен на заданный текст, когда пользователь покинет сайт.</li>
        <li><strong>Мерцающий текст:</strong> Заголовок страницы будет мерцать между оригинальным текстом и заданным текстом.</li>
    </ul>
    <?php
}

function imissyou_footer_script() {
    $options = get_option('imissyou_options');
    $custom_title = !empty($options['imissyou_text']) ? $options['imissyou_text'] : get_bloginfo('name');
    $favicon_url = !empty($options['imissyou_favicon']) ? $options['imissyou_favicon'] : plugins_url('iMissYouFavicon.ico', __FILE__);

    ?>
    <script>
        jQuery(document).ready(function($) {
            var originalTitle = document.title;
            var originalFavicon = $("link[rel='icon']").attr("href"); // Сохраняем оригинальный фавикон
            var mode = "<?php echo esc_js($options['imissyou_mode']); ?>";

            if (mode === 'return') {
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        document.title = "<?php echo esc_js($custom_title); ?>";
                        $("link[rel='icon']").attr("href", "<?php echo esc_url($favicon_url); ?>");
                    } else {
                        document.title = originalTitle;
                        $("link[rel='icon']").attr("href", originalFavicon); // Восстанавливаем оригинальный фавикон
                    }
                });
            } else if (mode === 'blinking') {
                setInterval(function() {
                    document.title = document.title === "<?php echo esc_js($options['imissyou_blinking_text']); ?>" ? originalTitle : "<?php echo esc_js($options['imissyou_blinking_text']); ?>";
                    $("link[rel='icon']").attr("href", "<?php echo esc_url($favicon_url); ?>");
                }, 500);
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'imissyou_footer_script');

function imissyou_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=imiss_you') . '">Настройки</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'imissyou_plugin_action_links');
