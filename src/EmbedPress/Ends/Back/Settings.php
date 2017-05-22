<?php
namespace EmbedPress\Ends\Back;

use \EmbedPress\Core;

(defined('ABSPATH') && defined('EMBEDPRESS_IS_LOADED')) or die("No direct script access allowed.");

/**
 * Entity that handles the plugin's settings page.
 *
 * @package     EmbedPress
 * @subpackage  EmbedPress/Ends/Back
 * @author      PressShack <help@pressshack.com>
 * @copyright   Copyright (C) 2017 Open Source Training, LLC. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */
class Settings
{
    /**
     * This class namespace.
     *
     * @since   1.0.0
     * @access  private
     * @static
     *
     * @var     string    $namespace
     */
    private static $namespace = '\EmbedPress\Ends\Back\Settings';

    /**
     * The plugin's unique identifier.
     *
     * @since   1.0.0
     * @access  private
     * @static
     *
     * @var     string    $identifier
     */
    private static $identifier = "plg_embedpress";

    /**
     * Unique identifier to the plugin's admin settings section.
     *
     * @since   1.0.0
     * @access  private
     * @static
     *
     * @var     string    $sectionAdminIdentifier
     */
    private static $sectionAdminIdentifier = "embedpress_options_admin";

    /**
     * Unique identifier to the plugin's general settings section.
     *
     * @since   1.0.0
     * @access  private
     * @static
     *
     * @var     string    $sectionGroupIdentifier    The name of the plugin.
     */
    private static $sectionGroupIdentifier = "embedpress";

    /**
     * Map to all settings.
     *
     * @since   1.0.0
     * @access  private
     * @static
     *
     * @var     string    $fieldMap
     */
    private static $fieldMap = array(
        'enablePluginInAdmin' => array(
            'label'   => "Load previews in the admin editor",
            'section' => "admin"
        ),
        'enablePluginInFront' => array(
            'label'   => "Load previews in the frontend editor",
            'section' => "admin"
        ),
        'forceFacebookLanguage' => array(
            'label'   => "Facebook embed language",
            'section' => "admin"
        )
    );

    /**
     * Class constructor. This prevents the class being directly instantiated.
     *
     * @since   1.0.0
     */
    public function __construct()
    {}

    /**
     * This prevents the class being cloned.
     *
     * @since   1.0.0
     */
    public function __clone()
    {}

    /**
     * Method that adds an sub-item for EmbedPress to the WordPress Settings menu.
     *
     * @since   1.0.0
     * @static
     */
    public static function registerMenuItem()
    {
        add_menu_page('EmbedPress Settings', 'EmbedPress', 'manage_options', 'embedpress', array(self::$namespace, 'renderForm'), null, 64);
    }

    /**
     * Method that configures the EmbedPress settings page.
     *
     * @since   1.0.0
     * @static
     */
    public static function registerActions()
    {
        $activeTab = isset($_GET['tab']) ? strtolower($_GET['tab']) : "";
        if ($activeTab !== "embedpress") {
            $action = "embedpress:{$activeTab}:settings:register";
        } else {
            $activeTab = "";
        }

        if (!empty($activeTab) && has_action($action)) {
            do_action($action, array(
                'id'   => self::$sectionAdminIdentifier,
                'slug' => self::$identifier
            ));
        } else {
            register_setting(self::$sectionGroupIdentifier, self::$sectionGroupIdentifier, array(self::$namespace, "validateForm"));

            add_settings_section(self::$sectionAdminIdentifier, '', null, self::$identifier);

            foreach (self::$fieldMap as $fieldName => $field) {
                add_settings_field($fieldName, $field['label'], array(self::$namespace, "renderField_{$fieldName}"), self::$identifier, self::${"section". ucfirst($field['section']) ."Identifier"});
            }
        }
    }

    /**
     * Returns true if the plugin is active
     *
     * @param  string   $plugin
     *
     * @return boolean
     */
    protected static function is_plugin_active( $plugin ) {
        return is_plugin_active( "{$plugin}/{$plugin}.php" );
    }

    /**
     * Returns true if the plugin is installed
     *
     * @param  string   $plugin
     *
     * @return boolean
     */
    protected static function is_plugin_installed( $plugin ) {
        return file_exists( plugin_dir_path( PUBLISHPRESS_ROOT ) . "{$plugin}/{$plugin}.php" );
    }

