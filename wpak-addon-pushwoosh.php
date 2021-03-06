<?php
/*
  Plugin Name: Pushwoosh for WP-AppKit
  Description: Subscribe users and send notifications without pain
  Version: 1.0.3
 */

if ( !class_exists( 'WpAppKitPushwoosh' ) ) {

    /**
     * Pushwoosh addon main manager class.
     */
    class WpAppKitPushwoosh {

    	const name = 'Pushwoosh for WP-AppKit';
        const slug = 'wpak-addon-pushwoosh';
        const i18n_domain = 'wpak-addon-pushwoosh';

        /**
         * Main entry point.
         *
         * Adds needed callbacks to some hooks.
         */
        public static function hooks() {
            add_filter( 'wpak_addons', array( __CLASS__, 'wpak_addons' ) );
            add_filter( 'wpak_default_phonegap_build_plugins', array( __CLASS__, 'wpak_default_phonegap_build_plugins' ), 10, 3 );
            add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ) );
            add_filter( 'wpak_licenses', array( __CLASS__, 'add_license' ) );
        }

        /**
         * Attached to 'wpak_addons' hook.
         *
         * Filter available addons and register this one for all WP-AppKit applications.
         *
         * @param array             $addons            Available addons.
         *
         * @return array            $addons            Addons with Pushwoosh (this one).
         */
        public static function wpak_addons( $addons ) {
            $addon = new WpakAddon( self::name, self::slug );

            $addon->set_location( __FILE__ );

            $addon->add_js( 'js/wpak-pushwoosh.js', 'module' );
            $addon->add_js( 'js/wpak-pushwoosh-app.js', 'init', 'before' );

            $addon->require_php( dirname(__FILE__) .'/wpak-pushwoosh-bo-settings.php' );

            $addons[] = $addon;

            return $addons;
        }

        /**
         * Attached to 'wpak_default_phonegap_build_plugins' hook.
         *
         * Filter default plugins included into the PhoneGap Build config.xml file.
         *
         * @param array             $default_plugins            The default plugins.
         * @param string            $export_type                Export type : 'phonegap-build' or 'phonegap-cli'
         * @param int               $app_id                     The App ID.
         *
         * @return array            $default_plugins            Plugins with Pushwoosh one in addition.
         */
        public static function wpak_default_phonegap_build_plugins( $default_plugins, $export_type, $app_id ) {
            if( WpakAddons::addon_activated_for_app( self::slug, $app_id ) ) {
                switch( $export_type ) {
                    //
                    // Due to PhoneGap Build being really long to update their Google Play libraries
                    // There is a specific version of this plugin for PhoneGap Build
                    // The cordova version is crashing if we try to include it with PhoneGap Build
                    // See: https://github.com/Pushwoosh/pushwoosh-phonegap-plugin/issues/189
                    // 2016-08-26: It seems to be fixed now, but the specific plugin is still maintained on npm, so let's keep using it, just in case.
                    //

                    case 'phonegap-build':
                        $default_plugins['pushwoosh-pgb-plugin'] = array( 'spec' => '6.5.3', 'source' => 'npm' );
                        break;
                    default:
                        $default_plugins['pushwoosh-cordova-plugin'] = array( 'spec' => '6.5.3', 'source' => 'npm' );
                        break;
                }
            }

            return $default_plugins;
        }

        /**
         * Attached to 'plugins_loaded' hook.
         *
         * Register the addon textdomain for string translations.
         */
        public static function plugins_loaded() {
            load_plugin_textdomain( self::i18n_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
        }

        /**
         * Register license management for this addon.
         *
         * @param array $licenses Licenses array given by WP-AppKit's core.
         * @return array
         */
        public static function add_license( $licenses ) {
            $licenses[] = array(
                'file' => __FILE__,
                'item_name' => self::name,
                'version' => '1.0.3',
                'author' => 'Uncategorized Creations',
            );
            return $licenses;
        }

    }

    WpAppKitPushwoosh::hooks();
}