<?php
/* ====================================
 * Plugin Name: WooCommerce Dynamic Pricing & Discounts Additional Conditions
 * Description: Плагин для добавления дополнительных условий скидок 
 * Author: ИП Никитин и партнеры
 * Author URI: https://ivannikitin.com
 * Version: 1.01
 * ==================================== */

if (!class_exists('RP_WCDPD')) {
	add_action('admin_notices', 'wcdpd_ac_wc_disabled_notice');
	return;
}

// Define Constants
define('WCDPD_AC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WCDPD_AC_PLUGIN_KEY', 'wc-dynamic-pricing-and-discounts_add_condition');

// Load text domains
load_textdomain('wcdpd_ac', WP_LANG_DIR . '/' . WCDPD_AC_PLUGIN_KEY . '/wcdpd_ac-' . apply_filters('plugin_locale', get_locale(), 'wcdpd_ac') . '.mo');
load_plugin_textdomain('wcdpd_ac', false, WCDPD_AC_PLUGIN_KEY . '/languages/');

if (!taxonomy_exists('shop_coupon_cat')) {
	add_action( 'init', 'wcdpd_register_coupon_category_taxonomy', 99 );
    add_filter('manage_edit-shop_coupon_columns', 'wcdpd_add_coupon_list_category_column');
    add_filter('manage_shop_coupon_posts_custom_column', 'wcdpd_coupon_list_category_column_content', 10, 2);
    add_action('restrict_manage_posts', 'wcdpd_add_shop_coupon_category_filter_selection', 10);
    add_action('admin_menu', 'wcdpd_add_coupon_cat_admin_menu', 20);
}	
	add_action('plugins_loaded', 'wcdpd_ac_add_conditions', 99);

function wcdpd_register_coupon_category_taxonomy()
    {

        $labels = array(
            'name'                       => _x('Coupon Categories', 'Taxonomy General Name', 'wcdpd_ac'),
            'singular_name'              => _x('Coupon Category', 'Taxonomy Singular Name', 'wcdpd_ac'),
            'menu_name'                  => __('Категории купонов', 'wcdpd_ac'),
            'all_items'                  => __('All Categories', 'wcdpd_ac'),
            'parent_item'                => __('Parent Category', 'wcdpd_ac'),
            'parent_item_colon'          => __('Parent Category:', 'wcdpd_ac'),
            'new_item_name'              => __('New Category Name', 'wcdpd_ac'),
            'add_new_item'               => __('Add New Category', 'wcdpd_ac'),
            'edit_item'                  => __('Edit Category', 'wcdpd_ac'),
            'update_item'                => __('Update Category', 'wcdpd_ac'),
            'view_item'                  => __('View Category', 'wcdpd_ac'),
            'separate_items_with_commas' => __('Separate categories with commas', 'wcdpd_ac'),
            'add_or_remove_items'        => __('Add or remove categories', 'wcdpd_ac'),
            'choose_from_most_used'      => __('Choose from the most used', 'wcdpd_ac'),
            'popular_items'              => __('Popular Categories', 'wcdpd_ac'),
            'search_items'               => __('Search Categories', 'wcdpd_ac'),
            'not_found'                  => __('Not Found', 'wcdpd_ac'),
            'no_terms'                   => __('No categories', 'wcdpd_ac'),
            'items_list'                 => __('Categories list', 'wcdpd_ac'),
            'items_list_navigation'      => __('Categories list navigation', 'wcdpd_ac'),
        );

        $capabilities = array(
            'manage_terms' => 'manage_woocommerce',
            'edit_terms'   => 'manage_woocommerce',
            'delete_terms' => 'manage_woocommerce',
            'assign_terms' => 'manage_woocommerce',
        );

        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'capabilities'      => $capabilities,
            'show_in_rest'      => true,
        );

        register_taxonomy('shop_coupon_cat', array('shop_coupon'), $args);

}

function wcdpd_add_coupon_list_category_column($columns)
    {

        $columns['coupon_categories'] = __('Категории', 'wcdpd_ac');

        return $columns;
    }

function wcdpd_coupon_list_category_column_content($column, $coupon_id)
    {

        if ('coupon_categories' !== $column) {
            return;
        }

        $categories = get_the_terms($coupon_id, 'shop_coupon_cat');

        if (!is_array($categories) || empty($categories)) {
            echo '–';
            return;
        }

        $content = array_map(function ($term) {
            $filter_link = admin_url('edit.php?post_type=shop_coupon&shop_coupon_cat' . '=' . $term->slug);
            return sprintf('<a href="%s">%s</a>', $filter_link, $term->name);
        }, $categories);

        echo implode(', ', $content);
}

function wcdpd_add_shop_coupon_category_filter_selection($post_type)
    {

        global $wp_query;

        if ('shop_coupon' !== $post_type) {
            return;
        }

        $args = array(
            'pad_counts'         => true,
            'show_count'         => true,
            'hierarchical'       => true,
            'hide_empty'         => false,
            'show_uncategorized' => true,
            'orderby'            => 'name',
            'selected'           => isset($wp_query->query_vars['shop_coupon_cat']) ? $wp_query->query_vars['shop_coupon_cat'] : '',
            'show_option_none'   => __('Select a category', 'wcdpd_ac'),
            'option_none_value'  => '',
            'value_field'        => 'slug',
            'taxonomy'           => 'shop_coupon_cat',
            'name'               => 'shop_coupon_cat',
            'class'              => 'dropdown_shop_coupon_cat',
        );

        wp_dropdown_categories($args);
}

function wcdpd_add_coupon_cat_admin_menu()
{

        global $submenu;

        // if woocommerce menu is present for current user then don't proceed.
        if (!isset($submenu['woocommerce']) || !is_array($submenu['woocommerce'])) {
            return;
        }

        $toplevel_slug = 'wcdpd_ac-admin';

        // filter out all coupon categories related menus under WooCommerce.
        $wc_coupon_submenus = array_filter($submenu['woocommerce'], function ($s) {
            return strpos($s[2], 'shop_coupon_cat') !== false;
        });

        // remove all coupon categories related submenus under WooCommerce.
        $submenu['woocommerce'] = array_filter($submenu['woocommerce'], function ($s) use ($wc_coupon_submenus) {
            return !in_array($s, $wc_coupon_submenus);
        });

        add_submenu_page(
            'woocommerce-marketing',
            __('Категории купонов', 'wcdpd_ac'),
            __('Категории купонов', 'wcdpd_ac'),
            'edit_shop_coupons',
            'edit-tags.php?taxonomy=shop_coupon_cat&amp;post_type=shop_coupon'
        );
}

function wcdpd_ac_add_conditions() {

	// Load conditions
	require_once WCDPD_AC_PLUGIN_PATH . 'classes/conditions/conditions/rp-wcdpd-condition-cart-coupons-group.class.php'; 
	// Load condition methods
	require_once WCDPD_AC_PLUGIN_PATH . 'classes/conditions/condition-methods/rp-wcdpd-condition-method-coupons-group.class.php'; 
	// Load condition fields
	require_once WCDPD_AC_PLUGIN_PATH . 'classes/conditions/condition-fields/rp-wcdpd-condition-field-multiselect-coupons-group.class.php'; 
}


function wcdpd_ac_wc_disabled_notice()
{
        echo '<div class="error"><p><strong>WooCommerce Dynamic Pricing & Discounts Additional Conditions</strong> requires WooCommerce to be active.</p></div>';
}
