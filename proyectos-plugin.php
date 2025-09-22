<?php
/**
 * Plugin Name: Proyectos Grid
 * Description: Plugin personalizado para mostrar proyectos en una grilla responsive con administración completa.
 * Version: 1.0.0
 * Author: José Vega
 * Text Domain: proyectos-grid
 * Domain Path: /languages
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('PROYECTOS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PROYECTOS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PROYECTOS_VERSION', '1.0.0');

class ProyectosPlugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_shortcode('proyectos_grid', array($this, 'proyectos_grid_shortcode'));
        add_shortcode('proyectos_form', array($this, 'proyectos_form_shortcode'));
        
        // Hook para activación del plugin
        register_activation_hook(__FILE__, array($this, 'activate'));
    }
    
    public function init() {
        // Registrar Custom Post Type
        $this->register_post_type();
        
        // Registrar taxonomías
        $this->register_taxonomies();
        
        // Cargar traducciones
        load_plugin_textdomain('proyectos-grid', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function activate() {
        $this->register_post_type();
        $this->register_taxonomies();
        flush_rewrite_rules();
    }
    
    private function register_post_type() {
        $labels = array(
            'name' => __('Proyectos', 'proyectos-grid'),
            'singular_name' => __('Proyecto', 'proyectos-grid'),
            'menu_name' => __('Proyectos', 'proyectos-grid'),
            'add_new' => __('Añadir Nuevo', 'proyectos-grid'),
            'add_new_item' => __('Añadir Nuevo Proyecto', 'proyectos-grid'),
            'edit_item' => __('Editar Proyecto', 'proyectos-grid'),
            'new_item' => __('Nuevo Proyecto', 'proyectos-grid'),
            'view_item' => __('Ver Proyecto', 'proyectos-grid'),
            'search_items' => __('Buscar Proyectos', 'proyectos-grid'),
            'not_found' => __('No se encontraron proyectos', 'proyectos-grid'),
            'not_found_in_trash' => __('No se encontraron proyectos en la papelera', 'proyectos-grid')
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'proyecto'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-portfolio',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest' => true
        );
        
        register_post_type('proyecto', $args);
    }
    
    private function register_taxonomies() {
        add_action('proyecto_categoria_add_form_fields', array($this, 'add_category_fields'));
        add_action('proyecto_categoria_edit_form_fields', array($this, 'edit_category_fields'));
        add_action('created_proyecto_categoria', array($this, 'save_category_fields'));
        add_action('edited_proyecto_categoria', array($this, 'save_category_fields'));
        
        // Taxonomía jerárquica (Categorías)
        $category_labels = array(
            'name' => __('Categorías de Proyecto', 'proyectos-grid'),
            'singular_name' => __('Categoría de Proyecto', 'proyectos-grid'),
            'search_items' => __('Buscar Categorías', 'proyectos-grid'),
            'all_items' => __('Todas las Categorías', 'proyectos-grid'),
            'parent_item' => __('Categoría Padre', 'proyectos-grid'),
            'parent_item_colon' => __('Categoría Padre:', 'proyectos-grid'),
            'edit_item' => __('Editar Categoría', 'proyectos-grid'),
            'update_item' => __('Actualizar Categoría', 'proyectos-grid'),
            'add_new_item' => __('Añadir Nueva Categoría', 'proyectos-grid'),
            'new_item_name' => __('Nuevo Nombre de Categoría', 'proyectos-grid'),
            'menu_name' => __('Categorías', 'proyectos-grid')
        );
        
        register_taxonomy('proyecto_categoria', 'proyecto', array(
            'hierarchical' => true,
            'labels' => $category_labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'categoria-proyecto'),
            'show_in_rest' => true
        ));
        
        // Taxonomía no jerárquica (Etiquetas/Tags)
        $tag_labels = array(
            'name' => __('Etiquetas de Proyecto', 'proyectos-grid'),
            'singular_name' => __('Etiqueta de Proyecto', 'proyectos-grid'),
            'search_items' => __('Buscar Etiquetas', 'proyectos-grid'),
            'all_items' => __('Todas las Etiquetas', 'proyectos-grid'),
            'edit_item' => __('Editar Etiqueta', 'proyectos-grid'),
            'update_item' => __('Actualizar Etiqueta', 'proyectos-grid'),
            'add_new_item' => __('Añadir Nueva Etiqueta', 'proyectos-grid'),
            'new_item_name' => __('Nuevo Nombre de Etiqueta', 'proyectos-grid'),
            'menu_name' => __('Etiquetas', 'proyectos-grid')
        );
        
        register_taxonomy('proyecto_etiqueta', 'proyecto', array(
            'hierarchical' => false,
            'labels' => $tag_labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'etiqueta-proyecto'),
            'show_in_rest' => true
        ));
    }
    
    public function add_category_fields() {
        ?>
        <div class="form-field">
            <label for="nombre_singular"><?php _e('Nombre Singular', 'proyectos-grid'); ?></label>
            <input type="text" name="nombre_singular" id="nombre_singular" value="" />
            <p class="description"><?php _e('Nombre singular para usar en formularios (ej: "curso", "terapia"). Si está vacío, se usará el título de la categoría.', 'proyectos-grid'); ?></p>
        </div>
        <?php
    }
    
    public function edit_category_fields($term) {
        $nombre_singular = get_term_meta($term->term_id, 'nombre_singular', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="nombre_singular"><?php _e('Nombre Singular', 'proyectos-grid'); ?></label>
            </th>
            <td>
                <input type="text" name="nombre_singular" id="nombre_singular" value="<?php echo esc_attr($nombre_singular); ?>" />
                <p class="description"><?php _e('Nombre singular para usar en formularios (ej: "curso", "terapia"). Si está vacío, se usará el título de la categoría.', 'proyectos-grid'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    public function save_category_fields($term_id) {
        if (isset($_POST['nombre_singular'])) {
            update_term_meta($term_id, 'nombre_singular', sanitize_text_field($_POST['nombre_singular']));
        }
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=proyecto',
            __('Configuración de Proyectos', 'proyectos-grid'),
            __('Configuración', 'proyectos-grid'),
            'manage_options',
            'proyectos-config',
            array($this, 'admin_config_page')
        );
    }
    
    public function admin_config_page() {
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['proyectos_config_nonce'], 'proyectos_config_action')) {
            update_option('proyectos_moneda_global', sanitize_text_field($_POST['moneda_global']));
            update_option('proyectos_enlace_base', esc_url_raw($_POST['enlace_base']));
            update_option('proyectos_email_receptores', sanitize_textarea_field($_POST['email_receptores']));
            update_option('proyectos_email_template', sanitize_textarea_field($_POST['email_template']));
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada correctamente.', 'proyectos-grid') . '</p></div>';
        }
        
        $moneda_global = get_option('proyectos_moneda_global', 'CLP');
        $enlace_base = get_option('proyectos_enlace_base', home_url('/contacto'));
        $email_receptores = get_option('proyectos_email_receptores', get_option('admin_email'));
        $email_template = get_option('proyectos_email_template', "Nuevo mensaje de contacto:\n\nNombre: [nombre]\nApellido: [apellido]\nTeléfono: [telefono]\nEmail: [email]\nInterés Principal: [interes_principal]\nModalidad: [modalidad]\nProyecto Específico: [proyecto_especifico]\nMotivación: [motivacion]");
        
        ?>
        <div class="wrap">
            <h1><?php _e('Configuración de Proyectos', 'proyectos-grid'); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('proyectos_config_action', 'proyectos_config_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="moneda_global"><?php _e('Moneda Global', 'proyectos-grid'); ?></label>
                        </th>
                        <td>
                            <select name="moneda_global" id="moneda_global">
                                <option value="USD" <?php selected($moneda_global, 'USD'); ?>>USD</option>
                                <option value="EUR" <?php selected($moneda_global, 'EUR'); ?>>EUR</option>
                                <option value="CLP" <?php selected($moneda_global, 'CLP'); ?>>CLP</option>
                                <option value="ARS" <?php selected($moneda_global, 'ARS'); ?>>ARS</option>
                                <option value="MXN" <?php selected($moneda_global, 'MXN'); ?>>MXN</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="enlace_base"><?php _e('Enlace Base Global', 'proyectos-grid'); ?></label>
                        </th>
                        <td>
                            <input type="url" name="enlace_base" id="enlace_base" value="<?php echo esc_attr($enlace_base); ?>" class="regular-text" />
                            <p class="description"><?php _e('Ejemplo: https://misitio.com/contacto', 'proyectos-grid'); ?></p>
                        </td>
                    </tr>
                    <!-- Adding email receptors configuration field -->
                    <tr>
                        <th scope="row">
                            <label for="email_receptores"><?php _e('Correos Receptores', 'proyectos-grid'); ?></label>
                        </th>
                        <td>
                            <textarea name="email_receptores" id="email_receptores" rows="3" class="large-text"><?php echo esc_textarea($email_receptores); ?></textarea>
                            <p class="description"><?php _e('Ingresa los correos donde se enviarán los formularios. Separa múltiples correos con comas. Ejemplo: admin@sitio.com, ventas@sitio.com', 'proyectos-grid'); ?></p>
                        </td>
                    </tr>
                    <!-- Adding email template configuration field -->
                    <tr>
                        <th scope="row">
                            <label for="email_template"><?php _e('Plantilla de Correo', 'proyectos-grid'); ?></label>
                        </th>
                        <td>
                            <textarea name="email_template" id="email_template" rows="10" class="large-text"><?php echo esc_textarea($email_template); ?></textarea>
                            <p class="description">
                                <?php _e('Plantilla para el correo que se enviará. Usa los siguientes placeholders:', 'proyectos-grid'); ?><br>
                                <strong>[nombre]</strong> - <?php _e('Nombre del usuario', 'proyectos-grid'); ?><br>
                                <strong>[apellido]</strong> - <?php _e('Apellido del usuario', 'proyectos-grid'); ?><br>
                                <strong>[telefono]</strong> - <?php _e('Teléfono del usuario', 'proyectos-grid'); ?><br>
                                <strong>[email]</strong> - <?php _e('Email del usuario', 'proyectos-grid'); ?><br>
                                <strong>[interes_principal]</strong> - <?php _e('Categoría seleccionada', 'proyectos-grid'); ?><br>
                                <strong>[modalidad]</strong> - <?php _e('Etiqueta/modalidad seleccionada', 'proyectos-grid'); ?><br>
                                <strong>[proyecto_especifico]</strong> - <?php _e('Proyecto específico seleccionado', 'proyectos-grid'); ?><br>
                                <strong>[motivacion]</strong> - <?php _e('Mensaje de motivación', 'proyectos-grid'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'proyecto_detalles',
            __('Detalles del Proyecto', 'proyectos-grid'),
            array($this, 'proyecto_detalles_callback'),
            'proyecto',
            'normal',
            'high'
        );
    }
    
    public function proyecto_detalles_callback($post) {
        wp_nonce_field('proyecto_detalles_nonce', 'proyecto_detalles_nonce_field');
        
        $valor = get_post_meta($post->ID, '_proyecto_valor', true);
        $moneda_individual = get_post_meta($post->ID, '_proyecto_moneda', true);
        $enlace_personalizado = get_post_meta($post->ID, '_proyecto_enlace_personalizado', true);
        $menu_order = get_post_meta($post->ID, '_proyecto_orden', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="proyecto_valor"><?php _e('Valor', 'proyectos-grid'); ?></label>
                </th>
                <td>
                    <input type="number" name="proyecto_valor" id="proyecto_valor" value="<?php echo esc_attr($valor); ?>" step="0.01" min="0" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="proyecto_moneda"><?php _e('Moneda (opcional)', 'proyectos-grid'); ?></label>
                </th>
                <td>
                    <select name="proyecto_moneda" id="proyecto_moneda">
                        <option value=""><?php _e('Usar moneda global', 'proyectos-grid'); ?></option>
                        <option value="USD" <?php selected($moneda_individual, 'USD'); ?>>USD</option>
                        <option value="EUR" <?php selected($moneda_individual, 'EUR'); ?>>EUR</option>
                        <option value="CLP" <?php selected($moneda_individual, 'CLP'); ?>>CLP</option>
                        <option value="ARS" <?php selected($moneda_individual, 'ARS'); ?>>ARS</option>
                        <option value="MXN" <?php selected($moneda_individual, 'MXN'); ?>>MXN</option>
                    </select>
                    <p class="description"><?php _e('Deja vacío para usar la moneda global configurada.', 'proyectos-grid'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="proyecto_enlace_personalizado"><?php _e('Enlace Personalizado', 'proyectos-grid'); ?></label>
                </th>
                <td>
                    <input type="url" name="proyecto_enlace_personalizado" id="proyecto_enlace_personalizado" value="<?php echo esc_attr($enlace_personalizado); ?>" class="regular-text" />
                    <p class="description"><?php _e('Deja vacío para usar el enlace base global con el parámetro service.', 'proyectos-grid'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="proyecto_orden"><?php _e('Orden en el menú', 'proyectos-grid'); ?></label>
                </th>
                <td>
                    <input type="number" name="proyecto_orden" id="proyecto_orden" value="<?php echo esc_attr($menu_order); ?>" min="0" step="1" />
                    <p class="description"><?php _e('Los proyectos se ordenarán de menor a mayor número. Deja vacío para orden por fecha.', 'proyectos-grid'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function save_meta_boxes($post_id) {
        if (!isset($_POST['proyecto_detalles_nonce_field']) || !wp_verify_nonce($_POST['proyecto_detalles_nonce_field'], 'proyecto_detalles_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['proyecto_valor'])) {
            update_post_meta($post_id, '_proyecto_valor', floatval($_POST['proyecto_valor']));
        }
        
        if (isset($_POST['proyecto_moneda'])) {
            update_post_meta($post_id, '_proyecto_moneda', sanitize_text_field($_POST['proyecto_moneda']));
        }
        
        if (isset($_POST['proyecto_enlace_personalizado'])) {
            update_post_meta($post_id, '_proyecto_enlace_personalizado', esc_url_raw($_POST['proyecto_enlace_personalizado']));
        }
        
        if (isset($_POST['proyecto_orden'])) {
            update_post_meta($post_id, '_proyecto_orden', intval($_POST['proyecto_orden']));
        }
    }
    
    public function enqueue_frontend_styles() {
        wp_enqueue_style('proyectos-grid-style', PROYECTOS_PLUGIN_URL . 'assets/style.css', array(), PROYECTOS_VERSION);
        
        wp_enqueue_script('proyectos-form-script', PROYECTOS_PLUGIN_URL . 'assets/form-script.js', array('jquery'), PROYECTOS_VERSION, true);
        
        wp_localize_script('proyectos-form-script', 'proyectos_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('proyectos_form_nonce')
        ));
    }
    
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        if ($post_type == 'proyecto') {
            wp_enqueue_style('proyectos-admin-style', PROYECTOS_PLUGIN_URL . 'assets/admin-style.css', array(), PROYECTOS_VERSION);
        }
    }
    
    public function proyectos_grid_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'posts_per_page' => -1
        ), $atts, 'proyectos_grid');
        
        $args = array(
            'post_type' => 'proyecto',
            'post_status' => 'publish',
            'posts_per_page' => intval($atts['posts_per_page']),
            'orderby' => array(
                'menu_order' => 'ASC',
                'date' => 'DESC'
            )
        );
        
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'proyecto_categoria',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($atts['category'])
                )
            );
        }
        
        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            return '<p>' . __('No se encontraron proyectos.', 'proyectos-grid') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="proyectos-grid">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php echo $this->render_proyecto_card(get_the_ID()); ?>
            <?php endwhile; ?>
        </div>
        <?php
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    public function proyectos_form_shortcode($atts) {
        $atts = shortcode_atts(array(), $atts, 'proyectos_form');
        
        // Get URL parameters
        $service_param = isset($_GET['service']) ? sanitize_text_field($_GET['service']) : '';
        $category_param = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $tag_param = isset($_GET['tag']) ? sanitize_text_field($_GET['tag']) : '';
        
        ob_start();
        ?>
        <div class="proyectos-form-container">
            <form method="post" action="" id="proyectos-form">
                <!-- Separating basic fields into individual form-row divs for proper 2-column layout -->
                <div class="proyectos-form">
                    <div class="form-row">
                        <div class="form-field">
                            <label for="nombre"><?php _e('Nombre', 'proyectos-grid'); ?> *</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <label for="apellido"><?php _e('Apellido', 'proyectos-grid'); ?> *</label>
                            <input type="text" id="apellido" name="apellido" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <label for="telefono"><?php _e('Teléfono', 'proyectos-grid'); ?> *</label>
                            <input type="tel" id="telefono" name="telefono" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <label for="email"><?php _e('Email', 'proyectos-grid'); ?> *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <!-- Separating conditional fields into individual form-row divs so they appear side by side -->
                    <div class="form-row">
                        <div class="form-field">
                            <label for="interes_principal"><?php _e('Interés Principal', 'proyectos-grid'); ?> *</label>
                            <select id="interes_principal" name="interes_principal" required>
                                <option value=""><?php _e('Selecciona una categoría', 'proyectos-grid'); ?></option>
                                <?php
                                $categorias = get_terms(array(
                                    'taxonomy' => 'proyecto_categoria',
                                    'hide_empty' => true
                                ));
                                foreach ($categorias as $categoria) {
                                    $selected = ($categoria->slug === $category_param) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($categoria->slug) . '" ' . $selected . '>' . esc_html($categoria->name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Project field with full-width class to span both columns -->
                    <div class="form-row">
                        <div class="form-field full-width" id="proyecto-field" style="display: none;">
                            <label for="proyecto_especifico" id="proyecto-label"><?php _e('Proyecto Específico', 'proyectos-grid'); ?></label>
                            <select id="proyecto_especifico" name="proyecto_especifico">
                                <option value=""><?php _e('Selecciona un proyecto', 'proyectos-grid'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- Textarea with full-width class to span both columns -->
                <div class="form-row">
                    <div class="form-field full-width">
                        <label for="motivacion"><?php _e('Motivación', 'proyectos-grid'); ?></label>
                        <textarea id="motivacion" name="motivacion" rows="4" placeholder="<?php _e('Cuéntame brevemente ¿Qué te motiva a buscar esta experiencia, terapia, producto?', 'proyectos-grid'); ?>"></textarea>
                    </div>
                </div>
                
                <!-- Submit button with full-width class to span both columns -->
                <div class="form-row">
                    <div class="form-field full-width">
                        <button type="submit" class="btn-enviar"><?php _e('Enviar Consulta', 'proyectos-grid'); ?></button>
                    </div>
                </div>
            </form>
            
            <!-- Added loader overlay for AJAX requests -->
            <div id="form-loader" class="form-loader" style="display: none;">
                <div class="loader-content">
                    <div class="spinner"></div>
                    <p><?php _e('Cargando...', 'proyectos-grid'); ?></p>
                </div>
            </div>
            
            <!-- Added success message container -->
            <div id="form-success-message" class="form-success-message" style="display: none;">
                <p><?php _e('Consulta enviada exitosamente.', 'proyectos-grid'); ?></p>
            </div>
            
            <!-- Added error message container -->
            <div id="form-error-message" class="form-error-message" style="display: none;">
                <p><?php _e('Error al enviar la consulta.', 'proyectos-grid'); ?></p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var initialCategory = '<?php echo esc_js($category_param); ?>';
            var initialTag = '<?php echo esc_js($tag_param); ?>';
            var initialService = '<?php echo esc_js($service_param); ?>';
            
            function showLoader() {
                $('#form-loader').show();
                // Disable all form fields during loading
                $('.proyectos-form select, .proyectos-form input, .proyectos-form textarea, .btn-enviar').prop('disabled', true);
            }
            
            function hideLoader() {
                $('#form-loader').hide();
                // Re-enable form fields after loading
                $('.proyectos-form select, .proyectos-form input, .proyectos-form textarea, .btn-enviar').prop('disabled', false);
            }
            
            function updateProjectLabel(categorySlug) {
                if (categorySlug) {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'get_category_singular_name',
                            category: categorySlug
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#proyecto-label').text('Selecciona tu ' + response.data);
                            } else {
                                var categoryName = $('#interes_principal option[value="' + categorySlug + '"]').text();
                                $('#proyecto-label').text('Selecciona tu ' + categoryName);
                            }
                        },
                        error: function() {
                            var categoryName = $('#interes_principal option[value="' + categorySlug + '"]').text();
                            $('#proyecto-label').text('Selecciona tu ' + categoryName);
                        }
                    });
                } else {
                    $('#proyecto-label').text('<?php _e('Proyecto Específico', 'proyectos-grid'); ?>');
                }
            }
            
            function updateRequiredFields() {
                // Remove required from all conditional fields first
                $('#modalidad, #proyecto_especifico').removeAttr('required');
                
                // Add required to visible fields
                if ($('#modalidad-field').length && $('#modalidad-field').is(':visible')) {
                    $('#modalidad').attr('required', true);
                }
                if ($('#proyecto-field').is(':visible')) {
                    $('#proyecto_especifico').attr('required', true);
                }
            }
            
            function loadTags(categorySlug) {
                if (!categorySlug) {
                    // Remove modalidad field from DOM completely
                    $('#modalidad-field').parent().remove();
                    $('#proyecto-field').hide();
                    updateRequiredFields();
                    return;
                }
                
                showLoader();
                updateProjectLabel(categorySlug);
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'get_proyecto_tags',
                        category: categorySlug
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            // Check if modalidad field exists, if not create it
                            if ($('#modalidad-field').length === 0) {
                                var modalidadRow = $('<div class="form-row">' +
                                    '<div class="form-field" id="modalidad-field">' +
                                    '<label for="modalidad"><?php _e('Modalidad', 'proyectos-grid'); ?></label>' +
                                    '<select id="modalidad" name="modalidad">' +
                                    '<option value=""><?php _e('Selecciona una modalidad', 'proyectos-grid'); ?></option>' +
                                    '</select>' +
                                    '</div>' +
                                    '</div>');
                                
                                // Insert after the interes_principal field
                                $('#interes_principal').closest('.form-row').after(modalidadRow);
                                
                                // Re-bind the change event for the new modalidad field
                                $('#modalidad').change(function() {
                                    var categorySlug = $('#interes_principal').val();
                                    var tagSlug = $(this).val();
                                    loadProjects(categorySlug, tagSlug);
                                });
                            }
                            
                            $('#modalidad').empty().append('<option value=""><?php _e('Selecciona una modalidad', 'proyectos-grid'); ?></option>');
                            $.each(response.data, function(index, tag) {
                                var selected = (tag.slug === initialTag) ? 'selected' : '';
                                $('#modalidad').append('<option value="' + tag.slug + '" ' + selected + '>' + tag.name + '</option>');
                            });
                            $('#modalidad-field').show();
                            updateRequiredFields();
                            
                            // If there's an initial tag, trigger change
                            if (initialTag) {
                                $('#modalidad').trigger('change');
                            } else {
                                // Load projects without tag filter
                                loadProjects(categorySlug, '');
                            }
                        } else {
                            // Remove modalidad field from DOM completely if no tags
                            $('#modalidad-field').parent().remove();
                            loadProjects(categorySlug, '');
                            updateRequiredFields();
                        }
                    },
                    error: function() {
                        hideLoader();
                    }
                });
            }
            
            // Function to load projects for selected category and tag
            function loadProjects(categorySlug, tagSlug) {
                if (!categorySlug) {
                    $('#proyecto-field').hide();
                    updateRequiredFields();
                    hideLoader(); // Hide loader if no category
                    return;
                }
                
                if (!$('#form-loader').is(':visible')) {
                    showLoader();
                }
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'get_proyecto_projects',
                        category: categorySlug,
                        tag: tagSlug
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            $('#proyecto_especifico').empty().append('<option value=""><?php _e('Selecciona un proyecto', 'proyectos-grid'); ?></option>');
                            $.each(response.data, function(index, project) {
                                // Remove parentheses for comparison
                                var cleanTitle = project.title.replace(/[()]/g, '');
                                var cleanInitial = initialService.replace(/[()]/g, '');
                                var selected = (cleanTitle === cleanInitial || project.title === initialService) ? 'selected' : '';
                                $('#proyecto_especifico').append('<option value="' + project.id + '" ' + selected + '>' + project.title + '</option>');
                            });
                            $('#proyecto-field').show();
                            updateRequiredFields();
                        } else {
                            $('#proyecto-field').hide();
                            updateRequiredFields();
                        }
                        hideLoader();
                    },
                    error: function() {
                        hideLoader();
                    }
                });
            }
            
            // Category change handler
            $('#interes_principal').change(function() {
                var categorySlug = $(this).val();
                // Reset initial tag when category changes manually
                if (categorySlug !== initialCategory) {
                    initialTag = '';
                }
                loadTags(categorySlug);
            });
            
            // Tag change handler - using event delegation since modalidad field can be dynamically created
            $(document).on('change', '#modalidad', function() {
                var categorySlug = $('#interes_principal').val();
                var tagSlug = $(this).val();
                loadProjects(categorySlug, tagSlug);
            });
            
            // Form submission handler
            $('#proyectos-form').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                
                showLoader();
                
                $.ajax({
                    url: proyectos_ajax.ajax_url,
                    type: 'POST',
                    data: formData + '&action=submit_proyecto_form&nonce=' + proyectos_ajax.nonce,
                    success: function(response) {
                        hideLoader();
                        if (response.success) {
                            $('#form-success-message').show();
                            $('#proyectos-form')[0].reset();
                        } else {
                            $('#form-error-message').show();
                        }
                    },
                    error: function() {
                        hideLoader();
                        $('#form-error-message').show();
                    }
                });
            });
            
            if (initialCategory) {
                // Trigger the change event immediately to load dependent fields
                $('#interes_principal').trigger('change');
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    private function render_proyecto_card($post_id) {
        $post = get_post($post_id);
        $valor = get_post_meta($post_id, '_proyecto_valor', true);
        $moneda_individual = get_post_meta($post_id, '_proyecto_moneda', true);
        $enlace_personalizado = get_post_meta($post_id, '_proyecto_enlace_personalizado', true);
        $menu_order = get_post_meta($post_id, '_proyecto_orden', true);
        
        if (!empty($menu_order)) {
            wp_update_post(array(
                'ID' => $post_id,
                'menu_order' => intval($menu_order)
            ));
        }
        
        $moneda = !empty($moneda_individual) ? $moneda_individual : get_option('proyectos_moneda_global', 'CLP');
        
        $enlace_proyecto = get_permalink($post_id); // Para imagen y título
        
        $categorias = get_the_terms($post_id, 'proyecto_categoria');
        $primera_categoria = $categorias && !is_wp_error($categorias) ? $categorias[0]->slug : '';
        
        $etiquetas = get_the_terms($post_id, 'proyecto_etiqueta');
        $etiquetas_mostrar = array();
        if ($etiquetas && !is_wp_error($etiquetas)) {
            $etiquetas_mostrar = array_slice($etiquetas, 0, 2); // Get maximum 2 tags
        }
        
        if (!empty($enlace_personalizado)) {
            $enlace_boton = $enlace_personalizado;
        } else {
            $enlace_base = get_option('proyectos_enlace_base', home_url('/contacto'));
            $service_param = urlencode($post->post_title);
            $enlace_boton = $enlace_base . '?service=' . $service_param;
            if (!empty($primera_categoria)) {
                $enlace_boton .= '&category=' . urlencode($primera_categoria);
            }
            // Add tag parameter if exists
            if (!empty($etiquetas_mostrar[0]->slug)) {
                $enlace_boton .= '&tag=' . urlencode($etiquetas_mostrar[0]->slug);
            }
        }
        
        $imagen = get_the_post_thumbnail($post_id, 'full', array('class' => 'proyecto-imagen'));
        if (empty($imagen)) {
            $imagen = '<div class="proyecto-imagen-placeholder"></div>';
        }
        
        ob_start();
        ?>
        <div class="proyecto-card">
            <div class="proyecto-imagen-container">
                <!-- Imagen ahora va al permalink del proyecto -->
                <a href="<?php echo esc_url($enlace_proyecto); ?>" class="proyecto-link">
                    <?php echo $imagen; ?>
                </a>
            </div>
            <div class="proyecto-contenido">
                <h3 class="proyecto-titulo">
                    <!-- Título ahora va al permalink del proyecto -->
                    <a href="<?php echo esc_url($enlace_proyecto); ?>" class="proyecto-link">
                        <?php echo esc_html($post->post_title); ?>
                    </a>
                </h3>
                <div class="proyecto-descripcion">
                    <?php echo wp_trim_words($post->post_content, 20, '...'); ?>
                </div>
                <div class="proyecto-precio">
                    <?php _e('Valor:', 'proyectos-grid'); ?> $<?php echo number_format($valor, 0, ',', '.'); ?> <?php echo esc_html($moneda); ?>
                </div>
                <div class="proyecto-footer <?php echo empty($etiquetas_mostrar) ? 'sin-etiqueta' : ''; ?>">
                    <?php if (!empty($etiquetas_mostrar)): ?>
                        <div class="proyecto-etiqueta">
                            <?php foreach ($etiquetas_mostrar as $etiqueta): ?>
                                <span class="etiqueta"><?php echo esc_html($etiqueta->name); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="proyecto-boton">
                        <!-- Botón usa el enlace personalizado/base -->
                        <a href="<?php echo esc_url($enlace_boton); ?>" class="btn-inscribirse">
                            <?php _e('Inscríbete hoy', 'proyectos-grid'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

add_action('wp_ajax_get_proyecto_tags', 'handle_get_proyecto_tags');
add_action('wp_ajax_nopriv_get_proyecto_tags', 'handle_get_proyecto_tags');

function handle_get_proyecto_tags() {
    $category_slug = sanitize_text_field($_POST['category']);
    
    // Get projects in this category
    $projects = get_posts(array(
        'post_type' => 'proyecto',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'proyecto_categoria',
                'field' => 'slug',
                'terms' => $category_slug
            )
        )
    ));
    
    $tags = array();
    foreach ($projects as $project) {
        $project_tags = get_the_terms($project->ID, 'proyecto_etiqueta');
        if ($project_tags && !is_wp_error($project_tags)) {
            foreach ($project_tags as $tag) {
                if (!isset($tags[$tag->slug])) {
                    $tags[$tag->slug] = array(
                        'name' => $tag->name,
                        'slug' => $tag->slug
                    );
                }
            }
        }
    }
    
    wp_send_json_success(array_values($tags));
}

add_action('wp_ajax_get_proyecto_projects', 'handle_get_proyecto_projects');
add_action('wp_ajax_nopriv_get_proyecto_projects', 'handle_get_proyecto_projects');

function handle_get_proyecto_projects() {
    $category_slug = sanitize_text_field($_POST['category']);
    $tag_slug = sanitize_text_field($_POST['tag']);
    
    $tax_query = array(
        array(
            'taxonomy' => 'proyecto_categoria',
            'field' => 'slug',
            'terms' => $category_slug
        )
    );
    
    if (!empty($tag_slug)) {
        $tax_query[] = array(
            'taxonomy' => 'proyecto_etiqueta',
            'field' => 'slug',
            'terms' => $tag_slug
        );
    }
    
    $projects = get_posts(array(
        'post_type' => 'proyecto',
        'posts_per_page' => -1,
        'tax_query' => $tax_query,
        'orderby' => array(
            'menu_order' => 'ASC',
            'date' => 'DESC'
        )
    ));
    
    $project_list = array();
    foreach ($projects as $project) {
        $project_list[] = array(
            'id' => $project->ID,
            'title' => $project->post_title
        );
    }
    
    wp_send_json_success($project_list);
}

add_action('wp_ajax_get_category_singular_name', 'handle_get_category_singular_name');
add_action('wp_ajax_nopriv_get_category_singular_name', 'handle_get_category_singular_name');

function handle_get_category_singular_name() {
    $category_slug = sanitize_text_field($_POST['category']);
    
    $term = get_term_by('slug', $category_slug, 'proyecto_categoria');
    if ($term) {
        $nombre_singular = get_term_meta($term->term_id, 'nombre_singular', true);
        if (!empty($nombre_singular)) {
            wp_send_json_success($nombre_singular);
        } else {
            wp_send_json_success($term->name);
        }
    }
    
    wp_send_json_error();
}

add_action('wp_ajax_submit_proyecto_form', 'handle_submit_proyecto_form');
add_action('wp_ajax_nopriv_submit_proyecto_form', 'handle_submit_proyecto_form');

function handle_submit_proyecto_form() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'proyectos_form_nonce')) {
        wp_send_json_error('Nonce verification failed');
    }
    
    // Get form data
    $nombre = sanitize_text_field($_POST['nombre']);
    $apellido = sanitize_text_field($_POST['apellido']);
    $telefono = sanitize_text_field($_POST['telefono']);
    $email = sanitize_email($_POST['email']);
    $interes_principal = sanitize_text_field($_POST['interes_principal']);
    $modalidad = sanitize_text_field($_POST['modalidad']);
    $proyecto_especifico = sanitize_text_field($_POST['proyecto_especifico']);
    $motivacion = sanitize_textarea_field($_POST['motivacion']);
    
    // Get email configuration
    $email_receptores = get_option('proyectos_email_receptores', get_option('admin_email'));
    $email_template = get_option('proyectos_email_template', 'Nuevo mensaje de contacto');
    
    // Replace placeholders in email template
    $email_body = str_replace(
        array('[nombre]', '[apellido]', '[telefono]', '[email]', '[interes_principal]', '[modalidad]', '[proyecto_especifico]', '[motivacion]'),
        array($nombre, $apellido, $telefono, $email, $interes_principal, $modalidad, $proyecto_especifico, $motivacion),
        $email_template
    );
    
    // Send email
    $subject = 'Nueva consulta de proyecto - ' . $nombre . ' ' . $apellido;
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    $sent = wp_mail($email_receptores, $subject, nl2br($email_body), $headers);
    
    if ($sent) {
        wp_send_json_success('Consulta enviada exitosamente');
    } else {
        wp_send_json_error('Error al enviar la consulta');
    }
}

new ProyectosPlugin();