
jQuery(document).ready(function($){

    // replace the divi checkbox function
    jQuery('.et-box-content').unbind('click');

    // replacement function
    $('.et-box-content').on( 'click', '.et_pb_yes_no_button', function(e){
        e.preventDefault();
        // Fix for nested .et-box-content triggering checkboxes multiple times.
        e.stopPropagation();

        var $click_area = $(this),
            $box_content = $click_area.closest('.et-box-content'),
            $checkbox    = $box_content.find('input[type="checkbox"]'),
            $state       = $box_content.find('.et_pb_yes_no_button');

        if ( $state.parent().next().hasClass( 'et_pb_clear_static_css' ) ) {
            $state = $state.add( $state.parent() );

            if ( $checkbox.is( ':checked' ) ) {
                $box_content.parent().next().hide();
            } else {
                $box_content.parent().next().show();
            }
        }

        $state.toggleClass('et_pb_on_state et_pb_off_state');

        // insert a check for the pannelwrapper tag
        $pannelwrapperTagCheck = check_for_panelwrapper_tag($box_content);

        console.log($pannelwrapperTagCheck);
        console.log(($pannelwrapperTagCheck[0] !== false));

        if ( $checkbox.is(':checked' ) ) {
            $checkbox.prop('checked', false);

            // if have pannelwrapper then toggle the div
            if ($pannelwrapperTagCheck[0] !== false) {
                $($pannelwrapperTagCheck[1]).hide();
            }

        } else {
            $checkbox.prop('checked', true);

            // if have pannelwrapper then toggle the div
            if ($pannelwrapperTagCheck[0] !== false) {
                $($pannelwrapperTagCheck[1]).show();
            }
        }
    });

    update_panelwrapper_state();

});

function update_panelwrapper_state () {
    $checkBoxes = jQuery('body').find('input[type="checkbox"]');

    var arrayLength = $checkBoxes.length;
    for (var i = 0; i < arrayLength; i++) {
        if ($($checkBoxes[i]).hasClass('yes_no_button')) {

            $box_content = $($checkBoxes[i]).closest('.et-box-content');
            $pannelwrapperTagCheck = check_for_panelwrapper_tag($box_content);

            if ($pannelwrapperTagCheck[0] !== false) {
                if ( $($checkBoxes[i]).is(':checked' ) ) {
                    // if have pannelwrapper then toggle the div
                    if ($pannelwrapperTagCheck[0] !== false) {
                        $($pannelwrapperTagCheck[1]).show();
                    }
                } else {
                    // if have pannelwrapper then toggle the div
                    if ($pannelwrapperTagCheck[0] !== false) {
                        $($pannelwrapperTagCheck[1]).hide();
                    }
                }
            }

        }

    }
}

function check_for_panelwrapper_tag($box_content) {

    $box_content_class = $box_content.attr("class");
    $box_content_classes = $box_content_class.split(' ');

    var arrayLength = $box_content_classes.length;
    for (var i = 0; i < arrayLength; i++) {
        if ($box_content_classes[i].indexOf('_panelwrapper_tag') !== -1) {
            $thisElem = $box_content_classes[i];

            // replace the tag, should be the element single name
            $thisElem = $thisElem.replace('_panelwrapper_tag', '');

            // build the target div
            $targetDiv = '.' + $thisElem + '_panelwrapper_div';

            // if exists, then return true
            if ($($targetDiv).length){
                return [true, $targetDiv];
            }
        }
    }

    return [false];
}