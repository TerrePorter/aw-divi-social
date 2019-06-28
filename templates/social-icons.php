<?php
/**
 * Created by PhpStorm.
 * User: jonathan
 * Date: 2018/01/06
 * Time: 11:03 PM
 *
 * Modified
 * User: Terre Porter
 * Date: 2019/06/24
 * Time: 11:03 AM
 */

$options = array(
    'dribbble'   => 'Dribbble',
    'facebook'  => 'Facebook',
    'flikr'      => 'Flikr',
    'google'     => 'Google',
    'houzz'      => 'Houzz',
    'instagram'  => 'Instagram',
    'linkedin'   => 'Linkedin',
    'meetup'     => 'Meetup',
    'myspace'    => 'MySpace',
    'pinterest'  => 'Pinterest',
    'podcast'    => 'Podcast',
    'skype'      => 'Skype',
    'soundcloud' => 'SoundCloud',
    'spotify'    => 'Spotify',
    'tumblr'     => 'Tumblr',
    'twitter'    => 'Twitter',
    'yelp'       => 'Yelp',
    'youtube'    => 'YouTube',
    'vimeo'      => 'Vimeo',
    'vine'       => 'Vine',
);

echo '<ul class="et-social-icons">';

foreach ($options as $optionKey => $optionValue) {
    //
    if ( 'on' === et_get_option( "divi_show_{$optionKey}_icon", 'on' ) ) {
        ?>
        <li class="et-social-icon et-social-<?php echo $optionKey?>">
            <a href="<?php echo esc_url(et_get_option("divi_{$optionKey}_url", '#')); ?>"
               title="<?php echo esc_html_e(et_get_option("divi_{$optionKey}_title", ''), 'Divi'); ?>"
               class="icon">
                <span><?php esc_html_e($optionValue, 'Divi'); ?></span>
            </a>
        </li>
        <?php
    }
}

echo '</ul>';

?>