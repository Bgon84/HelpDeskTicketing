$(document).ready(function() 
{
    $('#createuserroleselect').selectpicker('refresh');
    $('#createusergroupselect').selectpicker('refresh'); 
    $('#createusermanagerselect').selectpicker('refresh'); 
    $('#createuserpriorityorselect').selectpicker('refresh');    
        
    $('#usereditselect').selectpicker('refresh');
    $('#edituserroleselect').selectpicker('refresh');
    $('#editusergroupselect').selectpicker('refresh');
    $('#editusermanagerselect').selectpicker('refresh');
    $('#edituserpriorityorselect').selectpicker('refresh'); 


    // Clear Create User form
    $('#clearcreateuser').on('click', function()
    {
        ClearForm('createuserform');        
    });

    // Clear Edit User form
    $('#clearedituser').on('click', function()
    {
        ClearForm('edituserform');
        $('#optuserbtn').prop('disabled', true); 
        $('#optuserbtn').val('N/A');
    });
    
    //First and Last Name Validation (Alpha chars only)
    $('input.name').on('change', function()
    {
        var name = this.value.trim();
        
        if(!onlyAlpha(name))
        {
            swal('Whoops!', 'First and Last Names May Only Contain Alphabetic Characters.', 'error');
            $(this).addClass('alert');
        }
        else
        {
            $(this).removeClass('alert');
        }
    });

    $('input.otheremail').on('change', function()
    {
        var email = this.value;
        
        if(email !== '' && !validateEmail(email))
        {
            swal('Whoops!', 'Email Addresses Must Be in "user@domain.com" format.', 'error');
            $(this).addClass('alert');
            $(this).focus();
        }
        else
        {
            $(this).removeClass('alert');
        }
    })

    $('#createuserpass').on('change', function()
    {
        if($('#createuserpass').hasClass('alert'))
        {
            $(this).removeClass('alert');
        }
    });

    // password confirmation match check
    $('#createuserpassconfirm').on('change', function()
    {
        var pass = $('#createuserpass').val();
        var confirm = $('#createuserpassconfirm').val()

        if(pass !== confirm)
        {
            swal('Uh Oh!', 'Your passwords do not match.', 'error');
        }
        else
        {
            if($('#createuserpassconfirm').hasClass('alert'))
            {
                $(this).removeClass('alert');
            }
        }
    });

    // edit password confirmation match check
    $('#edituserpassconfirm').on('change', function()
    {
        var pass = $('#edituserpass').val();
        var confirm = $('#edituserpassconfirm').val();

        if(pass !== confirm)
        {
            swal('Uh Oh!', 'Your passwords do not match.', 'error');
        }
    });
    


    // Submit User Creation
    $('#createusersubmit').on('click', function()
    {
        var blank = false;
        $('#createuserform input.required').each(function(e)
        {
            if(this.value.trim() == '')
            {                
                $(this).addClass('alert');
                blank = true;      
            }
        });

        if(blank)
        {
            swal('Could Not Submit','Please Fill All Required Fields', 'error');
            e.preventDefault();                
            return;
        }
        
        var pass = $('#createuserpass').val();
        var confirm = $('#createuserpassconfirm').val();

        if(pass !== confirm)
        {
            swal('Uh Oh!', 'Your passwords do not match.', 'error');
            return;
        }

        if($('#createuserform input').hasClass('alert'))
        {
            swal('Could Not Submit', 'You Have Invalid Input. Please Correct.', 'error');
            return;
        }

        $.ajax(
        {
            url: 'createuser',
            type: 'POST',
            data: $('#createuserform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                console.log(resp);
                if(resp[0] == "success")
                {
                    var userid = resp[1]['userid'];
                    var name = resp[1]['name'];
                    var active = resp[1]['active'];

                    swal('Success!', 'Your User Has Been Created!', 'success');
  
                    ClearForm('createuserform');     

                    populateUserSelects(userid, name, active);                     
                }
                else if(resp.split(':')[1] == ' Unique violation')
                {
                    swal('Opps!', 'That Email already exists!', 'error');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })
    });



    // Populate User Editing Form when User is selected
    $('#usereditselect').on('change', function()
    {
        $('#editusermanagerselect option').prop('disabled', false);

        var userid = $('#usereditselect option:selected').val();       

        $('#edituserroleselect').val('default');
        $('#edituserroleselect').selectpicker('refresh');
        $('#editusergroupselect').val('default');                            
        $('#editusergroupselect').selectpicker('refresh');
        $('#edituserpriorityorselect').val('default');                            
        $('#edituserpriorityorselect').selectpicker('refresh');
        $('#editusermanagerselect').val('default');
        // disable the selected user's option in Manager select
        $('#editusermanagerselect option[value="' + userid + '"').prop('disabled', true);
        $('#editusermanagerselect').selectpicker('render');

        $('#editusertbl input.alert').removeClass('alert');

        getUserInformation(userid);
    });
    


    // Submit User Editing          
    $(document).on('click', '#editusersubmit', function()
    {
        if($('#usereditselect').val().length == 0)
        {
            swal('Hey!', 'Please Select a User to Edit.', 'error').then(function()
                {
                    $('#usereditselect').selectpicker('toggle');
                });
            return;
        }

        var blank = false;
        $('#edituserform input.required').each(function(e)
        {
            if(this.value.trim() == '')
            {                
                $(this).addClass('alert');
                blank = true;
            }
        });

        if(blank)
        {
            swal('Could Not Submit','Please Fill All Required Fields', 'error');
            e.preventDefault();
            return false;
        }

        var pass = $('#edituserpass').val().trim();
        var confirm = $('#edituserpassconfirm').val().trim();

        if(pass !== confirm)
        {
            swal('Uh Oh!', 'Your passwords do not match.', 'error');
            return;
        }

        if($('input').hasClass('alert'))
        {
            swal('Could Not Submit', 'You Have Invalid Input. Please Correct.', 'error');
            return;
        }

        var oldname = $('#usereditselect option:selected').text().split('(')[0];
        var logintype = '(' + $('#usereditselect option:selected').text().split('(')[1];
        var newname = $('#edituserfirstname').val() + ' ' + $('#edituserlastname').val();
        var userid = $('#usereditselect').val();

        if($('#edituseractive0').is(':checked'))
        {
            var inactivestatus = '(Inactive)';
            hideInactiveUser(userid);
        }
        else
        {
            var inactivestatus = '';
            showReactivatedUser(userid);
        }

        $('#usereditselect option:selected').text(newname + ' ' + logintype + ' ' + inactivestatus);
        
        $('#edituserform').find(':disabled').prop('disabled', false);

        $.ajax(
        {
            url:'edituser',
            type: 'POST',
            data: $('#edituserform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                if(resp == "success")
                {
                    swal('Success!', 'Your User Has Been Saved!', 'success');
  
                    ClearForm('edituserform');   
                    $('#optuserbtn').prop('disabled', true); 
                    $('#optuserbtn').val('N/A');      

                }
                else if(resp.split(':')[1] == ' Unique violation')
                {
                    swal('Opps!', 'That Email already exists!', 'error');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })
    });



    // Opting In and Out
    $('#optuserbtn').on('click', function()
    { 
        var userid = $('#selecteduserid').val();
        var _token = $('#token').val();

        $.ajax(
        {
            url:'opt',
            type: 'POST',
            data: {_token:_token, userid:userid},
            datatype: 'JSON',            
            success: function(resp)
            {
                if(resp == "opted in")
                {
                    swal('Success', 'This user will now be receiving tickets', 'success');  
                    $('#optuserbtn').val('Active');         
                }

                if(resp == 'opted out')
                {
                    swal('Success', 'This user will no longer be receiving tickets', 'success');
                    $('#optuserbtn').val('Inactive');
                }
            }
        })
    });

}); // end doc.ready()


function onlyAlpha(input)  
{  
   var regex = /^[A-Za-z]+$/;  
   return regex.test(input);
}  

function populateUserSelects(userid, name, active)
{
    var status = '';

    if(active == 0)
    {
        status = ' (Inactive)';
    }

    $('#usereditselect').append('<option value="'+userid+'">'+name+' (Native) '+status+'</option>');
    $('#usereditselect').selectpicker('refresh');

    $('#createusermanagerselect').append('<option value="'+userid+'">'+name+'</option>');
    $('#createusermanagerselect').selectpicker('refresh');
    $('#editusermanagerselect').append('<option value="'+userid+'">'+name+'</option>');
    $('#editusermanagerselect').selectpicker('refresh');

    $('#createroleuserselect').append('<option value="'+userid+'">'+name+'</option>');
    $('#createroleuserselect').selectpicker('refresh');
    $('#editroleuserselect').append('<option value="'+userid+'">'+name+'</option>');
    $('#editroleuserselect').selectpicker('refresh');

    $('#createqueueuserselect').append('<option value="'+userid+'">'+name+'</option>');
    $('#createqueueuserselect').selectpicker('refresh');
    $('#editqueueuserselect').append('<option value="'+userid+'">'+name+'</option>');
    $('#editqueueuserselect').selectpicker('refresh');

    $('#createnotificationuserselect').append('<option value="'+userid+'">'+name+'</option>');
    $('#createnotificationuserselect').selectpicker('refresh');
    $('#editnotificationuserselect').append('<option value="'+userid+'">'+name+'</option>');
    $('#editnotificationuserselect').selectpicker('refresh');

    $('#creategroupuserselect').append('<option value="'+userid+'">'+name+'</option>');
    $('#creategroupuserselect').selectpicker('refresh');
    $('#editgroupuserselect').append('<option value="'+userid+'">'+name+'</option>');
    $('#editgroupuserselect').selectpicker('refresh');
}

function hideInactiveUser(userid)
{
    $('#createusermanagerselect option[value='+userid+']').hide();
    $('#createusermanagerselect').selectpicker('refresh');
    $('#editusermanagerselect option[value='+userid+']').hide();
    $('#editusermanagerselect').selectpicker('refresh'); 

    $('#createroleuserselect option[value='+userid+']').hide();
    $('#createroleuserselect').selectpicker('refresh');
    $('#editroleuserselect option[value='+userid+']').hide();
    $('#editroleuserselect').selectpicker('refresh'); 

    $('#createqueueuserselect option[value='+userid+']').hide();
    $('#createqueueuserselect').selectpicker('refresh');
    $('#editqueueuserselect option[value='+userid+']').hide();
    $('#editqueueuserselect').selectpicker('refresh'); 

    $('#createnotificationuserselect option[value='+userid+']').hide();
    $('#createnotificationuserselect').selectpicker('refresh');
    $('#editnotificationuserselect option[value='+userid+']').hide();
    $('#editnotificationuserselect').selectpicker('refresh'); 

    $('#creategroupuserselect option[value='+userid+']').hide();
    $('#creategroupuserselect').selectpicker('refresh');
    $('#editgroupuserselect option[value='+userid+']').hide();
    $('#editgroupuserselect').selectpicker('refresh');
}

function showReactivatedUser(userid)
{
    $('#createusermanagerselect option[value='+userid+']').show();
    $('#createusermanagerselect').selectpicker('refresh');
    $('#editusermanagerselect option[value='+userid+']').show();
    $('#editusermanagerselect').selectpicker('refresh'); 

    $('#createroleuserselect option[value='+userid+']').show();
    $('#createroleuserselect').selectpicker('refresh');
    $('#editroleuserselect option[value='+userid+']').show();
    $('#editroleuserselect').selectpicker('refresh'); 

    $('#createqueueuserselect option[value='+userid+']').show();
    $('#createqueueuserselect').selectpicker('refresh');
    $('#editqueueuserselect option[value='+userid+']').show();
    $('#editqueueuserselect').selectpicker('refresh'); 

    $('#createnotificationuserselect option[value='+userid+']').show();
    $('#createnotificationuserselect').selectpicker('refresh');
    $('#editnotificationuserselect option[value='+userid+']').show();
    $('#editnotificationuserselect').selectpicker('refresh'); 

    $('#creategroupuserselect option[value='+userid+']').show();
    $('#creategroupuserselect').selectpicker('refresh');
    $('#editgroupuserselect option[value='+userid+']').show();
    $('#editgroupuserselect').selectpicker('refresh');
}

function getUserInformation(userid)
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
        url: 'getuserinfo',
        type: 'POST',
        data: {userid:userid},
        success: function(resp)
        {
            var email = resp['selecteduser']['email'];
            var name = resp['selecteduser']['name'];
            var officephone = resp['selecteduser']['phoneNumber'];
            var ext = resp['selecteduser']['extension'];
            var mobile = resp['selecteduser']['mobilephone'];
            var logintype = resp['selecteduser']['logintype'];
            var userid = resp['selecteduser']['userid'];               
            var firstname = name.split(' ')[0];
            var lastname = name.split(' ')[1];

            if(resp['manager'] !== null)
            {
                var managerid = resp['selecteduser']['manager'];
                $('#editusermanagerselect').selectpicker('val', managerid);
            }
            else
            {
                $('#editusermanagerselect').selectpicker('val', 0);                    
            }

            $('#edituserfirstname').val(firstname);
            $('#edituserlastname').val(lastname);
            $('#edituserprimaryemail').val(email); 
            $('#edituserprimaryphone').val(officephone);
            $('#edituserprimaryphoneext').val(ext);
            $('#edituserotherphone').val(mobile);
            
            if(logintype == 'LDAP')
            {
                $('#edituserfirstname').prop("readonly", true);
                $('#edituserlastname').prop("readonly", true);
                $('#edituserprimaryemail').prop("readonly", true); 
                $('#edituserprimaryphone').prop("readonly", true);
                $('#edituserprimaryphoneext').prop("readonly", true);
                $('#edituserotherphone').prop("readonly", true);
                $('#edituserpass').prop("readonly", true);
                $('#edituserpassconfirm').prop("readonly", true);
                $('#editusermanagerselect').prop("disabled", true);
                $('#editusermanagerselect').selectpicker("refresh");
                $('#edituserform input').removeClass('required');
            }
            else
            {
                $('#edituserfirstname').prop("readonly", false);
                $('#edituserlastname').prop("readonly", false);
                $('#edituserprimaryemail').prop("readonly", false); 
                $('#edituserprimaryphone').prop("readonly", false);
                $('#edituserprimaryphoneext').prop("readonly", false);
                $('#edituserotherphone').prop("readonly", false);
                $('#edituserpass').prop("readonly", false);
                $('#edituserpassconfirm').prop("readonly", false);
                $('#editusermanagerselect').prop("disabled", false);
                $('#editusermanagerselect').selectpicker("refresh");
            }
            console.log(resp['permissions']);
            if(resp['permissions'].length > 0)
            { 
                if($.inArray('Receive_Tickets', resp['permissions']) !== -1)
                {
                    $('#optuserbtn').prop('disabled', false);
                    $('#selecteduserid').val(userid);

                    if(resp['selecteduser']['optedin'] == 1)
                    {
                        $('#optuserbtn').val('Active');
                    }
                    else
                    {
                        $('#optuserbtn').val('Inactive');
                    }
                }
                else
                {
                    $('#optuserbtn').prop('disabled', true); 
                    $('#optuserbtn').val('N/A');                   
                }
            }
            else
            {
                $('#optuserbtn').prop('disabled', true); 
                $('#optuserbtn').val('N/A');
            }

            if(resp['groups'].length > 0)
            {
                var groups = [];

                for(i=0; i<resp['groups'].length; i++)
                {
                    groups.push(resp['groups'][i]['groupid']);
                }

                $('#editusergroupselect').selectpicker('val', groups);
            }
            
            
            if(resp['roles'].length > 0)
            {
                var roles = [];

                for(i=0; i<resp['roles'].length; i++)
                {
                    roles.push(resp['roles'][i]['roleid']);                   
                }

                $('#edituserroleselect').selectpicker('val', roles);
            }

            if(resp['priorityor'].length > 0)
            {
                $('#edituserpriorityorselect').selectpicker('val', resp['priorityor'][0]['level']);
                $('#edituserpriorityorselect').selectpicker('refresh');
            }
            

            if(resp['selecteduser']['active'] == 1)
            {
                $('#edituseractive1').prop('checked', true);
            }
            else
            {
                $('#edituseractive0').prop('checked', true);
                $('#optuserbtn').prop('disabled', true); 
                $('#optuserbtn').val('N/A');
            }

            if(resp['selecteduser']['direxclude'] == 1)
            {
                $('#edituserdirexclude1').prop('checked', true);
            }
            else
            {
                $('#edituserdirexclude0').prop('checked', true);
            }
        }
    });  
}