    /**
     * Method that render the settings's form.
     *
     * @since   1.0.0
     * @static
     */
    public static function renderForm()
    {
        // Add the color picker css file
        wp_enqueue_style('wp-color-picker');
        // Include our custom jQuery file with WordPress Color Picker dependency
        wp_enqueue_script('ep-settings', EMBEDPRESS_URL_ASSETS .'js/settings.js', array('wp-color-picker'), EMBEDPRESS_PLG_VERSION, true);

        $activeTab = isset($_GET['tab']) ? strtolower($_GET['tab']) : "";

        $settingsFieldsIdentifier = $activeTab === 'general' ? self::$sectionGroupIdentifier : "embedpress:{$activeTab}";
        $settingsSectionsIdentifier = $activeTab === 'general' ? self::$identifier : "embedpress:{$activeTab}";
        ?>
        <div id="embedpress-settings-wrapper">
            <header>
                <h1 class="pressshack-title">
                    <a href="//wordpress.org/plugins/embedpress" target="_blank" rel="noopener noreferrer" title="EmbedPress">
                        EmbedPress
                    </a>
                </h1>
            </header>

            <?php settings_errors(); ?>

            <div>
                <h2 class="nav-tab-wrapper">
                    <a href="?page=embedpress" class="nav-tab<?php echo $activeTab === 'embedpress' || empty($activeTab) ? ' nav-tab-active' : ''; ?> ">
                        Getting started
                    </a>

                    <a href="?page=embedpress&tab=general" class="nav-tab<?php echo $activeTab === 'general' ? ' nav-tab-active' : ''; ?> ">
                        General
                    </a>

                    <?php do_action('embedpress:settings:render:tab', $activeTab); ?>

                    <a href="?page=embedpress&tab=addons" class="nav-tab<?php echo $activeTab === 'addons' ? ' nav-tab-active' : ''; ?> ">
                        Add-ons
                    </a>
                </h2>
                <?php if ($activeTab === 'embedpress' || empty($activeTab)) : ?>
                    <div>
                        <h3>EmbedPress: embed ANYTHING in WordPress</h3>
                        <p>
                            WordPress supports around 40 media sources. The EmbedPress plugin adds support for over 40 more, including popular sources for social media, maps, video, audio, charts, and documents.
                        </p>
                        <p>
                            All you need is the URL. Add the URL to a WordPress post, and you can embed media from over 75 providers into your site!
                        </p>
                        <p>
                            EmbedPress supports these providers including Facebook, Giphy, Twitch, all Google media (Docs, Maps, Sheets, Drawings, Slides), Instagram, and many more.
                        </p>

                        <div class="container">
                            <div class="row">
                                <div class="col-4">
                                    <h2>Embed Facebook Posts and Videos</h2>
                                    <p>EmbedPress allow you to embed Facebook posts and videos. This is a plugin that makes it easy to embed media content into your site. All you have to do is copy-and-paste the URL of a supported provider into your post and pages. The plugin takes care of the rest! What could be simpler?</p>
                                </div>

                                <div class="col-8">
                                    <div class="image"><img src="<?php echo EMBEDPRESS_URL_ASSETS; ?>/images/facebook-embed.jpg" alt="WordPress EmbedPress Embed Facebook Posts" scale="0"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-8">
                                    <div class="image"><img src="<?php echo EMBEDPRESS_URL_ASSETS; ?>/images/wistia-embed.jpg" alt="WordPress EmbedPress Embed Wistia Videos" scale="0"></div>
                                </div>

                                <div class="col-4">
                                    <h2>Embed Wistia Videos</h2>
                                    <p>Embed Wistia videos by just pasting the URL into a WordPress post or page.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if (!empty($activeTab) && !in_array($activeTab, array('addons', 'embedpress')) ) : ?>
                    <form action="options.php" method="POST" style="padding-bottom: 20px;">
                        <?php settings_fields($settingsFieldsIdentifier); ?>
                        <?php do_settings_sections($settingsSectionsIdentifier); ?>

                        <button type="submit" class="button button-primary">Save changes</button>
                    </form>
                <?php endif; ?>

                <?php if ($activeTab === 'addons') : ?>
                    <?php
                    $icons_base_path = plugins_url( 'embedpress' ) . '/assets/images/' ;

                    $addons = array(
                        'embedpress-youtube' => array(
                            'title'       => __( 'The YouTube Add-on for EmbedPress', 'embedpress' ),
                            'description' => __( 'Get more features for your YouTube embeds in WordPress.', 'embedpress' ),
                            'available'   => true,
                            'installed'   => static::is_plugin_installed( 'embedpress-youtube' ),
                            'active'      => static::is_plugin_active( 'embedpress-youtube' ),
                        ),
                        'embedpress-vimeo' => array(
                            'title'       => __( 'The Vimeo Add-on for EmbedPress', 'embedpress' ),
                            'description' => __( 'Get more features for your Vimeo embeds in WordPress.', 'embedpress' ),
                            'available'   => true,
                            'installed'   => static::is_plugin_installed( 'embedpress-vimeo' ),
                            'active'      => static::is_plugin_active( 'embedpress-vimeo' ),
                        ),
                        'embedpress-wistia' => array(
                            'title'       => __( 'The Wistia Add-on for EmbedPress', 'embedpress' ),
                            'description' => __( 'Get more features for your Wistia embeds in WordPress.', 'embedpress' ),
                            'available'   => true,
                            'installed'   => static::is_plugin_installed( 'embedpress-wistia' ),
                            'active'      => static::is_plugin_active( 'embedpress-wistia' ),
                        ),
                    );

                    $args = array(
                        'addons'          => $addons,
                        'icons_base_path' => $icons_base_path,
                        'labels'          => array(
                            'active'         => __( 'Active', 'publishpress' ),
                            'installed'      => __( 'Installed', 'publishpress' ),
                            'get_pro_addons' => __( 'Get Pro Add-ons!', 'publishpress' ),
                            'coming_soon'    => __( 'Coming soon', 'publishpress' ),
                        ),
                    );

                    ?>
                    <div class="ep-module-settings">
                        <ul class="ep-block-addons-items">
                        <?php foreach ( $addons as $name => $addon ): ?>
                            <li class="ep-block-addons-item ">
                                <img src="<?php echo $icons_base_path . $name; ?>.jpg">
                                <h3><?php echo $addon['title']; ?></h3>
                                <p><?php echo $addon['description']; ?></p>

                                <?php if ( $addon['available'] ): ?>
                                    <?php if ( $addon['installed'] ): ?>
                                        <?php if ( $addon['active'] ): ?>
                                            <div>
                                                <span class="dashicons dashicons-yes"></span><span><?php echo __( 'Active', 'embedpress' ); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div>
                                                <span><?php echo __( 'Installed', 'embedpress' ); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="https://pressshack.com/addons/embedpress-club/" class="button button-primary">
                                            <span class="dashicons dashicons-cart"></span> <?php echo __( 'Get Pro Add-ons!', 'embedpress' ); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div><?php echo __( 'Coming soon', 'embedpress' ); ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <footer>
                <p>
                    <a href="//wordpress.org/support/plugin/embedpress/reviews/#new-post" target="_blank" rel="noopener noreferrer">If you like <strong>EmbedPress</strong> please leave us a <span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span> rating. Thank you!</a>
                </p>
                <hr>
                <nav>
                    <ul>
                        <li>
                            <a href="//pressshack.com/embedpress" target="_blank" rel="noopener noreferrer" title="About EmbedPress">About</a>
                        </li>
                        <li>
                            <a href="//pressshack.com/embedpress/docs/sources-support" target="_blank" rel="noopener noreferrer" title="List of supported sources by EmbedPress">Supported sources</a>
                        </li>
                        <li>
                            <a href="//pressshack.com/embedpress/docs" target="_blank" rel="noopener noreferrer" title="EmbedPress Documentation">Documentation</a>
                        </li>
                        <li>
                            <a href="//pressshack.com/embedpress/addons/" target="_blank" rel="noopener noreferrer" title="EmbedPress Add-Ons">Add-Ons</a>
                        </li>
                        <li>
                            <a href="//pressshack.com/contact" target="_blank" rel="noopener noreferrer" title="Contact the PressShack team">Contact</a>
                        </li>
                        <li>
                            <a href="//twitter.com/pressshack" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-twitter"></span>
                            </a>
                        </li>
                        <li>
                            <a href="//facebook.com/pressshack" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-facebook"></span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <p>
                    <a href="//pressshack.com" target="_blank" rel="noopener noreferrer">
                        <img width="100" src="//pressshack.com/wp-content/uploads/2016/11/logo-450.png">
                    </a>
                </p>
            </footer>
        </div>
        <?php
    }

