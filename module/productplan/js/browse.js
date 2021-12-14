$(document).on('click', '.task-toggle', function(e)
{
    var $toggle = $(this);
    var id = $(this).data('id');
    var isCollapsed = $toggle.toggleClass('collapsed').hasClass('collapsed');
    $toggle.closest('[data-ride="table"]').find('tr.parent-' + id).toggle(!isCollapsed);

    e.stopPropagation();
    e.preventDefault();
});

$(function()
{
    $('#productplanList tbody tr').each(function()
    {
        var $content = $(this).find('td.content');
        var content  = $content.find('div').html();
        if(content.indexOf('<br') >= 0)
        {
            $content.append("<a href='###' class='more'><i class='icon icon-chevron-double-down'></i></a>");
        }
    });

    $('#createExecutionButton').on('click', function()
    {
        var projectID = $('#project').val();
        var planID    = $('#planID').val();
        if(!projectID)
        {
            alert(projectNotEmpty);
            return false;
        }
        else
        {
            $.apps.open(createLink('execution', 'create', 'projectID=' + projectID + '&executionID=&copyExecutionID=&planID=' + planID + '&confirm=&productID=' + productID), 'project')
        }
        $('#projects').modal('hide');
    });
});

$(document).on('click', 'td.content .more', function(e)
{
    var $toggle = $(this);
    if($toggle.hasClass('open'))
    {
        $toggle.removeClass('open');
        $toggle.closest('.content').find('div').css('height', '25px');
        $toggle.css('padding-top', 0);
        $toggle.find('i').removeClass('icon-chevron-double-up').addClass('icon-chevron-double-down');
    }
    else
    {
        $toggle.addClass('open');
        $toggle.closest('.content').find('div').css('height', 'auto');
        $toggle.css('padding-top', ($toggle.closest('.content').find('div').height() - $toggle.height()) / 2);
        $toggle.find('i').removeClass('icon-chevron-double-down').addClass('icon-chevron-double-up');
    }
});

/**
 * Get planID
 *
 * @param  object $obj
 * @param  int    $branch
 * @access public
 * @return void
 */
function getPlanID(obj, branch)
{
    var planID = $(obj).attr("data-id");
    $('#planID').val(planID);

    link = createLink('productplan', 'ajaxGetProjects', 'productID=' + productID + '&branch=' + branch);
    $.get(link, function(projects)
    {
        $('#project').replaceWith(projects);
        $("#project_chosen").remove();
        $("#project").chosen();

        var projectList = $("#project").val();
        if(!projectList)
        {
            $("#project").attr('disabled', true);
            $("#project").trigger('chosen:updated');
            $(".tips").removeClass('hidden');

            var locateLink   = createLink('product', 'project', 'status=all&productID=' + productID + '&branch=' + branch);
            var locateButton = "<a href=" + locateLink + " class='btn btn-primary' data-app='product'>" + enterProjectList + "</a>";
            $("#projects .btn-primary").replaceWith(locateButton);
        }
    });
}
