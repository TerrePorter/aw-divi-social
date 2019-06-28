<?php


class et_build_epanel_handler
{
    /**
     *
     */
    function et_build_epanel()
    {
        global $themename, $shortname, $options, $et_disabled_jquery, $epanelMainTabs;

        // load theme settings array
        et_load_core_options();

        $tabs = array();
        $default_tab_names = array(
            'ad' => _x('Ads', 'site ads placement areas', $themename),
            'colorization' => _x('Colorization', 'site color scheme', $themename),
            'general' => _x('General', 'general options', $themename),
            'socialicons' => _x('Social Icons', 'social icons', $themename),
            'integration' => _x('Integration', 'integrate third-party code', $themename),
            'layout' => _x('Layout', 'page/post', $themename),
            'navigation' => _x('Navigation', 'navigation menu', $themename),
            'seo' => _x('SEO', 'search engine optimization', $themename),
            'support' => _x('Support', 'documentation links', $themename),
            'updates' => _x('Updates', 'theme updates', $themename),
        );

        // insert social icons tab
        array_splice($epanelMainTabs, array_search('general', $epanelMainTabs)+1, 0, ['socialicons']);

        /**
         * Filters the data used to construct ePanel's layout.
         *
         * @param array $options
         * @since 3.2.1
         *
         */
        $options = apply_filters('et_epanel_layout_data', $options);

        //echo "<pre>";
        //print_r($options);
        //echo "</pre>";

        /**
         * Filters the slugs/ids for ePanel's tabs.
         *
         * @param string[] $tab_slugs
         * @since 1.0
         * @since 3.2.1 Deprecated
         *
         * @deprecated
         *
         */
        $epanelMainTabs = apply_filters('epanel_page_maintabs', $epanelMainTabs);


        foreach ($epanelMainTabs as $tab_slug) {
            if (isset($default_tab_names[$tab_slug])) {
                $tabs[$tab_slug] = $default_tab_names[$tab_slug];
            }
        }

        /**
         * Filters ePanel's localized tab names.
         *
         * @param string[] $tabs {
         *
         * @type string $tab_slug Localized tab name.
         *     ...
         * }
         * @since 3.2.1
         *
         */
        $tabs = apply_filters('et_epanel_tab_names', $tabs);


        et_core_nonce_verified_previously();

        if (isset($_GET['saved'])) {
            if ($_GET['saved']) {
                echo '<div id="message" class="updated fade"><p><strong>' . esc_html($themename) . ' ' . esc_html__('settings saved.',
                        $themename) . '</strong></p></div>';
            }
        }
        if (isset($_GET['reset'])) {
            if ($_GET['reset']) {
                echo '<div id="message" class="updated fade"><p><strong>' . esc_html($themename) . ' ' . esc_html__('settings reset.',
                        $themename) . '</strong></p></div>';
            }
        }


        ?>

        <div id="wrapper">
            <div id="panel-wrap">


                <div id="epanel-top">
                    <button class="et-save-button" id="epanel-save-top"><?php esc_html_e('Save Changes',
                            $themename); ?></button>
                </div>

                <form method="post" id="main_options_form" enctype="multipart/form-data">
                    <div id="epanel-wrapper">
                        <div id="epanel" class="et-onload">
                            <div id="epanel-content-wrap">
                                <div id="epanel-content">
                                    <div id="epanel-header">
                                        <h1 id="epanel-title"><?php printf(esc_html__('%s Theme Options', $themename),
                                                esc_html($themename)); ?></h1>
                                        <a href="#" class="et-defaults-button epanel-reset"
                                           title="<?php esc_attr_e('Reset to Defaults', $themename); ?>"><span
                                                    class="label"><?php esc_html_e('Reset to Defaults',
                                                    $themename); ?></span></a>
                                        <?php echo et_core_esc_previously(et_core_portability_link('epanel',
                                            array('class' => 'et-defaults-button epanel-portability'))); ?>
                                    </div>
                                    <ul id="epanel-mainmenu">
                                        <?php
                                        foreach ($tabs as $tab_slug => $tab_name) {
                                            if ('ad' === $tab_slug) {
                                                $tab_slug = 'advertisements';
                                            }

                                            printf('<li><a href="#wrap-%1$s">%2$s</a></li>', esc_attr($tab_slug),
                                                esc_html($tab_name));
                                        }

                                        do_action('epanel_render_maintabs', $epanelMainTabs);
                                        ?>
                                    </ul><!-- end epanel mainmenu -->

                                    <?php
                                    foreach ($options as $value) {
                                        if (!isset($value['type'])) {
                                            continue;
                                        }

                                        if (!empty($value['depends_on'])) {
                                            // function defined in 'depends on' key returns false, if a setting shouldn't be displayed
                                            if (!call_user_func($value['depends_on'])) {
                                                continue;
                                            }
                                        }

                                        $is_new_global_setting = false;
                                        $global_setting_main_name = $global_setting_sub_name = '';

                                        if (isset($value['is_global']) && $value['is_global'] && !empty($value['id'])) {
                                            $is_new_global_setting = true;
                                            $global_setting_main_name = isset($value['main_setting_name']) ? sanitize_text_field($value['main_setting_name']) : '';
                                            $global_setting_sub_name = isset($value['sub_setting_name']) ? sanitize_text_field($value['sub_setting_name']) : '';
                                        }

                                        // Is hidden option
                                        $is_hidden_option = isset($value['hide_option']) && $value['hide_option'];
                                        $hidden_option_classname = $is_hidden_option ? ' et-hidden-option' : '';
                                        $disabled = $is_hidden_option ? 'disabled="disabled"' : '';

                                        if (in_array($value['type'], array(
                                            'text',
                                            'textlimit',
                                            'textarea',
                                            'select',
                                            'checkboxes',
                                            'different_checkboxes',
                                            'colorpicker',
                                            'textcolorpopup',
                                            'upload',
                                            'callback_function',
                                            'et_color_palette',
                                            'password'
                                        ))) { ?>
                                            <div class="et-epanel-box">
                                                <div class="et-box-title">
                                                    <h3><?php echo esc_html($value['name']); ?></h3>
                                                    <div class="et-box-descr">
                                                        <p><?php
                                                            echo wp_kses($value['desc'],
                                                                array(
                                                                    'a' => array(
                                                                        'href' => array(),
                                                                        'title' => array(),
                                                                        'target' => array(),
                                                                    ),
                                                                )
                                                            );
                                                            ?></p>
                                                    </div> <!-- end et-box-desc-content div -->
                                                </div> <!-- end div et-box-title -->

                                                <div class="et-box-content">

                                                    <?php
                                                    if (in_array($value['type'], array('text', 'password'))) {
                                                        $this->processType_TP($value, $global_setting_main_name, $global_setting_sub_name, $is_new_global_setting);

                                                    } elseif ('textlimit' === $value['type']) {
                                                        $this->processType_GenericBasicInput('textlimit',$value, $is_new_global_setting, $global_setting_main_name, $global_setting_sub_name, $shortname);

                                                    } elseif ('colorpicker' === $value['type']) {
                                                        echo '<div id="colorpickerHolder"></div>';

                                                    } elseif ('textcolorpopup' === $value['type']) {
                                                        $this->processType_GenericBasicInput('textcolorpopup', $value, $is_new_global_setting, $global_setting_main_name, $global_setting_sub_name, $shortname);

                                                    } elseif ('textarea' === $value['type']) {
                                                        $this->processType_GenericBasicInput('textarea', $value, $is_new_global_setting, $global_setting_main_name, $global_setting_sub_name, $shortname);

                                                    } elseif ('upload' === $value['type']) {
                                                        $this->processType_Upload(   $value, $themename, $is_new_global_setting, $global_setting_main_name, $global_setting_sub_name);

                                                    } elseif ('select' === $value['type']) {
                                                        $this->processType_Select($value);

                                                    } elseif ('checkboxes' === $value['type']) {
                                                        $this->processType_Checkboxes($value, $themename);

                                                    } elseif ('different_checkboxes' === $value['type']) {
                                                        $this->processType_DifferentCheckboxes($value);

                                                    } elseif ('callback_function' === $value['type']) {
                                                        call_user_func($value['function_name']);

                                                    } elseif ('et_color_palette' === $value['type']) {
                                                        $this->processType_EtColorPalette($value, $is_new_global_setting, $global_setting_main_name, $global_setting_sub_name);
                                                    }
                                                    ?>
                                                </div> <!-- end et-box-content div -->
                                                <span class="et-box-description"></span>
                                            </div> <!-- end et-epanel-box div -->

                                            <?php
                                        } elseif ('checkbox' === $value['type'] || 'checkbox2' === $value['type']) {
                                            $this->processType_CheckboxCheckbox2($value, $hidden_option_classname, $is_new_global_setting, $global_setting_main_name, $global_setting_sub_name, $is_hidden_option, $themename, $disabled);

                                        } elseif ('checkbox_list' === $value['type']) {
                                            $this->processType_CheckboxList($value, $hidden_option_classname, $themename);

                                        } elseif ('support' === $value['type']) { ?>

                                            <div class="inner-content">
                                                <?php include get_template_directory() . "/includes/functions/" . $value['name'] . ".php"; ?>
                                            </div>

                                        <?php } elseif ('contenttab-wrapstart' === $value['type'] || 'subcontent-start' === $value['type']) { ?>

                                            <?php $et_contenttab_class = 'contenttab-wrapstart' === $value['type'] ? 'et-content-div' : 'et-tab-content'; ?>

                                            <div id="<?php echo esc_attr($value['name']); ?>" class="<?php echo esc_attr($et_contenttab_class); ?>">

                                        <?php } elseif ('contenttab-wrapend' === $value['type'] || 'subcontent-end' === $value['type']) { ?>

                                            </div> <!-- end <?php echo esc_html($value['name']); ?> div -->

                                        <?php } elseif ('subnavtab-start' === $value['type']) { ?>

                                            <ul class="et-id-tabs">

                                        <?php } elseif ('subnavtab-end' === $value['type']) { ?>

                                            </ul>

                                        <?php } elseif ('subnav-tab' === $value['type']) { ?>

                                            <li><a href="#<?php echo esc_attr($value['name']); ?>"><span
                                                            class="pngfix"><?php echo esc_html($value['desc']); ?></span></a>
                                            </li>

                                        <?php } elseif ('panelwrapper-start' === $value['type']) { ?>

                                            <?php $et_panelwrapper_class = (isset($value['class'])? $value['class'] : (isset($value['name'])? $value['name'] : '')); ?>

                                            <div class="<?php echo $et_panelwrapper_class?>" style="display: none; padding: 20px; border: 1px solid #cdcdcd; margin-bottom: 20px;">

                                        <?php } elseif ('panelwrapper-end' === $value['type']) { ?>

                                            </div>

                                        <?php } elseif ($value['type'] === "clearfix") { ?>

                                            <div class="et-clearfix"></div>

                                        <?php } ?>

                                    <?php } //end foreach ($options as $value) ?>

                                </div> <!-- end epanel-content div -->
                            </div> <!-- end epanel-content-wrap div -->
                        </div> <!-- end epanel div -->
                    </div> <!-- end epanel-wrapper div -->

                    <div id="epanel-bottom">
                        <?php wp_nonce_field('epanel_nonce'); ?>
                        <button class="et-save-button" name="save" id="epanel-save"><?php esc_html_e('Save Changes',
                                $themename); ?></button>

                        <input type="hidden" name="action" value="save_epanel"/>
                    </div><!-- end epanel-bottom div -->

                </form>

                <div class="reset-popup-overlay">
                    <div class="defaults-hover">
                        <div class="reset-popup-header"><?php esc_html_e('Reset', $themename); ?></div>
                        <?php echo et_get_safe_localization(__('This will return all of the settings throughout the options page to their default values. <strong>Are you sure you want to do this?</strong>',
                            $themename)); ?>
                        <div class="et-clearfix"></div>
                        <form method="post">
                            <?php wp_nonce_field('et-nojs-reset_epanel', '_wpnonce_reset'); ?>
                            <input name="reset" type="submit" value="<?php esc_attr_e('Yes', $themename); ?>"
                                   id="epanel-reset"/>
                            <input type="hidden" name="action" value="reset"/>
                        </form>
                        <span class="no"><?php esc_html_e('No', $themename); ?></span>
                    </div>
                </div>

            </div> <!-- end panel-wrap div -->
        </div> <!-- end wrapper div -->

        <div id="epanel-ajax-saving">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/core/admin/images/ajax-loader.gif'); ?>"
                 alt="loading" id="loading"/>
        </div>

        <script type="text/template" id="epanel-yes-no-button-template">
            <div class="et_pb_yes_no_button_wrapper">
                <div class="et_pb_yes_no_button"><!-- .et_pb_on_state || .et_pb_off_state -->
                    <span class="et_pb_value_text et_pb_on_value"><?php esc_html_e('Enabled', $themename); ?></span>
                    <span class="et_pb_button_slider"></span>
                    <span class="et_pb_value_text et_pb_off_value"><?php esc_html_e('Disabled', $themename); ?></span>
                </div>
            </div>
        </script>

        <style type="text/css">
            #epanel p.postinfo-author .mark:after {
                content: '<?php esc_html_e( "Author", $themename ); ?>';
            }

