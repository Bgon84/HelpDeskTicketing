$(document).ready(function()
{
	$(document).on('click', '.editpriority', function()
	{
		var priorityid = $(this).attr('id').split('-')[1];

		$(this).addClass('nodisplay');
		$('#lblpriority-' + priorityid).addClass('nodisplay');
		$('#lbldescription-' + priorityid).addClass('nodisplay');

		$('#save-' + priorityid).removeClass('nodisplay');
		$('#priority-' + priorityid).removeClass('nodisplay');
		$('#description-' + priorityid).removeClass('nodisplay');
		
	});

	$(document).on('click', '.savepriority', function()
	{
		var priorityid = $(this).attr('id').split('-')[1];

		var priority = $('#priority-' + priorityid).val();
		var description = $('#description-' + priorityid).val();

		if(!isPosInt(priority))
		{
			swal('Whoops!', 'Priority must be a positive integer!', 'error');
			$('#priority-' + priorityid).addClass('alert');
			return;
		}

        $.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            }
        });
		
        $.ajax(
        {
            url: 'editpriority',
            type: 'POST',
            data: {priorityid:priorityid, priority:priority, description:description},
            datatype: 'JSON',
            success: function(resp)
            {
            	if(resp[0] == 'success')
            	{
            		swal('Success!', 'Priority Has Been Updated', 'success');

            		$('#lblpriority-' + priorityid).text(resp[1]['priority']);
					$('#lbldescription-' + priorityid).text(resp[1]['description']);

    				$('#edit-' + priorityid).removeClass('nodisplay');
					$('#lblpriority-' + priorityid).removeClass('nodisplay');
					$('#lbldescription-' + priorityid).removeClass('nodisplay');

					$('#save-' + priorityid).addClass('nodisplay');
					$('#priority-' + priorityid).addClass('nodisplay');
					$('#description-' + priorityid).addClass('nodisplay');

					if($('#priority-' + priorityid).hasClass('alert'))
					{
						$('#priority-' + priorityid).removeClass('alert');
					}

            	}
            	else if(resp.split(':')[1] == ' Unique violation')
                {
                    swal('Opps!', 'That Priority already exists!', 'error');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        });
	});

	$('#addpriority').on('click', function()
	{
		$('#createprioritiestbl').removeClass('nodisplay');
		$('#addpriority').addClass('nodisplay');

	});

	$('#createprioritybtn').on('click', function()
	{
		var priority = $('#createpriority').val();
		var description = $('#createprioritydescription').val();
		var editperm = $('#editperm').val();

		if(!isPosInt(priority))
		{
			swal('Whoops!', 'Priority must be a positive integer!', 'error');
			$('#createpriority').addClass('alert');
			return;
		}

		$.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            }
        });
		
        $.ajax(
        {
            url: 'createpriority',
            type: 'POST',
            data: {priority:priority, description:description},
            datatype: 'JSON',
            success: function(resp)
            {
            	if(resp[0] == 'success')
            	{
            		swal('Success!', 'Priority Has Been Created', 'success');

            		$('#createprioritiestbl').addClass('nodisplay');
					$('#addpriority').removeClass('nodisplay');

					var priorityid = resp[1]['priorityid'];
					var priority = resp[1]['priority'];
					var description = "";

					if(resp[1]['description'] !== null)
					{
						description = resp[1]['description'];
					}					

					var newrow = '';

					newrow += '<tr>';

					if(editperm == 'true')
					{
						newrow += '<td><input type="button" class="btn btn-primary editpriority" id="edit-'+priorityid+'" value="Edit">';
						newrow += '<input type="button" class="btn btn-primary savepriority nodisplay" id="save-'+priorityid+'" value="Save"></td>';
					}
					else
					{
						newrow += '<td></td>';
					}
					

					newrow += '<td><label id="lblpriority-'+priorityid+'">'+priority+'</label>';
					newrow += '<input type="text" class="form-control priorityinfo nodisplay" id="priority-'+priorityid+'" name="priority-'+priorityid+'" value="'+priority+'"></td>';
	                
	                newrow += '<td><label id="lbldescription-'+priorityid+'">'+description+'</label>';
	                newrow += '<input type="text" class="form-control priorityinfo nodisplay" id="description-'+priorityid+'" name="description-'+priorityid+'" value="'+description+'" maxlength="26"></td>';

	                newrow += '</tr>';	                				
								
					$('#prioritiestbl').append(newrow);

					$('#createpriority').val('');
					$('#createprioritydescription').val('');

            	}
            	else if(resp.split(':')[1] == ' Unique violation')
                {
                    swal('Opps!', 'That Priority already exists!', 'error');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        });
	})

}); // end of doc.ready
