<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Condition Field: Multiselect - Coupons Group
 *
 * @class RP_WCDPD_Condition_Field_Multiselect_Coupons_Group
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
class RP_WCDPD_Condition_Field_Multiselect_Coupons_Group extends RightPress_Condition_Field_Multiselect_Product_Categories
{

    protected $plugin_prefix = RP_WCDPD_PLUGIN_PRIVATE_PREFIX;
    protected $key = 'coupons-group';
    protected $supports_hierarchy   = false;

    // Singleton instance
    protected static $instance = false;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        parent::__construct();
    }


    /**
     * Load multiselect options
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function load_multiselect_options($ids = array(), $query = '')
    {
        return RightPress_Conditions::get_all_hierarchical_taxonomy_terms('shop_coupon_cat',$ids, $query);
    }
    
    public function get_placeholder()
    {

        return __('Select coupon categories', 'rightpress');
    }


}

RP_WCDPD_Condition_Field_Multiselect_Coupons_Group::get_instance();
