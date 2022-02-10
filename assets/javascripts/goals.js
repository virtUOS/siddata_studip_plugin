$(document).ready(function () {
    // event for editing properties
    $(".siddata-edit-property-button").click(function (event) {
        toggle_property(event, this);
    });

    // event for collapsing goals
    $('.siddata-goal-collapse').click(function (e){
        e.preventDefault();
        e.stopPropagation();

        const full_id = $(e.target).parent().attr('id');
        const split_id = full_id.split("_");
        const goal_id = split_id[split_id.length - 1];

        $('#siddata-goal-collapse-collapse_'+goal_id).toggle();
        $('#siddata-goal-collapse-expand_'+goal_id).toggle();
        $('#siddata-goal-content_'+goal_id).toggle();
    });

});

/**
 * toggle editing field for goal properties
 * @param event
 * @param target
 */
function toggle_property(event, target) {
    event.preventDefault();
    event.stopPropagation();
    const full_id = $(target).attr("id");
    const split_id = full_id.split("_");
    const prop_id = split_id[split_id.length - 1];
    $("#siddata-edit-goal-property-p_" + prop_id).toggle();
    $("#siddata-goal-property_" + prop_id).toggle();
}
