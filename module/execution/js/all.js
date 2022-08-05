$(function()
{
    $('#executionTableList').on('sort.sortable', function(e, data)
    {
        var list = '';
        for(i = 0; i < data.list.length; i++) list += $(data.list[i].item).attr('data-id') + ',';
        $.post(createLink('execution', 'updateOrder'), {'executions' : list, 'orderBy' : orderBy});
    });

    var nameWidth = $('#executionsForm thead th.c-name').width();
    if(isCNLang && nameWidth < 150 && !useDatatable) $('#executionsForm thead th.c-name').css('width', '150px');
    if(!isCNLang && nameWidth < 200 && !useDatatable) $('#executionsForm thead th.c-name').css('width', '200px');

    toggleFold('#executionsForm', unfoldExecutions, 0, 'execution');

    $('.table td.has-child > .plan-toggle').each(function()
    {
        var fold = $(this).hasClass('collapsed');
        var parentID = $(this).closest('tr').attr('data-id');
        if(fold)
        {
            $('.parent-' + parentID).hide();
            $(this).closest('td').removeClass('parent');
        }
        else
        {
            $('.parent-' + parentID).show();
            $(this).closest('td').addClass('parent');
        }
    });

    /* Expand and fold substages. */
    $('.table td.has-child > .plan-toggle').click(function()
    {
        var parentID = $(this).closest('tr').attr('data-id');
        $('.parent-' + parentID).toggle();

        if($('.parent-' + parentID).css('display') == 'none')
        {
            $(this).closest('td').removeClass('parent');
        }
        else
        {
            $(this).closest('td').addClass('parent');
        }
    });

    $(document).on('click', "#toggleFold", function()
    {
        var fold = $(this).hasClass('collapsed');
        if(fold)
        {
            $('.has-child.c-name.flex').removeClass('parent');
            $('.table td.has-child > .plan-toggle').addClass('collapsed');
        }
        else
        {
            $('.has-child.c-name.flex').addClass('parent');
            $('.table td.has-child > .plan-toggle').removeClass('collapsed');
        }
    });

    $('#executionsForm').table(
    {
        statisticCreator: function(table)
        {
            var $table       = table.getTable();
            var $checkedRows = $table.find(table.isDataTable ? '.datatable-row-left.checked' : 'tbody>tr.checked');
            var $originTable = table.isDataTable ? table.$.find('.datatable-origin') : null;
            var $rows        = $table.find(table.isDataTable ? '.datatable-rows .datatable-row-left' : 'tbody>tr');

            var taskIdList = [];
            $rows.each(function()
            {
                var $row = $(this);
                if($originTable) $row = $originTable.find('tbody>tr[data-id="' + $row.data('id') + '"]');
                var data = $row.data();
                taskIdList.push(data.id);
            });
        }
    })
});

function byProduct(productID, projectID, status)
{
    location.href = createLink('project', 'all', "status=" + status + "&project=" + projectID + "&orderBy=" + orderBy + '&productID=' + productID);
}