    /**
     * Method that validates the form data.
     *
     * @since   1.0.0
     * @static
     *
     * @param   mixed   $freshData  Data received from the form.
     *
     * @return  array
     */
    public static function validateForm($freshData)
    {
        $data = array(
            'enablePluginInAdmin' => (bool)$freshData['enablePluginInAdmin'],
            'enablePluginInFront' => (bool)$freshData['enablePluginInFront'],
            'fbLanguage'          => $freshData['fbLanguage']
        );

        return $data;
    }

    /**
     * Method that renders the enablePluginInAdmin input.
     *
     * @since   1.0.0
     * @static
     */
    public static function renderField_enablePluginInAdmin()
    {
        $fieldName = "enablePluginInAdmin";

        $options = get_option(self::$sectionGroupIdentifier);

        $options[$fieldName] = !isset($options[$fieldName]) ? true : (bool)$options[$fieldName];

        echo '<label><input type="radio" id="'. $fieldName .'_0" name="'. self::$sectionGroupIdentifier .'['. $fieldName .']" value="0" '. (!$options[$fieldName] ? "checked" : "") .' /> No</label>';
        echo "&nbsp;&nbsp;";
        echo '<label><input type="radio" id="'. $fieldName .'_1" name="'. self::$sectionGroupIdentifier .'['. $fieldName .']" value="1" '. ($options[$fieldName] ? "checked" : "") .' /> Yes</label>';
        echo '<p class="description">Do you want EmbedPress to run here in the admin area? Disabling this <strong>will not</strong> affect your frontend embeds.</p>';
    }

