$(function()
{
    $('.main-side #branch').closest('td').find('#product_chosen .chosen-single').css('width', '153px');

    if(storyStatus == 'reviewing')
    {
        $('#reviewer').next().find('.picker-selection').each(function()
        {
            if(reviewedReviewer.indexOf($(this).find('span').text()) != -1) $(this).css('pointer-events', 'none');
        });

        $('#reviewer').change(function()
        {
            if($('#reviewer').next().find('.picker-selection').length === 0)
            {
                alert(reviewerNotEmpty);
                $('#reviewer').val(reviewers);
                $('#reviewer').trigger('chosen:updated');
            }
            else
            {
                reviewers = $('#reviewer').val();
            }
        });
    }
    else
    {
        $('#reviewer').change(function()
        {
            if(!$('#reviewer').val()) $('#needNotReview').attr('checked', true).change();
        });
    }

    $('#needNotReview').on('change', function()
    {
        $('#reviewer').text('').attr('disabled', $(this).is(':checked') ? 'disabled' : null).trigger('chosen:updated');
        if($(this).is(':checked'))
        {
            $('#reviewerBox').removeClass('required');
        }
        else
        {
            $('#reviewerBox').addClass('required');
        }
    });
    if(!$('#reviewer').val()) $('#needNotReview').change();

    $('#source').on('change', function()
    {
        var source = $(this).val();
        if($.inArray(source, feedbackSource) != -1)
        {
            $('.feedbackBox').removeClass('hidden');
        }
        else
        {
            $('.feedbackBox').addClass('hidden');
        }
    });

    $('#linkStoriesLink').click(function()
    {
        var storyIdList = '';
        $('#linkStoriesBox input').each(function()
        {
            storyIdList += $(this).val() + ',';
        });

        var link = '';
        if(storyType == 'story')
        {
            link = createLink('story', 'linkStories', 'storyID=' + storyID + '&browseType=&excludeStories=' + storyIdList, '', true);
        }
        else
        {
            link = createLink('story', 'linkRequirements', 'storyID=' + storyID + '&browseType=&excludeStories=' + storyIdList, '', true);
        }

        var modalTrigger = new $.zui.ModalTrigger({type: 'iframe', width: '95%', url: link});
        modalTrigger.show();
    });

    $('#duplicateStory').picker(
    {
        disableEmptySearch : true,
        dropWidth : 'auto'
    });
})
