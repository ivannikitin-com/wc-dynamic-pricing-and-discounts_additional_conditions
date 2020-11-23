<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

// Load dependencies
//require_once 'rightpress-condition-cart.class.php';

/**
 * Condition: Cart - Coupons group
 *
 * @class RP_WCDPD_Condition_Cart_Coupons_group
 * @package RightPress
 * @author RightPress
 */
class RP_WCDPD_Condition_Cart_Coupons_group extends RightPress_Condition_Cart_Coupons
{

    protected $plugin_prefix = RP_WCDPD_PLUGIN_PRIVATE_PREFIX;
    protected $key      = 'coupons-group';
    protected $contexts = array('product_pricing', 'cart_discounts', 'checkout_fees');
    protected $method   = 'coupons-group';
    protected $fields   = array(
        'after' => array('coupons-group'),
    );
    protected $position = 60;

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
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {

        return __('Coupons group applied', 'rightpress');
    }

    /**
     * Get value to compare against condition
     *
     * @access public
     * @param array $params
     * @return mixed
     */
    public function get_value($params)
    {

        //$coupon_ids = RightPress_Help::get_wc_cart_applied_coupon_ids();

        // Remove coupons with empty id - they are not real coupons and most probably are our cart discounts
        /*foreach ($coupon_ids as $coupon_id_key => $coupon_id) {
            if ($coupon_id === 0) {
                unset($coupon_ids[$coupon_id_key]);
            }
        }*/

        //return $coupon_ids;
        $group_ids = array();
        $coupon_groups = get_terms(array('taxonomy' => 'shop_coupon_cat'));
        foreach( $coupon_groups as $coupon_group ){
            $group_ids[] = $coupon_group->term_id;
        }
        return $group_ids;
    }

}
RP_WCDPD_Condition_Cart_Coupons_group::get_instance();