    /**
     * Method that renders the enablePluginInFront input.
     *
     * @since   1.6.0
     * @static
     */
    public static function renderField_enablePluginInFront()
    {
        $fieldName = "enablePluginInFront";

        $options = get_option(self::$sectionGroupIdentifier);

        $options[$fieldName] = !isset($options[$fieldName]) ? true : (bool)$options[$fieldName];

        echo '<label><input type="radio" id="'. $fieldName .'_0" name="'. self::$sectionGroupIdentifier .'['. $fieldName .']" value="0" '. (!$options[$fieldName] ? "checked" : "") .' /> No</label>';
        echo "&nbsp;&nbsp;";
        echo '<label><input type="radio" id="'. $fieldName .'_1" name="'. self::$sectionGroupIdentifier .'['. $fieldName .']" value="1" '. ($options[$fieldName] ? "checked" : "") .' /> Yes</label>';
        echo '<p class="description">Do you want EmbedPress to run within editors in frontend (if there\'s any)? Disabling this <strong>will not</strong> affect embeds seem by your regular users in frontend.</p>';
    }

    /**
     * Method that renders the forceFacebookLanguage input.
     *
     * @since   1.3.0
     * @static
     */
    public static function renderField_forceFacebookLanguage()
    {
        $fieldName = "fbLanguage";

        $options = get_option(self::$sectionGroupIdentifier);

        $options[$fieldName] = !isset($options[$fieldName]) ? "" : $options[$fieldName];

        $facebookLocales = self::getFacebookAvailableLocales();

        echo '<select name="'. self::$sectionGroupIdentifier .'['. $fieldName .']">';
        echo '<option value="0">Automatic (by Facebook)</option>';
        echo '<optgroup label="Available">';
        foreach ($facebookLocales as $locale => $localeName) {
            echo '<option value="'. $locale .'"'. ($options[$fieldName] === $locale ? ' selected' : '') .'>'. $localeName .'</option>';
        }
        echo '</optgroup>';
        echo '</select>';

        echo '<p class="description">Sometimes Facebook can choose the wrong language for embeds. If this happens, choose the correct language here.</p>';
    }

