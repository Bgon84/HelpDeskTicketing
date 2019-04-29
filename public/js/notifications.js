$(document).ready(function() 
{
    $("#createnotificationuserselect").selectpicker('refresh');
    $('#createnotificationqueueselect').selectpicker('refresh');
    $('#editnotificationselect').selectpicker('refresh');
    $('#editnotificationuserselect').selectpicker('refresh');
    $('#editnotificationqueueselect').selectpicker('refresh');

    var varmsgshow = cookies.get('varmsg');

    if(varmsgshow == 'true')
    {
        $('#varlist').show();
    }
    else
    {
        $('#varlist').hide();
    }

    $('#hidevariablemessage').on('click', function()
    {
        varmsgshow = cookies.get('varmsg');

        if (varmsgshow == 'true')
        {
            $('#varlist').hide();
            cookies.set('varmsg', 'false');
        }
        else
        {
            $('#varlist').show();
            cookies.set('varmsg', 'true');            
        }
    });

    $('.helpicon').on('click', function()
    {
        $('#notificationhelp').modal('show');
    });

	$('#clearcreatenotificationform').on('click', function()
	{
        clearNotificationCreationForm();
	});
    
    $('#cleareditnotificationform').on('click', function()
    {
        clearNotificationEditForm();
    });

	// Submit Notification Creation
    $('#createnotificationsubmit').on('click', function()
    {
        var createnotifyname = $('#createnotificationname').val().trim();

        if(createnotifyname.length == 0)
        {
            swal('Hey!', 'How about naming this Notification?', 'error');
            $('#createnotificationname').addClass('alert');
            $('#createnotificationname').val('');
            return;
        }

        if($('#createnotificationtriggeraction').val() == 0)
        {
            swal('Hey!', 'You Must Select a Trigger Action', 'error').then(function()
                {
                    $('#createnotificationtriggeraction').selectpicker('toggle');
                });
            return;
        }

        if($('#createnotificationrecipient').val().length == 0)
        {
            swal('Hey!', 'Notification Recipient Cannot be Blank', 'error');
            $('#createnotificationrecipient').addClass('alert');
            return;
        }

        if($('#createnotification').val().length == 0)
        {
            swal('Hey!', 'Notification Message Cannot be Blank', 'error');
            $('#createnotification').addClass('alert');
            return;
        }

        $.ajax(
        {
            url:'createnotification',
            type: 'POST',
            data: $('#createnotificationform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                if(resp[0] == "success")
                {
                    var notificationid = resp[1]['notificationid'];
                    var notificationname = resp[1]['notificationname'];
                    var active = resp[1]['active'];
                    var status = '';

                    swal('Success!', 'Your Notification Has Been Created!', 'success');

                    clearNotificationCreationForm();

                    if(active == 0)
                    {
                        status = ' (Inactive)';
                    }

                    $('#editnotificationselect').append('<option value="'+notificationid+'">'+notificationname+status+'</option>');
                    $('#editnotificationselect').selectpicker('refresh');                           
                }
                else if(resp.split(':')[1] == ' Unique violation')
                {
                    swal('Opps!', 'That Name already exists!', 'error');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })
    });

    // Populate Notification Editing Form when Notification is selected
    $('#editnotificationselect').on('change', function()
    {
        var notificationid = $('#editnotificationselect option:selected').val();       

        editfilterexpressions = [];
        removeeditfilterexpressions = [];
        $('#editaddtfetbody').html('');
        $('#editnotificationtriggerfilterexp').val('');
        $('#removeeditnotificationtriggerfilterexp').val('');
        
        if(!$('#edittickettfetbl').hasClass('nodisplay'))
        {
            $('#edittickettfetbl').addClass('nodisplay');
        }

        $('#editnotificationqueueselect').val('default');
        $('#editnotificationqueueselect').selectpicker('refresh');
        $('#editnotificationuserselect').val('default');                            
        $('#editnotificationuserselect').selectpicker('refresh');
        
        $.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax(
        {
            url: 'getnotificationinfo',
            type: 'POST',
            data: {notificationid:notificationid},
            success: function(resp)
            {
                $('#editnotificationname').val(resp['notification']['notificationname']);

                $('#editnotificationtriggeraction').selectpicker('val', resp['notification']['triggeraction']);

                $('#editnotificationtriggerfilterexp').val(resp['notification']['filterexpression']);
                $('#editnotificationrecipient').val(resp['notification']['recipient']);
                $('#editnotification').val(resp['notification']['notification']);

                if(resp['queues'].length > 0)
                {
                    var queues = [];

                    for(i=0; i<resp['queues'].length; i++)
                    {
                        queues.push(resp['queues'][i]['queueid']);
                    }

                    $('#editnotificationqueueselect').selectpicker('val', queues);
                }

                if(resp['filterexpressions'].length > 0)
                {   
                    $('#edittickettfetbl').removeClass('nodisplay');
                    for(i=0; i<resp['filterexpressions'].length; i++)
                    {
                        editfilterexpressions.push('id='+resp['filterexpressions'][i]['expressionid']+'&data='+resp['filterexpressions'][i]['data']+'&operator='+resp['filterexpressions'][i]['operator']+'&criteria='+ resp['filterexpressions'][i]['criteria']);
                        $('#editnotificationtriggerfilterexp').val(editfilterexpressions);
                        newrow = '<tr id="tfe-' + resp['filterexpressions'][i]['expressionid'] + '"><td>' + resp['filterexpressions'][i]['data'] + ' </td><td>' + resp['filterexpressions'][i]['operator'] + ' </td><td>' + resp['filterexpressions'][i]['criteria'] + ' </td><td><span class="btn removeedittfebtn">X</span></td></tr>';
                        $('#editaddtfetbody').append(newrow);

                        //TODO: figure out how to keep track of tfe's that are added and deleted upon saving (and again in Notifications Controller)
                    }
                }

                if(resp['notification']['active'] == 1)
                {
                    $('#editnotificationactive1').prop('checked', true);
                }
                else
                {
                    $('#editnotificationactive0').prop('checked', true);
                }                
            }
        });                               
    });
    
    // Submit Notification Editing          
    $('#editnotificationsubmit').on('click', function()
    {
        if($('#editnotificationselect').val().length == 0)
        {
            swal('Hey!', 'Please Select a Notification to Edit.', 'error').then(function()
                {
                    $('#editnotificationselect').selectpicker('toggle');
                });
            return;
        }

        var editnotifyname = $('#editnotificationname').val().trim();

        if(editnotifyname.length == 0)
        {
            swal('Hey!', 'You Cannot Have a Blank Notification Name.', 'error');
            $('#editnotificationname').addClass('alert');
            $('#editnotificationname').val('');
            return;
        }

        if($('#editnotificationtriggeraction').val() == 0)
        {
            swal('Hey!', 'You Must Select a Trigger Action', 'error').then(function()
                {
                    $('#editnotificationtriggeraction').selectpicker('toggle');
                });
            return;
        }

        if($('#editnotificationrecipient').val().length == 0)
        {
            swal('Hey!', 'Notification Recipient Cannot be Blank', 'error');
            $('#editnotificationrecipient').addClass('alert');
            return;
        }

        if($('#editnotification').val().length == 0)
        {
            swal('Hey!', 'Notification Message Cannot be Blank', 'error');
            $('#editnotification').addClass('alert');
            return;
        }

        var newname = $('#editnotificationname').val().toUpperCase();

        if($('#editnotificationactive0').is(':checked'))
        {
            var inactivestatus = ' (Inactive)';
        }
        else
        {
            var inactivestatus = '';
        }

        $('#editnotificationselect option:selected').text(newname + ' ' + inactivestatus);

        $.ajax(
        {
            url:'editnotification',
            type: 'POST',
            data: $('#editnotificationform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                if(resp == "success")
                {
                    swal('Success!', 'Your Notification Has Been Saved!', 'success');

                    clearNotificationEditForm();

                }
                else if(resp.split(':')[1] == ' Unique violation')
                {
                    swal('Opps!', 'That Name already exists!', 'error');
                }
                else
                {
                    swal('Uh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })
    });


// Trigger Filter Expression
    $(document).on('click', '#createaddtfe', function()
    {
        $('#notificationtfemodal').modal('show');
        $('#editAddTFEbtn').addClass('nodisplay');
        $('#createAddTFEbtn').removeClass('nodisplay');
    });

    $(document).on('click', '#editaddtfe', function()
    {
        $('#notificationtfemodal').modal('show');
        $('#createAddTFEbtn').addClass('nodisplay');
        $('#editAddTFEbtn').removeClass('nodisplay');
    });


    $(document).on('click', '.databtn', function()
    {
        $('#data').text($(this).text());
    });

    $(document).on('click', '.operatorbtn', function()
    {
        $('#operator').text($(this).text());
    });

    $(document).on('keyup', '#criteriainput', function()
    {
        $('#criteria').text($(this).val());
    });

    $(document).on('click', '#cleartfebtn', function()
    {
        clearTFEmodal();
    });

    var createfilterexpressions = [];
    var removecreatefilterexpressions = [];
    $(document).on('click', '#createAddTFEbtn', function()
    {
        var data = $('#data').text();
        var operator = $('#operator').text();
        var criteria = $('#criteria').text();
        
        if(data.length < 1)
        {
            swal('Hey!', 'You Must Select Data!', 'error');
            return;
        }

        if(operator.length < 1)
        {
            swal('Hey!', 'You Must Select an Operator!', 'error');
            return;
        }

        if(criteria.length < 1)
        {
            swal('Hey!', 'You Must Input Criteria!', 'error');
            return;
        }

        newrow = '<tr><td>' + data + ' </td><td>' + operator + ' </td><td>' + criteria + ' </td><td><span class="btn removecreatetfebtn">X</span></td></tr>';

        $('#createaddtfetbody').append(newrow);

        if($('#createtickettfetbl').hasClass('nodisplay'))
        {
            $('#createtickettfetbl').removeClass('nodisplay');
        }

        createfilterexpressions.push('data='+data+'&operator='+operator+'&criteria='+ criteria);
        $('#createnotificationtriggerfilterexp').val(createfilterexpressions);

        clearTFEmodal();
    });

    var editfilterexpressions = [];
    var removeeditfilterexpressions = [];
    $(document).on('click', '#editAddTFEbtn', function()
    {
        var data = $('#data').text();
        var operator = $('#operator').text();
        var criteria = $('#criteria').text();
        
        if(data.length < 1)
        {
            swal('Hey!', 'You Must Select Data!', 'error');
            return;
        }

        if(operator.length < 1)
        {
            swal('Hey!', 'You Must Select an Operator!', 'error');
            return;
        }

        if(criteria.length < 1)
        {
            swal('Hey!', 'You Must Input Criteria!', 'error');
            return;
        }


        newrow = '<tr><td>' + data + ' </td><td>' + operator + ' </td><td>' + criteria + ' </td><td><span class="btn removeedittfebtn">X</span></td></tr>';

        $('#editaddtfetbody').append(newrow);

        if($('#edittickettfetbl').hasClass('nodisplay'))
        {
            $('#edittickettfetbl').removeClass('nodisplay');
        }

        var id = null;
        editfilterexpressions.push('id='+id+'&data='+data+'&operator='+operator+'&criteria='+ criteria);
        $('#editnotificationtriggerfilterexp').val(editfilterexpressions);

        clearTFEmodal();
    });

    $(document).on('click', '.removeedittfebtn', function()
    {
        var row = $(this).closest('tr');
        var children = row.children();
        var data = children[0]['innerHTML'];
        var operator = children[1]['innerText'];
        var criteria = children[2]['innerHTML'];
        removeeditfilterexpressions.push('data='+data.trim()+'&operator='+operator.trim()+'&criteria='+ criteria.trim());
        $('#removeeditnotificationtriggerfilterexp').val(removeeditfilterexpressions);
        row.remove();
    });


    $(document).on('click', '.removecreatetfebtn', function()
    {
        var row = $(this).closest('tr');
        var children = row.children();
        var data = children[0]['innerHTML'];
        var operator = children[1]['innerText'];
        var criteria = children[2]['innerHTML'];
        removecreatefilterexpressions.push('data='+data.trim()+'&operator='+operator.trim()+'&criteria='+ criteria.trim());
        $('#removecreatenotificationtriggerfilterexp').val(removecreatefilterexpressions);
        row.remove();
    });

// End Trigger Filter Expressions

}); // end doc.ready

function clearTFEmodal()
{
    $('#data').text('');
    $('#operator').text('');
    $('#criteria').text('');
    $('#criteriainput').val('');
}

function clearNotificationCreationForm()
{
    ClearForm('createnotificationform');
    createfilterexpressions = [];
    removecreatefilterexpressions = [];
    $('#createaddtfetbody').html('');
    $('#createnotificationtriggerfilterexp').val('');
    $('#removecreatenotificationtriggerfilterexp').val('');
    
    if(!$('#createtickettfetbl').hasClass('nodisplay'))
    {
        $('#createtickettfetbl').addClass('nodisplay');
    }
}

function clearNotificationEditForm()
{
    ClearForm('editnotificationform');
    editfilterexpressions = [];    
    removeeditfilterexpressions = [];
    $('#editaddtfetbody').html('');
    $('#editnotificationtriggerfilterexp').val('');
    $('#removeeditnotificationtriggerfilterexp').val('');
    
    if(!$('#edittickettfetbl').hasClass('nodisplay'))
    {
        $('#edittickettfetbl').addClass('nodisplay');
    }
}