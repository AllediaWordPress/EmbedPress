<?php
namespace EmbedPress\Ends\Back;

use \EmbedPress\Ends\Handler as EndHandlerAbstract;
use \EmbedPress\Shortcode;
use \EmbedPress\Core;
use \Embera\Embera;

(defined('ABSPATH') && defined('EMBEDPRESS_IS_LOADED')) or die("No direct script access allowed.");

/**
 * The admin-facing functionality of the plugin.
 * Defines the plugin name, version, and enqueue the admin-specific stylesheets and scripts.
 *
 * @package     EmbedPress
 * @subpackage  EmbedPress/Ends/Back
 * @author      PressShack <help@pressshack.com>
 * @copyright   Copyright (C) 2017 Open Source Training, LLC. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */
class Handler extends EndHandlerAbstract
{
    /**
     * Method that register all scripts for the admin area.
     *
     * @since   1.0.0
     *
     * @return  void
     */
    public function enqueueScripts()
    {
        $plgSettings = Core::getSettings();

        $urlSchemes = apply_filters('embedpress:getAdditionalURLSchemes', $this->getUrlSchemes());

        wp_enqueue_script("bootbox-bootstrap", EMBEDPRESS_URL_ASSETS .'js/vendor/bootstrap/bootstrap.min.js', array('jquery'), $this->pluginVersion, true);
        wp_enqueue_script("bootbox", EMBEDPRESS_URL_ASSETS .'js/vendor/bootbox.min.js', array('jquery', 'bootbox-bootstrap'), $this->pluginVersion, true);
        wp_enqueue_script($this->pluginName, EMBEDPRESS_URL_ASSETS .'js/preview.js', array('jquery', 'bootbox'), $this->pluginVersion, true);
        wp_localize_script($this->pluginName, '$data', array(
            'previewSettings'       => array(
                'baseUrl'    => get_site_url() .'/',
                'versionUID' => $this->pluginVersion,
                'debug'      => true
            ),
            'EMBEDPRESS_SHORTCODE'  => EMBEDPRESS_SHORTCODE,
            'EMBEDPRESS_URL_ASSETS' => EMBEDPRESS_URL_ASSETS,
            'urlSchemes'            => $urlSchemes
        ));

        $installedPlugins = Core::getPlugins();
        if (count($installedPlugins) > 0) {
            foreach ($installedPlugins as $plgSlug => $plgNamespace) {
                $plgScriptPathRelative = "assets/js/embedpress.{$plgSlug}.js";
                $plgName = "embedpress-{$plgSlug}";

                if (file_exists(WP_PLUGIN_DIR . "/{$plgName}/{$plgScriptPathRelative}")) {
                    wp_enqueue_script($plgName, plugins_url($plgName) .'/'. $plgScriptPathRelative, array($this->pluginName), $this->pluginVersion, true);
                }
            }
        }
    }

    /**
     * Method that register all stylesheets for the admin area.
     *
     * @since   1.0.0
     * @static
     *
     * @return  void
     */
    public static function enqueueStyles()
    {
        wp_enqueue_style('embedpress-grid', plugins_url('embedpress/assets/css/simple-grid.css'));
        wp_enqueue_style('embedpress-admin', plugins_url('embedpress/assets/css/admin.css'));
        wp_enqueue_style('embedpress-addons', plugins_url('embedpress/assets/css/addons.css'));
    }

    /**
     * Method that receive a string via AJAX and return the decoded-shortcoded-version of that string.
     *
     * @since   1.0.0
     *
     * @return  void
     */
    public function doShortcodeReceivedViaAjax()
    {
        $subject = isset($_POST['subject']) ? $_POST['subject'] : "";

        $response = array(
            'data' => Shortcode::parseContent($subject, true)
        );

        header('Content-Type:application/json;charset=UTF-8');
        echo json_encode($response);

        exit();
    }

