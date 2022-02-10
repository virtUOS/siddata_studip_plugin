$(document).ready(function () {

    // event for adding a new answer to a multitext activity
    $('.siddata-add-answer-button').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        const full_id = $(e.target).attr('id');
        const split_id = full_id.split("_");
        const activity_id = split_id[split_id.length - 1];
        const index = $("#siddata-questionnaire-answer_"+activity_id).siblings().length;
        $("<input type=\"text\" id=\"siddata-answer_"+activity_id+"\" name=\"siddata-questionnaire-answer_"+activity_id+"["+index+"]\">").insertBefore(e.target);
    });
    $('.siddata-add-answer-button-questionnaire').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        const full_id = $(e.target).attr('id');
        const split_id = full_id.split("_");
        const goal_id = split_id[split_id.length - 2]
        const activity_id = split_id[split_id.length - 1];
        const index = $("#siddata-answer_"+activity_id).siblings().length;
        $("<input type=\"text\" id=\"siddata-answer_"+activity_id+"\" name=\"siddata-questionnaire-answer_"+goal_id+"["+activity_id+"]["+index+"]\" required>").insertBefore(e.target);
    });

    // event for toggling siddata feedback text div
    $('.siddata-feedback-text-button').click(function(e){
        e.preventDefault();
        e.stopPropagation();
        toggleFeedbackText($(this));
    });

    // marking of feedback symbols on click
    $(".siddata-feedback-option").click(function(e){
        let $parents = $(e.target).parents(".siddata-feedback-options");
        $("#"+$parents[0].id).find(".siddata-feedback-option").css(
            {
                "background-position": "-9999px -9999px",
                "background-size": $(e.target).attr("width")+"px"+" "+$(e.target).attr("height")+"px",
                "filter":""
            }
        );
        $(e.target).css(
            {
                "background-position": "bottom left",
                "background-size": $(e.target).attr("width")+"px"+" "+$(e.target).attr("height")+"px",
                "filter": "drop-shadow(0 0 4px #708090)"
            }
        );
    });

    // display iframe in full width if iframe activity exist
    if ($('.siddata-iframe-activity').length > 0) {
        $('.siddata').addClass("siddata-full-width");
    }


    // collapse/expand descriptions

    // setup
    $('.siddata-activity-description').each(function() {
        // tolerance of 10
        if ($(this).prop('scrollHeight') - 10 > $(this).height()) {
            $(this).css('box-shadow', 'inset 0em -2em 2em -1em #e7ebf1');

            const full_id = $(this).attr('id');
            const split_id = full_id.split("_");
            const activity_id = split_id[split_id.length - 1];
            $('#siddata-activity-showmore_'+activity_id).toggle();
        } else {
            const full_id = $(this).attr('id');
            const split_id = full_id.split("_");
            const activity_id = split_id[split_id.length - 1];
            $('#siddata-activity-description-toggle_'+activity_id).remove();
        }
    });

    // show more button
    $('.siddata-activity-description-showmore').click(function(e) {
        const full_id = $(e.target).attr('id');
        const split_id = full_id.split("_");
        const activity_id = split_id[split_id.length - 1];

        $('#siddata-activity-showmore_'+activity_id).toggle();
        $('#siddata-activity-showless_'+activity_id).toggle();
        $('#siddata-activity-description_'+activity_id).css('max-height', '100%').css('box-shadow', '0 0 0');
    });

    // show less button
    $('.siddata-activity-description-showless').click(function(e) {
        const full_id = $(e.target).attr('id');
        const split_id = full_id.split("_");
        const activity_id = split_id[split_id.length - 1];

        $('#siddata-activity-showmore_'+activity_id).toggle();
        $('#siddata-activity-showless_'+activity_id).toggle();
        $('#siddata-activity-description_'+activity_id).css('max-height', '10em').css('box-shadow', 'inset 0em -2em 2em -1em #e7ebf1');
    });

    // show person email
    $('.siddata-activity-email-show').click(function(e) {
        e.preventDefault();
        e.stopPropagation();

        const full_id = $(e.target).attr('id');
        const split_id = full_id.split("_");
        const activity_id = split_id[split_id.length - 1];

        $('#siddata-activity-email_'+activity_id).show();
        $('#siddata-activity-email-show_'+activity_id).hide();

        // invoke show_email action
        $.ajax($(e.target).attr('href'));
    });

    $('.siddata-activity-color-grey').find('.button').prop('disabled', true);
});

/**
 * toggle feedback text div for given href element
 * @param feedback_obj href element
 */
function toggleFeedbackText(feedback_obj) {
    const full_id = feedback_obj.attr('id');
    const split_id = full_id.split("_");
    const id = split_id[split_id.length - 1];
    $('#siddata-feedback-text-div_'+id).toggle('fade');
    $('textarea[name="siddata-feedback-text-input_'+id+'"]').focus();
}
