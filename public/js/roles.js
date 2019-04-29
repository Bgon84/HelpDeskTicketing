$(document).ready(function() 
{
    $("#createroleuserselect").selectpicker('refresh');
    $("#createrolepermissionselect").selectpicker('refresh');
    $('#editroleselect').selectpicker('refresh');
    $('#editrolepermissionselect').selectpicker('refresh');            
    $('#editroleuserselect').selectpicker('refresh');

    // Clear Create Role Form
    $('#clearcreateroleform').on('click', function()
    {
        ClearForm('createroleform');
    });

    // Clear Edit Role Form
    $('#cleareditroleform').on('click', function()
    {
        ClearForm('editroleform');
    });

    // Submit Role Creation
    $('#createrolesubmit').on('click', function()
    {
        var createrolename = $('#createrolename').val().trim();
        if(createrolename.length == 0)
        {
            swal('Hey!', 'How about naming this Role?', 'error');
            $('#createrolename').addClass('alert');
            return;
        }

        swal({
            title: "Submitting Your Role...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });

    	$.ajax(
        {
            url:'createrole',
            type: 'POST',
            data: $('#createroleform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                if(resp[0] == "success")
                {
                    var roleid = resp[1]['roleid'];
                    var rolename = resp[1]['rolename'];
                    var active = resp[1]['active'];

                    swal('Success!', 'Your Role Has Been Created!', 'success');

                    ClearForm('createroleform');

                    populateRoleSelects(roleid, rolename, active);                       
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

    // Populate Role Edit Form
    $('#editroleselect').on('change', function()
    {
    	var roleid = $('#editroleselect option:selected').val();       

        $('#editrolepermissionselect').val('default');
        $('#editrolepermissionselect').selectpicker('refresh');
        $('#editroleuserselect').val('default');                            
        $('#editroleuserselect').selectpicker('refresh');
        
        $.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax(
        {
            url: 'getroleinfo',
            type: 'POST',
            data: {roleid:roleid},
            success: function(resp)
            {
                $('#editrolename').val(resp['role']['rolename']);
                $('#editroledescription').val(resp['role']['description'])

                if(resp['users'].length > 0)
                {
                    var users = [];

                    for(i=0; i<resp['users'].length; i++)
                    {
                        users.push(resp['users'][i]['userid']);
                    }

                    $('#editroleuserselect').selectpicker('val', users);
                }

                if(resp['perms'].length > 0)
                {
                    var perms = [];

                    for(i=0; i<resp['perms'].length; i++)
                    {
                        perms.push(resp['perms'][i]['permissionid']);
                    }

                    $('#editrolepermissionselect').selectpicker('val', perms);
                }

                if(resp['role']['active'] == 1)
                {
                    $('#editroleactive1').prop('checked', true);
                }
                else
                {
                    $('#editroleactive0').prop('checked', true);
                }                
            }
        });                       
    });

    // Submit Edit Role
    $('#editrolesubmit').on('click', function()
    {
        if($('#editroleselect').val().length == 0)
        {
            swal('Hey!', 'Please Select a Role to Edit.', 'error').then(function()
                {
                    $('#editroleselect').selectpicker('toggle');
                });
            return;
        }

        var editrolename = $('#editrolename').val().trim();

        if(editrolename.length == 0)
        {
            swal('Hey!', 'You Cannot Have a Blank Role Name.', 'error');
            $('#editrolename').addClass('alert');
            return;
        }

        var newname = $('#editrolename').val().toUpperCase();
        var roleid = $('#editroleselect').val();
        
        if($('#editroleactive0').is(':checked'))
        {
            var inactivestatus = '(Inactive)';
            hideInactiveRole(roleid);
        }
        else
        {
            var inactivestatus = '';
            showReactivatedRole(roleid);
        }

        $('#editroleselect option:selected').text(newname +' '+ inactivestatus);

        swal({
            title: "Submitting Your Role...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });
        
        $.ajax(
        {
            url:'editrole',
            type: 'POST',
            data: $('#editroleform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                if(resp == "success")
                {
                    swal('Success!', 'Your Role Has Been Saved!', 'success');

                    ClearForm('editroleform');                    

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

}); // end of doc.ready


function populateRoleSelects(roleid, rolename, active)
{
    if(active == 0)
    {
        var status = '(Inactive)';
    }
    else
    {
        var status = '';
    }
    $('#editroleselect').append('<option value="'+roleid+'">'+rolename+' '+status+'</option>');
    $('#editroleselect').selectpicker('refresh');

    $('#creategrouproleselect').append('<option value="'+roleid+'">'+rolename+'</option>');
    $('#creategrouproleselect').selectpicker('refresh');

    $('#editgrouproleselect').append('<option value="'+roleid+'">'+rolename+'</option>');
    $('#editgrouproleselect').selectpicker('refresh');

    $('#createuserroleselect').append('<option value="'+roleid+'">'+rolename+'</option>');
    $('#createuserroleselect').selectpicker('refresh');

    $('#edituserroleselect').append('<option value="'+roleid+'">'+rolename+'</option>');
    $('#edituserroleselect').selectpicker('refresh');    
}

function hideInactiveRole(roleid)
{
    $('#creategrouproleselect option[value='+roleid+']').hide();
    $('#creategrouproleselect').selectpicker('refresh');
    $('#editgrouproleselect option[value='+roleid+']').hide();
    $('#editgrouproleselect').selectpicker('refresh'); 

    $('#createuserroleselect option[value='+roleid+']').hide();
    $('#createuserroleselect').selectpicker('refresh');
    $('#edituserroleselect option[value='+roleid+']').hide();
    $('#edituserroleselect').selectpicker('refresh'); 

}

function showReactivatedRole(roleid)
{
    $('#creategrouproleselect option[value='+roleid+']').show();
    $('#creategrouproleselect').selectpicker('refresh');
    $('#editgrouproleselect option[value='+roleid+']').show();
    $('#editgrouproleselect').selectpicker('refresh'); 

    $('#createuserroleselect option[value='+roleid+']').show();
    $('#createuserroleselect').selectpicker('refresh');
    $('#edituserroleselect option[value='+roleid+']').show();
    $('#edituserroleselect').selectpicker('refresh'); 
}