    /**
     * Returns a list of locales that can be used on Facebook embeds.
     *
     * @since   1.3.0
     * @static
     *
     * @return  array
     */
    public static function getFacebookAvailableLocales()
    {
        $locales = array(
            'af_ZA' => "Afrikaans",
            'ak_GH' => "Akan",
            'am_ET' => "Amharic",
            'ar_AR' => "Arabic",
            'as_IN' => "Assamese",
            'ay_BO' => "Aymara",
            'az_AZ' => "Azerbaijani",
            'be_BY' => "Belarusian",
            'bg_BG' => "Bulgarian",
            'bn_IN' => "Bengali",
            'br_FR' => "Breton",
            'bs_BA' => "Bosnian",
            'ca_ES' => "Catalan",
            'cb_IQ' => "Sorani Kurdish",
            'ck_US' => "Cherokee",
            'co_FR' => "Corsican",
            'cs_CZ' => "Czech",
            'cx_PH' => "Cebuano",
            'cy_GB' => "Welsh",
            'da_DK' => "Danish",
            'de_DE' => "German",
            'el_GR' => "Greek",
            'en_GB' => "English (UK)",
            'en_IN' => "English (India)",
            'en_PI' => "English (Pirate)",
            'en_UD' => "English (Upside Down)",
            'en_US' => "English (US)",
            'eo_EO' => "Esperanto",
            'es_CL' => "Spanish (Chile)",
            'es_CO' => "Spanish (Colombia)",
            'es_ES' => "Spanish (Spain)",
            'es_LA' => "Spanish",
            'es_MX' => "Spanish (Mexico)",
            'es_VE' => "Spanish (Venezuela)",
            'et_EE' => "Estonian",
            'eu_ES' => "Basque",
            'fa_IR' => "Persian",
            'fb_LT' => "Leet Speak",
            'ff_NG' => "Fulah",
            'fi_FI' => "Finnish",
            'fo_FO' => "Faroese",
            'fr_CA' => "French (Canada)",
            'fr_FR' => "French (France)",
            'fy_NL' => "Frisian",
            'ga_IE' => "Irish",
            'gl_ES' => "Galician",
            'gn_PY' => "Guarani",
            'gu_IN' => "Gujarati",
            'gx_GR' => "Classical Greek",
            'ha_NG' => "Hausa",
            'he_IL' => "Hebrew",
            'hi_IN' => "Hindi",
            'hr_HR' => "Croatian",
            'ht_HT' => "Haitian Creole",
            'hu_HU' => "Hungarian",
            'hy_AM' => "Armenian",
            'id_ID' => "Indonesian",
            'ig_NG' => "Igbo",
            'is_IS' => "Icelandic",
            'it_IT' => "Italian",
            'ja_JP' => "Japanese",
            'ja_KS' => "Japanese (Kansai)",
            'jv_ID' => "Javanese",
            'ka_GE' => "Georgian",
            'kk_KZ' => "Kazakh",
            'km_KH' => "Khmer",
            'kn_IN' => "Kannada",
            'ko_KR' => "Korean",
            'ku_TR' => "Kurdish (Kurmanji)",
            'ky_KG' => "Kyrgyz",
            'la_VA' => "Latin",
            'lg_UG' => "Ganda",
            'li_NL' => "Limburgish",
            'ln_CD' => "Lingala",
            'lo_LA' => "Lao",
            'lt_LT' => "Lithuanian",
            'lv_LV' => "Latvian",
            'mg_MG' => "Malagasy",
            'mi_NZ' => "Māori",
            'mk_MK' => "Macedonian",
            'ml_IN' => "Malayalam",
            'mn_MN' => "Mongolian",
            'mr_IN' => "Marathi",
            'ms_MY' => "Malay",
            'mt_MT' => "Maltese",
            'my_MM' => "Burmese",
            'nb_NO' => "Norwegian (bokmal)",
            'nd_ZW' => "Ndebele",
            'ne_NP' => "Nepali",
            'nl_BE' => "Dutch (België)",
            'nl_NL' => "Dutch",
            'nn_NO' => "Norwegian (nynorsk)",
            'ny_MW' => "Chewa",
            'or_IN' => "Oriya",
            'pa_IN' => "Punjabi",
            'pl_PL' => "Polish",
            'ps_AF' => "Pashto",
            'pt_BR' => "Portuguese (Brazil)",
            'pt_PT' => "Portuguese (Portugal)",
            'qc_GT' => "Quiché",
            'qu_PE' => "Quechua",
            'rm_CH' => "Romansh",
            'ro_RO' => "Romanian",
            'ru_RU' => "Russian",
            'rw_RW' => "Kinyarwanda",
            'sa_IN' => "Sanskrit",
            'sc_IT' => "Sardinian",
            'se_NO' => "Northern Sámi",
            'si_LK' => "Sinhala",
            'sk_SK' => "Slovak",
            'sl_SI' => "Slovenian",
            'sn_ZW' => "Shona",
            'so_SO' => "Somali",
            'sq_AL' => "Albanian",
            'sr_RS' => "Serbian",
            'sv_SE' => "Swedish",
            'sw_KE' => "Swahili",
            'sy_SY' => "Syriac",
            'sz_PL' => "Silesian",
            'ta_IN' => "Tamil",
            'te_IN' => "Telugu",
            'tg_TJ' => "Tajik",
            'th_TH' => "Thai",
            'tk_TM' => "Turkmen",
            'tl_PH' => "Filipino",
            'tl_ST' => "Klingon",
            'tr_TR' => "Turkish",
            'tt_RU' => "Tatar",
            'tz_MA' => "Tamazight",
            'uk_UA' => "Ukrainian",
            'ur_PK' => "Urdu",
            'uz_UZ' => "Uzbek",
            'vi_VN' => "Vietnamese",
            'wo_SN' => "Wolof",
            'xh_ZA' => "Xhosa",
            'yi_DE' => "Yiddish",
            'yo_NG' => "Yoruba",
            'zh_CN' => "Simplified Chinese (China)",
            'zh_HK' => "Traditional Chinese (Hong Kong)",
            'zh_TW' => "Traditional Chinese (Taiwan)",
            'zu_ZA' => "Zulu",
            'zz_TR' => "Zazaki"
        );

        return $locales;
    }
}
