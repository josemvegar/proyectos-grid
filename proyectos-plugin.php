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
        
        // Taxonomía no jerárquica (Etiquetas)
        $tag_labels = array(
            'name' => __('Etiquetas de Proyecto', 'proyectos-grid'),
            'singular_name' => __('Etiqueta de Proyecto', 'proyectos-grid'),
            'search_items' => __('Buscar Etiquetas', 'proyectos-grid'),
            'popular_items' => __('Etiquetas Populares', 'proyectos-grid'),
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
            echo '<div class="notice notice-success"><p>' . __('Configuración guardada correctamente.', 'proyectos-grid') . '</p></div>';
        }
        
        $moneda_global = get_option('proyectos_moneda_global', 'CLP');
        $enlace_base = get_option('proyectos_enlace_base', home_url('/contacto'));
        
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
        
        // Enlace del botón (personalizado o base)
        if (!empty($enlace_personalizado)) {
            $enlace_boton = $enlace_personalizado;
        } else {
            $enlace_base = get_option('proyectos_enlace_base', home_url('/contacto'));
            $service_param = urlencode($post->post_title);
            $enlace_boton = $enlace_base . '?service=' . $service_param;
            if (!empty($primera_categoria)) {
                $enlace_boton .= '&category=' . urlencode($primera_categoria);
            }
        }
        
        $etiquetas = get_the_terms($post_id, 'proyecto_etiqueta');
        $primera_etiqueta = $etiquetas && !is_wp_error($etiquetas) ? $etiquetas[0]->name : '';
        
        $imagen = get_the_post_thumbnail($post_id, 'medium', array('class' => 'proyecto-imagen'));
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
                <div class="proyecto-footer <?php echo empty($primera_etiqueta) ? 'sin-etiqueta' : ''; ?>">
                    <?php if ($primera_etiqueta): ?>
                        <div class="proyecto-etiqueta">
                            <span class="etiqueta"><?php echo esc_html($primera_etiqueta); ?></span>
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

new ProyectosPlugin();