$(document).ready(function() 
{
    $('#creategrouproleselect').selectpicker('refresh');
    $('#creategroupuserselect').selectpicker('refresh');            
    $('#editgroupselect').selectpicker('refresh');
    $('#editgrouproleselect').selectpicker('refresh');
    $('#editgroupuserselect').selectpicker('refresh');

    // Clear selectpickers on Create Group form
    $('#clearcreategroup').on('click', function()
    {
        ClearForm('creategroupform');
    });

    // Clear selectpickers on Edit Group form
    $('#cleareditgroup').on('click', function()
    {
        ClearForm('editgroupform');        
    });
    
    // Submit Group Creation
    $('#creategroupsubmit').on('click', function()
    {
        var creategroupname = $('#creategroupname').val().trim();
        if(creategroupname.length == 0)
        {
            swal('Hey!', 'How about naming this Group?', 'error');
            $('#creategroupname').addClass('alert');
            $('#creategroupname').val('');
            return;
        }

    swal({
        title: "Submitting Your Group...",
        text: "Please be patient!",
        showCancelButton: false,
        showConfirmButton: false,
        allowEscapeKey: false,
        allowOutsideClick: false,
        imageUrl: "/img/loadgear.gif"
    });
        $.ajax(
        {
            url:'creategroup',
            type: 'POST',
            data: $('#creategroupform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            { 
                if(resp[0] == "success")
                {
                    var groupid = resp[1]['groupid'];
                    var groupname = resp[1]['groupname'];
                    var active = resp[1]['active'];

                    swal('Success!', 'Your Group Has Been Created!', 'success');

                    ClearForm('creategroupform');

                    populateGroupSelects(groupid, groupname, active);                           
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

    // Populate Group Editing Form when Group is selected
    $('#editgroupselect').on('change', function()
    {
        var groupid = $('#editgroupselect option:selected').val();       

        $('#editgrouproleselect').val('default');
        $('#editgrouproleselect').selectpicker('refresh');
        $('#editgroupuserselect').val('default');                            
        $('#editgroupuserselect').selectpicker('refresh');

        $.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax(
        {
            url: 'getgroupinfo',
            type: 'POST',
            data: {groupid:groupid},
            success: function(resp)
            {
                $('#editgroupname').val(resp['group']['groupname']);
                $('#editgroupdescription').val(resp['group']['description']);

                if(resp['users'].length > 0)
                {
                    var users = [];

                    for(i=0; i<resp['users'].length; i++)
                    {
                        users.push(resp['users'][i]['userid']);
                    }

                    $('#editgroupuserselect').selectpicker('val', users);
                }

                if(resp['roles'].length > 0)
                {
                    var roles = [];

                    for(i=0; i<resp['roles'].length; i++)
                    {
                        roles.push(resp['roles'][i]['roleid']);
                    }

                    $('#editgrouproleselect').selectpicker('val', roles);
                }


                if(resp['group']['active'] == 1)
                {
                    $('#editgroupactive1').prop('checked', true);
                }
                else
                {
                    $('#editgroupactive0').prop('checked', true);
                }                
            }
        });                               
    });
    
    // Submit Group Editing          
    $('#editgroupsubmit').on('click', function()
    {        
        if($('#editgroupselect').val().length == 0)
        {
            swal('Hey!', 'Please Select a Group to Edit.', 'error').then(function()
                {
                    $('#editgroupselect').selectpicker('toggle');
                });
            return;
        }

        var editgroupname = $('#editgroupname').val().trim();

        if(editgroupname.length == 0)
        {
            swal('Hey!', 'You Cannot Have a Blank Group Name.', 'error');
            $('#editgroupname').addClass('alert');
            $('#editgroupname').val('');
            return;
        }

        var newname = $('#editgroupname').val().toUpperCase();
        var groupid = $('#editgroupselect').val();
        
        if($('#editgroupactive0').is(':checked'))
        {
           var inactivestatus = '(Inactive)';
           hideInactiveGroup(groupid);
        }
        else
        {
           var inactivestatus = '';
           showReactivatedGroup(groupid);
        }

        $('#editgroupselect option:selected').text(newname +' '+ inactivestatus);

        swal({
            title: "Submitting Your Group...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });
        
        $.ajax(
        {
            url:'editgroup',
            type: 'POST',
            data: $('#editgroupform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                if(resp == "success")
                {
                    swal('Success!', 'Your Group Has Been Saved!', 'success');
   
                    ClearForm('editgroupform');            
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

}); // end doc.ready()

    function populateGroupSelects(groupid, groupname, active)
    {
        var status = '';

        if(active == 0)
        {
            status = ' (Inactive)';
        }

        $('#editgroupselect').append('<option value="'+groupid+'">'+groupname+status+'</option>');
        $('#editgroupselect').selectpicker('refresh'); 
        
        $('#createusergroupselect').append('<option value="'+groupid+'">'+groupname+'</option>');
        $('#createusergroupselect').selectpicker('refresh'); 

        $('#editusergroupselect').append('<option value="'+groupid+'">'+groupname+'</option>');
        $('#editusergroupselect').selectpicker('refresh'); 

        $('#createqueuegroupselect').append('<option value="'+groupid+'">'+groupname+'</option>');
        $('#createqueuegroupselect').selectpicker('refresh'); 

        $('#editqueuegroupselect').append('<option value="'+groupid+'">'+groupname+'</option>');
        $('#editqueuegroupselect').selectpicker('refresh'); 
    }

    function hideInactiveGroup(groupid)
    {
        $('#createusergroupselect option[value='+groupid+']').hide();
        $('#createusergroupselect').selectpicker('refresh');
        $('#editusergroupselect option[value='+groupid+']').hide();
        $('#editusergroupselect').selectpicker('refresh'); 

        $('#createqueuegroupselect option[value='+groupid+']').hide();
        $('#createqueuegroupselect').selectpicker('refresh');
        $('#editqueuegroupselect option[value='+groupid+']').hide();
        $('#editqueuegroupselect').selectpicker('refresh'); 

    }

    function showReactivatedGroup(groupid)
    {
        $('#createusergroupselect option[value='+groupid+']').show();
        $('#createusergroupselect').selectpicker('refresh');
        $('#editusergroupselect option[value='+groupid+']').show();
        $('#editusergroupselect').selectpicker('refresh'); 

        $('#createqueuegroupselect option[value='+groupid+']').show();
        $('#createqueuegroupselect').selectpicker('refresh');
        $('#editqueuegroupselect option[value='+groupid+']').show();
        $('#editqueuegroupselect').selectpicker('refresh'); 
    }