            #epanel p.postinfo-date .mark:after {
                content: '<?php esc_html_e( "Date", $themename ); ?>';
            }

            #epanel p.postinfo-categories .mark:after {
                content: '<?php esc_html_e( "Categories", $themename ); ?>';
            }

            #epanel p.postinfo-comments .mark:after {
                content: '<?php esc_html_e( "Comments", $themename ); ?>';
            }

            #epanel p.postinfo-rating_stars .mark:after {
                content: '<?php esc_html_e( "Ratings", $themename ); ?>';
            }
        </style>

        <?php
    }


    /**
     * Process option type for text or password
     */
    private function processType_TP($value, $global_setting_main_name, $global_setting_sub_name, $is_new_global_setting)
    {

        if ('et_automatic_updates_options' === $global_setting_main_name) {
            if (!$setting = get_site_option($global_setting_main_name)) {
                $setting = get_option($global_setting_main_name, array());
            }

            $et_input_value = isset($setting[$global_setting_sub_name]) ? $setting[$global_setting_sub_name] : '';
        } else {
            $et_input_value = et_get_option($value['id'], '', '', false, $is_new_global_setting,
                $global_setting_main_name, $global_setting_sub_name);
            $et_input_value = !empty($et_input_value) ? $et_input_value : $value['std'];

        }

        $et_input_value = stripslashes($et_input_value);

        if ('password' === $value['type'] && !empty($et_input_value)) {
            $et_input_value = _et_epanel_password_mask();
        }
        ?>

        <input name="<?php echo esc_attr($value['id']); ?>" id="<?php echo esc_attr($value['id']); ?>"
               type="<?php echo esc_attr($value['type']); ?>" value="<?php echo esc_attr($et_input_value); ?>"/>
        <?php
    }

    private function processType_GenericBasicInput(
        $inputMode = '',
        $value,
        $is_new_global_setting,
        $global_setting_main_name,
        $global_setting_sub_name,
        $shortname
    ) {

        // setup input options
        if (isset($value['id'])) {
            $inputName = 'name="' . esc_attr($value['id']) . '"';
            $inputId = 'id="' . esc_attr($value['id']) . '"';
        }

        if (isset($value['class'])) {
            $inputClass = 'class="' . esc_attr($value['class']) . '"';
        }

        if (isset($value['max'])) {
            $inputMaxlength = 'maxlength="' . esc_attr($value['max']) . '"';
            $inputMaxlength .= ' size="' . esc_attr($value['max']) . '"';
        }

        $et_input_value = et_get_option($value['id'], '', '', false, $is_new_global_setting, $global_setting_main_name,
            $global_setting_sub_name);
        $et_input_value = !empty($et_input_value) ? $et_input_value : $value['std'];
        $et_input_value = 'value="' . esc_attr(stripslashes($et_input_value)) . '"';

        //
        switch ($inputMode) {


            case 'textcolorpopup':
                //
                $inputClass = 'colorpopup';

            case 'textlimit':
                //
                $inputType = 'type="text"';

                //
                $template = '<%s %s %s %s %s %s>';
                $data = [
                    (isset($inputName) ? $inputName : ''),
                    (isset($inputId) ? $inputId : ''),
                    (isset($inputType) ? $inputType : ''),
                    (isset($inputClass) ? $inputClass : ''),
                    (isset($inputMaxlength) ? $inputMaxlength : ''),
                    $et_input_value
                ];
                break;

            case 'textarea':
                // get the custom css value from WP custom CSS option if supported
                if (($shortname . '_custom_css') === $value['id'] && function_exists('wp_get_custom_css')) {
                    $et_textarea_value = wp_get_custom_css();
                    $et_textarea_value = strip_tags($et_textarea_value);
                } else {
                    $et_textarea_value = et_get_option($value['id'], '', '', false, $is_new_global_setting,
                        $global_setting_main_name, $global_setting_sub_name);
                    $et_textarea_value = !empty($et_textarea_value) ? $et_textarea_value : $value['std'];
                }

                //
                $template = '<textarea %s %s>%s</textarea>';
                $data = [
                    (isset($inputName) ? $inputName : ''),
                    (isset($inputId) ? $inputId : ''),
                    esc_textarea($et_textarea_value),
                ];
                break;

            default:
                return '';

        }

        //
        echo vsprintf($template, $data);

    }

    private function processType_Upload(
        $value,
        $themename,
        $is_new_global_setting,
        $global_setting_main_name,
        $global_setting_sub_name
    ) {
        //
        $et_upload_button_data = isset($value['button_text']) ? sprintf(' data-button_text="%1$s"',
            esc_attr($value['button_text'])) : '';
        ?>

        <input id="<?php echo esc_attr($value['id']); ?>" class="et-upload-field" type="text" size="90"
               name="<?php echo esc_attr($value['id']); ?>"
               value="<?php echo esc_url(et_get_option($value['id'], '', '', false, $is_new_global_setting,
                   $global_setting_main_name, $global_setting_sub_name)); ?>"/>
        <div class="et-upload-buttons">
            <span class="et-upload-image-reset"><?php esc_html_e('Reset', $themename); ?></span>
            <input class="et-upload-image-button"
                   type="button"<?php echo et_core_esc_previously($et_upload_button_data); ?>
                   value="<?php esc_attr_e('Upload', $themename); ?>"/>
        </div>

        <div class="clear"></div>
        <?php
    }

    private function processType_Select($value)
    {
        ?>
        <select name="<?php echo esc_attr($value['id']); ?>" id="<?php echo esc_attr($value['id']); ?>">
            <?php foreach ($value['options'] as $option_key => $option) { ?>
                <?php
                $et_select_active = '';
                $et_use_option_values = (isset($value['et_array_for']) && in_array($value['et_array_for'],
                        array('pages', 'categories'))) ||
                (isset($value['et_save_values']) && $value['et_save_values']) ? true : false;

                $et_option_db_value = et_get_option($value['id']);

                if (($et_use_option_values && ($et_option_db_value === $option_key)) || (stripslashes($et_option_db_value) === trim(stripslashes($option))) || (!$et_option_db_value && isset($value['std']) && stripslashes($option) === stripslashes($value['std']))) {
                    $et_select_active = ' selected="selected"';
                }
                ?>
                <option<?php if ($et_use_option_values) {
                    echo ' value="' . esc_attr($option_key) . '"';
                } ?> <?php echo et_core_esc_previously($et_select_active); ?>><?php echo esc_html(trim($option)); ?></option>
            <?php } ?>
        </select>
        <?php
    }

    private function processType_Checkboxes($value, $themename)
    {

        if (empty($value['options'])) {
            esc_html_e("You don't have pages", $themename);
        } else {
            $i = 1;
            $className = 'inputs';
            if (isset($value['excludeDefault']) && $value['excludeDefault'] === 'true') {
                $className .= ' different';
            }

            foreach ($value['options'] as $option) {
                $checked = "";
                $class_name_last = 0 === $i % 3 ? ' last' : '';

                if (et_get_option($value['id'])) {
                    if (in_array($option, et_get_option($value['id']))) {
                        $checked = "checked=\"checked\"";
                    }
                }

                $et_checkboxes_label = $value['id'] . '-' . $option;
                if ('custom' === $value['usefor']) {
                    $et_helper = (array)$value['helper'];
                    $et_checkboxes_value = $et_helper[$option];
                } else {
                    if ('taxonomy_terms' === $value['usefor'] && isset($value['taxonomy_name'])) {
                        $et_checkboxes_term = get_term_by('id', $option, $value['taxonomy_name']);
                        $et_checkboxes_value = sanitize_text_field($et_checkboxes_term->name);
                    } else {
                        $et_checkboxes_value = ('pages' === $value['usefor']) ? get_pagename($option) : get_categname($option);
                    }
                }
                ?>

                <p class="<?php echo esc_attr($className . $class_name_last); ?>">
                    <input type="checkbox" class="et-usual-checkbox" name="<?php echo esc_attr($value['id']); ?>[]"
                           id="<?php echo esc_attr($et_checkboxes_label); ?>"
                           value="<?php echo esc_attr($option); ?>" <?php echo esc_html($checked); ?> />
                    <label for="<?php echo esc_attr($et_checkboxes_label); ?>"><?php echo esc_html($et_checkboxes_value); ?></label>
                </p>

                <?php $i++;
            }
        }
        ?>
        <br class="et-clearfix"/>
        <?php
    }

    private function processType_DifferentCheckboxes($value)
    {

        foreach ($value['options'] as $option) {
            $checked = '';
            if (et_get_option($value['id']) !== false) {
                if (in_array($option, et_get_option($value['id']))) {
                    $checked = "checked=\"checked\"";
                }
            } elseif (isset($value['std'])) {
                if (in_array($option, $value['std'])) {
                    $checked = "checked=\"checked\"";
                }
            } ?>

            <p class="postinfo <?php echo esc_attr('postinfo-' . $option); ?>">
                <input type="checkbox" class="et-usual-checkbox" name="<?php echo esc_attr($value['id']); ?>[]"
                       id="<?php echo esc_attr($value['id'] . '-' . $option); ?>"
                       value="<?php echo esc_attr($option); ?>" <?php echo esc_html($checked); ?> />
            </p>
        <?php } ?>
        <br class="et-clearfix"/>
        <?php
    }

    private function processType_EtColorPalette(
        $value,
        $is_new_global_setting,
        $global_setting_main_name,
        $global_setting_sub_name
    ) {
        //
        $items_amount = isset($value['items_amount']) ? $value['items_amount'] : 1;
        $et_input_value = et_get_option($value['id'], '', '', false, $is_new_global_setting, $global_setting_main_name,
            $global_setting_sub_name);
        $et_input_value_processed = str_replace('|', '', $et_input_value);
        $et_input_value = !empty($et_input_value_processed) ? $et_input_value : $value['std'];
        ?>
        <div class="et_pb_colorpalette_overview">
            <?php
            for ($colorpalette_index = 1; $colorpalette_index <= $items_amount; $colorpalette_index++) { ?>
                <span class="colorpalette-item colorpalette-item-<?php echo esc_attr($colorpalette_index); ?>"
                      data-index="<?php echo esc_attr($colorpalette_index); ?>"><span class="color"></span></span>
            <?php } ?>

        </div>
        <?php for ($colorpicker_index = 1; $colorpicker_index <= $items_amount; $colorpicker_index++) { ?>
            <div class="colorpalette-colorpicker" data-index="<?php echo esc_attr($colorpicker_index); ?>">
                <input data-index="<?php echo esc_attr($colorpicker_index); ?>" type="text"
                       class="input-colorpalette-colorpicker" data-alpha="true"/>
            </div>
        <?php } ?>
        <input name="<?php echo esc_attr($value['id']); ?>" id="<?php echo esc_attr($value['id']); ?>"
               class="et_color_palette_main_input" type="hidden" value="<?php echo esc_attr($et_input_value); ?>"/>
        <?php
    }

    private function processType_CheckboxCheckbox2(
        $value,
        $hidden_option_classname,
        $is_new_global_setting,
        $global_setting_main_name,
        $global_setting_sub_name,
        $is_hidden_option,
        $themename,
        $disabled
    ) {

        $et_box_class = ('checkbox' === $value['type'] ? 'et-epanel-box-small-1' : ('checkbox2' === $value['type'] ? 'et-epanel-box-small-2' : 'et-epanel-box-small-1'));
        ?>
        <div class="<?php echo esc_attr('et-epanel-box ' . $et_box_class . $hidden_option_classname); ?>">
            <div class="et-box-title"><h3><?php echo esc_html($value['name']); ?></h3>
                <div class="et-box-descr">
                    <p><?php
                        echo wp_kses($value['desc'], array(
                            'a' => array(
                                'href' => array(),
                                'title' => array(),
                                'target' => array(),
                            ),
                        ));
                        ?></p>
                </div> <!-- end et-box-desc-content div -->
            </div> <!-- end div et-box-title -->
            <div class="et-box-content <?php echo (isset($value['add_visible_toggle'])?$value['add_visible_toggle']:'') ?>">
                <?php
                $checked = '';
                $value_id = et_get_option($value['id']);

                if ($is_new_global_setting && isset($value['main_setting_name']) && isset($value['sub_setting_name'])) {
                    $saved_checkbox = et_get_option($value['id'], '', '', false, $is_new_global_setting,
                        $global_setting_main_name, $global_setting_sub_name);
                    $checked = ('on' === $saved_checkbox || (!$saved_checkbox && 'on' === $value['std'])) ?
                        'checked="checked"' : '';
                } else {
                    if (!empty($value_id)) {
                        if ('on' === $value_id) {
                            $checked = 'checked="checked"';
                        } else {
                            $checked = '';
                        }
                    } else {
                        if ('on' === $value['std']) {
                            $checked = 'checked="checked"';
                        }
                    }
                }
                ?>

                <?php if (isset($value['hidden_option_message']) && $is_hidden_option) : ?>
                    <div class="et-hidden-option-message">
                        <?php echo et_core_esc_previously(wpautop(esc_html($value['hidden_option_message']))); ?>
                    </div>
                <?php endif; ?>
                <input type="checkbox" class="et-checkbox yes_no_button" name="<?php echo esc_attr($value['id']); ?>"
                       id="<?php echo esc_attr($value['id']); ?>" <?php echo et_core_esc_previously($checked); ?> <?php echo et_core_esc_previously($disabled); ?>/>

            </div> <!-- end et-box-content div -->
            <?php if ('et_pb_static_css_file' === $value['id']) { ?>
                <span class="et-button"><?php echo esc_html_x('Clear', 'clear static resources', $themename); ?></span>
            <?php } ?>
            <span class="et-box-description"></span>
        </div> <!-- end epanel-box-small div -->

        <?php
    }

    private function processType_CheckboxList($value, $hidden_option_classname, $themename)
    {
        ?>
        <div class="<?php echo esc_attr('et-epanel-box et-epanel-box__checkbox-list' . $hidden_option_classname); ?>">
            <div class="et-box-title">
                <h3><?php echo esc_html($value['name']); ?></h3>
                <div class="et-box-descr">
                    <p>
                        <?php
                        echo wp_kses($value['desc'], array(
                            'a' => array(
                                'href' => array(),
                                'title' => array(),
                                'target' => array(),
                            ),
                        ));
                        ?>
                    </p>
                </div> <!-- end et-box-descr div -->
            </div> <!-- end div et-box-title -->
            <div class="et-box-content et-epanel-box-small-2">
                <div class="et-box-content--list">
                    <?php
                    if (empty($value['options'])) {
                        esc_html_e('No available options.', $themename);
                    } else {
                        $defaults = (isset($value['default']) && is_array($value['default'])) ? $value['default'] : array();
                        $stored_values = et_get_option($value['id'], array());
                        $value_options = $value['options'];
                        if (is_callable($value_options)) {
                            $value_options = call_user_func($value_options);
                        }

                        foreach ($value_options as $option_key => $option) {
                            $option_value = isset($value['et_save_values']) && $value['et_save_values'] ? sanitize_text_field($option_key) : sanitize_text_field($option);
                            $option_label = sanitize_text_field($option);
                            $checked = isset($defaults[$option_value]) ? $defaults[$option_value] : 'off';
                            if (isset($stored_values[$option_value])) {
                                $checked = $stored_values[$option_value];
                            }
                            $checked = 'on' === $checked ? 'checked="checked"' : '';
                            $checkbox_list_id = sanitize_text_field($value['id'] . '-' . $option_key);
                            ?>
                            <div class="et-box-content">
                                <span class="et-panel-box__checkbox-list-label">
                                    <?php echo esc_html($option_label); ?>
                                </span>
                                <input type="checkbox" class="et-checkbox yes_no_button"
                                       name="<?php echo esc_attr($value['id']); ?>[]"
                                       id="<?php echo esc_attr($checkbox_list_id); ?>"
                                       value="<?php echo esc_attr($option_value); ?>" <?php echo et_core_esc_previously($checked); ?> />
                            </div> <!-- end et-box-content div -->
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <span class="et-box-description"></span>
        </div> <!-- end epanel-box-small div -->
        <?php
    }

    /**
     * Functions from 'wp-admin/includes/plugin.php'
     *
     * to be able to verify the plugin is active or not at plugin instantiation
     */

    /**
     * Determines whether the plugin is active for the entire network.
     *
     * Only plugins installed in the plugins/ folder can be active.
     *
     * Plugins in the mu-plugins/ folder can't be "activated," so this function will
     * return false for those plugins.
     *
     * For more information on this and similar theme functions, check out
     * the {@link https://developer.wordpress.org/themes/basics/conditional-tags/
     * Conditional Tags} article in the Theme Developer Handbook.
     *
     * @since 3.0.0
     *
     * @param string $plugin Path to the plugin file relative to the plugins directory.
     * @return bool True if active for the network, otherwise false.
     */
    function is_plugin_active_for_network( $plugin ) {
        if ( ! is_multisite() ) {
            return false;
        }

        $plugins = get_site_option( 'active_sitewide_plugins' );
        if ( isset( $plugins[ $plugin ] ) ) {
            return true;
        }

        return false;
    }


    /**
     * Determines whether a plugin is active.
     *
     * Only plugins installed in the plugins/ folder can be active.
     *
     * Plugins in the mu-plugins/ folder can't be "activated," so this function will
     * return false for those plugins.
     *
     * For more information on this and similar theme functions, check out
     * the {@link https://developer.wordpress.org/themes/basics/conditional-tags/
     * Conditional Tags} article in the Theme Developer Handbook.
     *
     * @since 2.5.0
     *
     * @param string $plugin Path to the plugin file relative to the plugins directory.
     * @return bool True, if in the active plugins list. False, not in the list.
     */
    function is_plugin_active( $plugin ) {
        return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || $this->is_plugin_active_for_network( $plugin );
    }

}