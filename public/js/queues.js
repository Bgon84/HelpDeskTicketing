$(document).ready(function() 
{
    var qactivestatus = '';
    $("#createqueueuserselect").selectpicker('refresh');
    $("#createqueuegroupselect").selectpicker('refresh'); 
    $("#createqueueassignmentopts").selectpicker('refresh');
    $("#createqueueelevationselect").selectpicker('refresh'); 

    $('#editqueuegroupselect').selectpicker('refresh');  
    $("#editqueueuserselect").selectpicker('refresh');          
    $('#editqueueselect').selectpicker('refresh');
    $('#editqueueassignmentopts').selectpicker('refresh');
    $('#editqueueelevationselect').selectpicker('refresh');

    $('#toqueueselect').selectpicker('refresh');

    // Clear Create Queue Form
    $('#clearcreatequeueform').on('click', function()
    {
        ClearForm('createqueueform');
    });

    // Clear Edit Queue Form
    $('#cleareditqueueform').on('click', function()
    {
        ClearForm('editqueueform');
    });

    // Submit queue Creation
    $('#createqueuesubmit').on('click', function()
    {
        var createqueuename = $('#createqueuename').val().trim();

        if(createqueuename.length == 0)
        {
            swal('Hey!', 'How about naming this Queue?', 'error');
            $('#createqueuename').addClass('alert');
            $('#createqueuename').val('');
            return;
        }

        if($('#createqueueassignmentopts').val().length == 0)
        {
            swal('Hey!', 'You Must Select an Assignment Option', 'error').then(function()
                {
                    $('#createqueueassignmentopts').selectpicker('toggle');
                });
            return;
        }


        $.ajax(
        {
            url:'createqueue',
            type: 'POST',
            data: $('#createqueueform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                if(resp[0] == "success")
                {
                    var queueid = resp[1]['queueid'];
                    var queuename = resp[1]['queuename'];
                    var active = resp[1]['active'];

                    swal('Success!', 'Your queue Has Been Created!', 'success');

                    ClearForm('createqueueform');

                    populateQueueSelects(queueid, queuename, active);                          
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

    //Populate Queue Edit Form
    $('#editqueueselect').on('change', function()
    {
        var queueid = $('#editqueueselect option:selected').val();       

        $('#editqueuepriorityselect').val('default');
        $('#editqueuepriorityselect').selectpicker('refresh');   
        $('#editqueuequeueselect').val('default');
        $('#editqueuequeueselect').selectpicker('refresh');
        $('#editqueueuserselect').val('default');
        $('#editqueueuserselect').selectpicker('refresh');
        
        $.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax(
        {
            url: 'getqueueinfo',
            type: 'POST',
            data: {queueid:queueid},
            success: function(resp)
            {
                $('#editqueuename').val(resp['queue']['queuename']);
                $('#editqueuedescription').val(resp['queue']['description']);

                $('#editqueueelevationselect').selectpicker('val', resp['queue']['elevationqueue']);

                if(resp['groups'].length > 0)
                {
                    var groups = [];

                    for(i=0; i<resp['groups'].length; i++)
                    {
                        groups.push(resp['groups'][i]['groupid']);
                    }

                    $('#editqueuegroupselect').selectpicker('val', groups);
                }

                if(resp['users'].length > 0)
                {
                    var users = [];

                    for(i=0; i<resp['users'].length; i++)
                    {
                        users.push(resp['users'][i]['userid']);
                    }

                    $('#editqueueuserselect').selectpicker('val', users);
                }

                if(resp['options'].length > 0)
                {
                    var options = [];

                    for(i=0; i<resp['options'].length; i++)
                    {
                        options.push(resp['options'][i]['optionid']);
                    }

                    $('#editqueueassignmentopts').selectpicker('val', options);
                }

                if(resp['queue']['active'] == 1)
                {
                    $('#editqueueactive1').prop('checked', true);                    
                }
                else
                {
                    $('#editqueueactive0').prop('checked', true);
                    qactivestatus = 'Inactive';
                }                
            }
        });                       
    });

    // Submit Edit Queue
    $('#editqueuesubmit').on('click', function(e)
    {
        if($('#editqueueselect').val().length == 0)
        {
            swal('Hey!', 'Please Select a Queue to Edit.', 'error').then(function()
                {
                    $('#editqueueselect').selectpicker('toggle');
                });
            return;
        }

        var editqueuename = $('#editqueuename').val().trim();

        if(editqueuename.length == 0)
        {
            swal('Hey!', 'Queue Name Cannot be Blank.', 'error');
            $('#editqueuename').addClass('alert');
            $('#editqueuename').val('');
            return;
        }

        if($('#editqueueassignmentopts').val().length == 0)
        {
            swal('Hey!', 'You Must Select an Assignment Option', 'error').then(function()
                {
                    $('#editqueueassignmentopts').selectpicker('toggle');
                });
            return;
        }

        var newname = $('#editqueuename').val().toUpperCase();
        var queueid = $('#editqueueselect option:selected').val();   

        if($('#editqueueactive0').is(':checked'))
        {
            $.ajaxSetup(
            {
                headers: 
                {
                  'X-CSRF-Token': $('meta[name="_token"]').attr('content')
                }
            });

            $.ajax(
            {
                url: 'checkfortickets',
                type: 'POST',
                data: {queueid:queueid},
                success: function(resp)
                { 
                    if(resp.length > 0)
                    {
                        swal({
                              title: 'Hold On!',
                              text: "There are unresolved tickets assigned to this Queue. You must reassign these tickets before making this Queue inactive",
                              type: 'warning',
                              showCancelButton: true,
                              confirmButtonColor: '#3085d6',
                              cancelButtonColor: '#d33',
                              confirmButtonText: 'Move Tickets'
                            }).then(function(result) 
                            { 
                              if(result) 
                              { 
                                var ticketlist = '';
                                var ticketids = [];
                                for(i=0; i<resp.length; i++)
                                {
                                    ticketlist += '<tr><td>' + resp[i].ticketid + '</td>'; 
                                    ticketlist += '<td>' + resp[i].requestor + '</td>'; 
                                    ticketlist += '<td>' + resp[i].category + '</td>'; 
                                    ticketlist += '<td>' + resp[i].description + '</td>'; 
                                    ticketlist += '<td>' + resp[i].status + '</td>'; 
                                    ticketlist += '<td>' + resp[i].created_at + '</td></tr>'; 

                                    ticketids.push(resp[i].ticketid);
                                }

                                $('#fromqueue').text(newname);
                                $('#fromqueueid').val(queueid);
                                $('#ticketids').val(ticketids);
                                $('#ticketlist tbody').html(ticketlist);                                
                                $('#moveticketsmodal').modal('show');
                                $('#moveticketsmodal').on('shown.bs.modal', function (e) {      
                                    $('#toqueueselect option').show();                                              
                                    $('#toqueueselect option[value="'+queueid+'"]').hide();
                                    $('#toqueueselect').selectpicker('refresh');
                                })                               
                            }
                        })
                    }
                    else
                    {
                        var inactivestatus = '(Inactive)';
                        removeInactiveQueue(queueid);

                        $('#editqueueselect option:selected').text(newname +' '+ inactivestatus);

                        $.ajax(
                        {
                            url:'editqueue',
                            type: 'POST',
                            data: $('#editqueueform').serialize(),
                            datatype: 'JSON',
                            success: function(resp)
                            {
                                if(resp[0] == "success")
                                {
                                    swal('Success!', 'Your Queue Has Been Saved!', 'success');

                                    ClearForm('editqueueform');
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
                    }
                }
            });
        }
        else
        {
            var inactivestatus = '';

            if(qactivestatus == 'Inactive')
            {
                populateQueueSelects(queueid, newname, 1);
            }
        

            $('#editqueueselect option:selected').text(newname +' '+ inactivestatus);

            $.ajax(
            {
                url:'editqueue',
                type: 'POST',
                data: $('#editqueueform').serialize(),
                datatype: 'JSON',
                success: function(resp)
                {
                    if(resp[0] == "success")
                    {
                        swal('Success!', 'Your Queue Has Been Saved!', 'success');

                        ClearForm('editqueueform');
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
        }
    });

    $(document).on('click', '#submitmovetickets', function()
    {
        if($('#toqueueselect').val() == 0)
        {
            swal('Hey!', 'You Must Select a Queue to Move These Tickets to.', 'error').then(function()
                {
                    $('#toqueueselect').selectpicker('toggle');
                });
            return;
        }

        $.ajax(
        {
            url:'movetickets',
            type: 'POST',
            data: $('#moveticketform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                if(resp[0] == "success")
                {
                    swal('Success!', 'Your Tickets Have Been Moved!', 'success');
                    var inactivestatus = '(Inactive)';
                    removeInactiveQueue(resp[1]);
                    $('#editqueueselect option:selected').text(resp[2] +' '+ inactivestatus);
                    $('#moveticketsmodal').modal('hide');
                    ClearForm('editqueueform');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })
    })

}); // end doc.ready

    function populateQueueSelects(queueid, queuename, active)
    {
        if(active == 0)
        {
            var status = '(Inactive)';
            $('#editqueueselect').append('<option value="'+queueid+'">'+queuename+' '+status+'</option>');
            $('#editqueueselect').selectpicker('refresh');
        }
        else
        {
            if(!$('#editqueueselect option[value="'+queueid+'"]').length)
            {
                $('#editqueueselect').append('<option value="'+queueid+'">'+queuename+'</option>');
                $('#editqueueselect').selectpicker('refresh');                 
            }


            $('#createcategoryqueueselect').append('<option value="'+queueid+'">'+queuename+'</option>');
            $('#createcategoryqueueselect').selectpicker('refresh'); 

            $('#editcategoryqueueselect').append('<option value="'+queueid+'">'+queuename+'</option>');
            $('#editcategoryqueueselect').selectpicker('refresh'); 

            $('#createnotificationqueueselect').append('<option value="'+queueid+'">'+queuename+'</option>');
            $('#createnotificationqueueselect').selectpicker('refresh'); 

            $('#editnotificationqueueselect').append('<option value="'+queueid+'">'+queuename+'</option>');
            $('#editnotificationqueueselect').selectpicker('refresh'); 

            $('#createqueueelevationselect').append('<option value="'+queueid+'">'+queuename+'</option>');
            $('#createqueueelevationselect').selectpicker('refresh');

            $('#editqueueelevationselect').append('<option value="'+queueid+'">'+queuename+'</option>');
            $('#editqueueelevationselect').selectpicker('refresh'); 
        }        
    }

    function removeInactiveQueue(queueid)
    {
        $('#createcategoryqueueselect option[value='+queueid+']').remove();
        $('#createcategoryqueueselect').selectpicker('refresh'); 

        $('#editcategoryqueueselect option[value='+queueid+']').remove();
        $('#editcategoryqueueselect').selectpicker('refresh'); 

        $('#createnotificationqueueselect option[value='+queueid+']').remove();
        $('#createnotificationqueueselect').selectpicker('refresh'); 

        $('#editnotificationqueueselect option[value='+queueid+']').remove();
        $('#editnotificationqueueselect').selectpicker('refresh'); 

        $('#createqueueelevationselect option[value='+queueid+']').remove();
        $('#createqueueelevationselect').selectpicker('refresh');

        $('#editqueueelevationselect option[value='+queueid+']').remove();
        $('#editqueueelevationselect').selectpicker('refresh'); 
    }