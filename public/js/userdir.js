// $(document).ready(function()
// {


// }); // end doc.ready

$(document).on('click', '#clearsearchbtn', function()
{ 
    $('#search').val('');
    
    $('tr.searchable').each(function()
    {
        if($(this).attr('style'))
        {
            $(this).removeAttr('style');
        }
    });
});

function edituser(userid)
{
	window.location.replace('edituser/' + userid);
}