    /**
     * Method that receive an url via AJAX and return the info about that url/embed.
     *
     * @since   1.0.0
     *
     * @return  void
     */
    public function getUrlInfoViaAjax()
    {
        $url = isset($_GET['url']) ? trim($_GET['url']) : "";

        $response = array(
            'url'             => $url,
            'canBeResponsive' => false
        );

        if (!!strlen($response['url'])) {
            $embera = new Embera();

            $additionalServiceProviders = Core::getAdditionalServiceProviders();
            if (!empty($additionalServiceProviders)) {
                foreach ($additionalServiceProviders as $serviceProviderClassName => $serviceProviderUrls) {
                    Shortcode::addServiceProvider($serviceProviderClassName, $serviceProviderUrls, $embera);
                }
            }

            $urlInfo = $embera->getUrlInfo($response['url']);
            if (isset($urlInfo[$response['url']])) {
                $urlInfo = (object)$urlInfo[$response['url']];
                $response['canBeResponsive'] = Core::canServiceProviderBeResponsive($urlInfo->provider_alias);
            }
        }

        header('Content-Type:application/json;charset=UTF-8');
        echo json_encode($response);

        exit();
    }

    /**
     * Returns a list of supported URL schemes for the preview script
     *
     * @return array
     */
    public function getUrlSchemes()
    {
        return array(
            // PollDaddy
            '*.polldaddy.com/s/*',
            '*.polldaddy.com/poll/*',
            '*.polldaddy.com/ratings/*',
            'polldaddy.com/s/*',
            'polldaddy.com/poll/*',
            'polldaddy.com/ratings/*',

            // VideoPress
            'videopress.com/v/*',

            // Tumblr
            '*.tumblr.com/post/*',

            // SmugMug
            'smugmug.com/*',
            '*.smugmug.com/*',

            // SlideShare
            'slideshare.net/*/*',
            '*.slideshare.net/*/*',

            // Reddit
            'reddit.com/r/[^/]+/comments/*',

            // Photobucket
            'i*.photobucket.com/albums/*',
            'gi*.photobucket.com/groups/*',

            // Cloudup
            'cloudup.com/*',

            // Imgur
            'imgur.com/*',
            'i.imgur.com/*',

            // YouTube (http://www.youtube.com/)
            'youtube.com/watch\\?*',

            // Flickr (http://www.flickr.com/)
            'flickr.com/photos/*/*',
            'flic.kr/p/*',

            // Viddler (http://www.viddler.com/)
            'viddler.com/v/*',

            // Hulu (http://www.hulu.com/)
            'hulu.com/watch/*',

            // Vimeo (http://vimeo.com/)
            'vimeo.com/*',
            'vimeo.com/groups/*/videos/*',

            // CollegeHumor (http://www.collegehumor.com/)
            'collegehumor.com/video/*',

            // Deviantart.com (http://www.deviantart.com)
            '*.deviantart.com/art/*',
            '*.deviantart.com/*#/d*',
            'fav.me/*',
            'sta.sh/*',

            // SlideShare (http://www.slideshare.net/)

            // chirbit.com (http://www.chirbit.com/)
            'chirb.it/*',

            // nfb.ca (http://www.nfb.ca/)
            '*.nfb.ca/film/*',

            // Scribd (http://www.scribd.com/)
            'scribd.com/doc/*',

            // Dotsub (http://dotsub.com/)
            'dotsub.com/view/*',

            // Animoto (http://animoto.com/)
            'animoto.com/play/*',

            // Rdio (http://rdio.com/)
            '*.rdio.com/artist/*',
            '*.rdio.com/people/*',

            // MixCloud (http://mixcloud.com/)
            'mixcloud.com/*/*/',

            // FunnyOrDie (http://www.funnyordie.com/)
            'funnyordie.com/videos/*',

            // Ted (http://ted.com)
            'ted.com/talks/*',

            // Sapo Videos (http://videos.sapo.pt)
            'videos.sapo.pt/*',

            // Official FM (http://official.fm)
            'official.fm/tracks/*',
            'official.fm/playlists/*',

            // HuffDuffer (http://huffduffer.com)
            'huffduffer.com/*/*',

            // Shoudio (http://shoudio.com)
            'shoudio.com/*',
            'shoud.io/*',

            // Moby Picture (http://www.mobypicture.com)
            'mobypicture.com/user/*/view/*',
            'moby.to/*',

            // 23HQ (http://www.23hq.com)
            '23hq.com/*/photo/*',

            // Cacoo (https://cacoo.com)
            'cacoo.com/diagrams/*',

            // Dipity (http://www.dipity.com)
            'dipity.com/*/*/',

            // Roomshare (http://roomshare.jp)
            'roomshare.jp/post/*',
            'roomshare.jp/en/post/*',

            // Dailymotion (http://www.dailymotion.com)
            'dailymotion.com/video/*',

            // Crowd Ranking (http://crowdranking.com)
            'c9ng.com/*/*',

            // CircuitLab (https://www.circuitlab.com/)
            'circuitlab.com/circuit/*',

            // Coub (http://coub.com/)
            'coub.com/view/*',
            'coub.com/embed/*',

            // SpeakerDeck (https://speakerdeck.com)
            'speakerdeck.com/*/*',

            // Instagram (https://instagram.com)
            'instagram.com/p/*',
            'instagr.am/p/*',

            // SoundCloud (http://soundcloud.com/)
            'soundcloud.com/*',

            // Kickstarter (http://www.kickstarter.com)
            'kickstarter.com/projects/*',

            // Ustream (http://www.ustream.tv)
            '*.ustream.tv/*',
            '*.ustream.com/*',

            // Daily Mile (http://www.dailymile.com)
            'dailymile.com/people/*/entries/*',

            // Sketchfab (http://sketchfab.com)
            'sketchfab.com/models/*',
            'sketchfab.com/*/folders/*',

            // Meetup (http://www.meetup.com)
            'meetup.com/*',
            'meetu.ps/*',

            // AudioSnaps (http://audiosnaps.com)
            'audiosnaps.com/k/*',

            // RapidEngage (https://rapidengage.com)
            'rapidengage.com/s/*',

            // Getty Images (http://www.gettyimages.com/)
            'gty.im/*',
            'gettyimages.com/detail/photo/*',

            // amCharts Live Editor (http://live.amcharts.com/)
            'live.amcharts.com/*',

            // Infogram (https://infogr.am/)
            'infogr.am/*',

            // ChartBlocks (http://www.chartblocks.com/)
            'public.chartblocks.com/c/*',

            // ReleaseWire (http://www.releasewire.com/)
            'rwire.com/*',

            // ShortNote (https://www.shortnote.jp/)
            'shortnote.jp/view/notes/*',

            // EgliseInfo (http://egliseinfo.catholique.fr/)
            'egliseinfo.catholique.fr/*',

            // Silk (http://www.silk.co/)
            '*.silk.co/explore/*',
            '*.silk.co/s/embed/*',

            // Twitter
            'twitter.com/*/status/*',
            'twitter.com/i/moments/*',
            'twitter.com/*/timelines/*',

            // http://bambuser.com
            'bambuser.com/v/*',

            // https://clyp.it
            'clyp.it/*',

            // https://gist.github.com
            'gist.github.com/*/*',

            // http://issuu.com
             'issuu.com/*',

            // https://portfolium.com
            'portfolium.com/*',

            // https://www.reverbnation.com
            'reverbnation.com/*',

            // http://rutube.ru
            'rutube.ru/video/*',

            // https://spotify.com/
            'open.spotify.com/*',

            // http://www.videojug.com
            'videojug.com/*',

            // https://vine.com
            'vine.co/v/*',

            // Facebook
            'facebook.com/*',

            // Google Shortened Url
            'goo.gl/*',

            // Google Maps
            'google.com/*',
            'google.com.*/*',
            'maps.google.com/*',

            // Google Docs
            'docs.google.com/presentation/*',
            'docs.google.com/document/*',
            'docs.google.com/spreadsheets/*',
            'docs.google.com/forms/*',
            'docs.google.com/drawings/*',

            // Twitch.tv
            '*.twitch.tv/*',
            'twitch.tv/*',

            // Giphy
            '*.giphy.com/gifs/*',
            'giphy.com/gifs/*',
            'i.giphy.com/*',
            'gph.is/*',

            // Wistia
            '*.wistia.com/medias/*',
            'fast.wistia.com/embed/medias/*.jsonp'
        );
    }
}
