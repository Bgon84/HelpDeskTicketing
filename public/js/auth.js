$(document).ready(function()
{
    $(".authopt").bootstrapSwitch();

    // Get Authentication Settings When Option is Toggled
    $('input[name=authopt]').on('switchChange.bootstrapSwitch', function(event, state)
    {   
        // Get all the inputs that are checked.
        var countChecked = 0;
        $('input[name=authopt]:checked').each( function()
        {
            countChecked++;
        });

        // If 1 then one other is checked
        // If 2 both are checked
        // If 0 non are checked.

        var inputId = $(this).attr('id');
        // We need the id of the option so split after the -
        inputId = inputId.split('-').pop();

        var updateState = 0;

        if(state === true)
        {
            updateState = 1;
        }

        if((updateState == 0 && countChecked != 0) || (updateState == 1))
        {
            // Now based on what updateState is update the DB.
            if(inputId > 0)
            { // Need to have an id if not do not update.
                
                swal({
                    title: "Verifying...",
                    text: "Please be patient!",
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    imageUrl: "/img/loadgear.gif"
                });
                
                $.ajaxSetup(
                    {
                        headers:
                        {
                            'X-CSRF-Token': $('meta[name="_token"]').attr('content')
                        }
                    }
                );

                // Ajax the value to update the db
                $.ajax(
                    {
                        url: 'updateAuthOptions',
                        type: 'POST',
                        data: {
                            optionID : inputId,
                            state : updateState
                        },
                        success: function(data)
                        {
                            if(data == 'no admin')
                            {
                                swal('Cannot Turn LDAP off', 'There are no active Native users <br> with the proper Admin permissions. <br> LDAP cannot be turned off at this time.', 'error');
                                $('#authopt-'+inputId).bootstrapSwitch('state', true, true);
                            }
                            else if (data == 'connection failed')
                            {
                                swal('Could Not Connect!', 'Your LDAP settings are not valid, please verify and retry.', 'error');
                                $('#authopt-'+inputId).bootstrapSwitch('state', false, true);
                                $('#auth-ldap-settings').removeClass('hidden');
                            }
                            else
                            {
                                data = JSON.parse(data);

                                if("error" in data)
                                {
                                    // Alert failed to save.
                                    swal("Failed Authorization Update", "Something went wrong and we could not update the authorization option, please try again.", "error");
                                    
                                    if($('#authopt-'+inputId).bootstrapSwitch('state') == true)
                                    {
                                        $('#authopt-'+inputId).bootstrapSwitch('state', false, true);
                                    }
                                    else
                                    {
                                        $('#authopt-'+inputId).bootstrapSwitch('state', true, true);
                                    }
                                    
                                }
                                else
                                {
                                    if(data['ldap'] == 0)
                                    {
                                        $("#auth-ldap-settings").addClass('hidden');
                                    }
                                    else
                                    {
                                        $("#auth-ldap-settings").removeClass('hidden');
                                    }

                                    swal("Authorization Option Updated", "We have successfully updated the authorization option.", "success");
                                }
                            }
                        }
                    }
                );
            }
        }
        else
        {
            $(this).bootstrapSwitch('state', true, true);
            swal("Cannot Update", "You cannot set all options to off, no one would be able to login.", "error");
        }
    });

    // Sync ldap
    $(document).on("click", "#sync-ldap-users", function(e)
    {
        e.preventDefault();

        $.ajaxSetup(
            {
                headers:
                {
                    'X-CSRF-Token': $('meta[name="_token"]').attr('content')
                }
            }
        );

        swal(
            {
                title: "Sync LDAP?",
                text: "Would you like to sync your LDAP users? This could take a few seconds.",
                type: "info",
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: function()
                {
                    return new Promise(function(resolve, reject)
                        {
                            // Send to route.
                            $.ajax(
                                {
                                    url: "syncLdap",
                                    type: "GET",
                                    success: function(r)
                                    {
                                        resolve('success');
                                    },
                                    error: function(e)
                                    {
                                        resolve('error');
                                    }
                                }
                            );
                        }
                    );
                }
            }
        ).then(function(message)
        {
            if(message == 'success')
            {
                swal("LDAP Sync Complete", "We have successfully synced to LDAP.", "success");
            }
            else
            {
                swal("Something Went Wrong", "Either your credentials are not correct or we are blocked from LDAP, please verify your information and try again.", "error");
            }
        }, function (dismiss)
        {
            if(dismiss === "cancel")
            {
                swal("LDAP Sync Cancelled", "Your LDAP sync has been cancelled", "success");
            }
        }); 
    });

    // Show edit auth form
    $('#editauthbtn').on('click', function()
    {
        $('#authsettingstbl').css('display', 'none');
        $('#editauthsettingstbl').css('display', 'table');
    });

    // Save edited Authentication Settings
    $('#saveeditauthbtn').on('click', function(e)
    {
        if($('#editauthserver').val().length < 1 && $('#editauthserver').val().length < 1)
        {
            swal('Just a second...', 'A Server is Required.', 'error');
            $('#editauthserver').addClass('alert');
            e.preventDefault();
            return;
        }

        if($('#editauthport').val().length < 1 && $('#editauthport').val().length < 1)
        {
            swal('Just a second...', 'A Port is Required.', 'error');
            $('#editauthport').addClass('alert');
            e.preventDefault();
            return;
        }

        if($('#editauthusername').val().length < 1 && $('#editauthusername').val().length < 1)
        {
            swal('Just a second...', 'A Username is Required.', 'error');
            $('#editauthusername').addClass('alert');
            e.preventDefault();
            return;
        }

        if($('#editauthpass').val().length < 1 && $('#editauthpass').val().length < 1)
        {
            swal('Just a second...', 'A Password is Required.', 'error');
            $('#editauthpass').addClass('alert');
            e.preventDefault();
            return;
        }

        if($('#editauthbind').val().length < 1 && $('#editauthbind').val().length < 1)
        {
            swal('Just a second...', 'A Bind DN is Required.', 'error');
            $('#editauthbind').addClass('alert');
            e.preventDefault();
            return;
        }

        if(!isPosInt($('#editauthport').val()))
        {
            swal('Whoops!', 'Port must be a positive integer!', 'error');
            $('#smtpport').addClass('alert');
            return;
        }

        swal({
            title: "Verifying...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });
        
        $('#editauthsettingstbl').css('display', 'none');
        $('#authsettingstbl').css('display', 'table');

        $.ajax(
        {
            url: 'editauth',
            type: 'POST',
            data: $('#autheditform').serialize(),
            success: function(resp)
            {
                $('.alert').removeClass('alert');

                if(resp == 'success')
                {
                    swal('Success!', 'Authentication Settings Have Been Updated.', 'success');

                    $('#currauthname').html($('#editauthname').val());
                    $('#currauthserver').html($('#editauthserver').val());
                    $('#currauthport').html($('#editauthport').val());
                    $('#currauthusername').html($('#editauthusername').val());
                    //$('#currauthpass').html($('#editauthpass').val());
                    $('#currauthbind').html($('#editauthbind').val());
                    $('#currauthfilter').html($('#editauthfilter').val());              
                }
                else if(resp.split(':')[1] == ' Unique violation')
                {
                    swal('Opps!', 'That Name already exists!', 'error');
                }
                else if(resp == "connection failed")
                {
                    swal('Could Not Connect!', 'Your LDAP settings are not valid, please verfiy and retry.', 'error');
                    ClearForm('autheditform');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })
    });

    $('#ldapsyncsavebtn').on('click', function(e)
    {
        var interval = $('#ldapsyncinterval').val();
        var enabled = true;

        if(isNaN(interval))
        {
            swal('Opps!', 'The Interval must be a numeric value!', 'error');
            e.preventDefault();
            return;
        }

        if(!$('#ldapsyncenable').is(':checked'))
        {
            enabled = false;
        }

        $.ajaxSetup(
            {
                headers:
                {
                    'X-CSRF-Token': $('meta[name="_token"]').attr('content')
                }
            }
        );
        
        $.ajax(
        {
            url: 'editldapsynctrigger',
            type: 'POST',
            data: {interval:interval, enabled:enabled},
            success: function(resp)
            {
                if(resp == 'success')
                {
                    swal('Success!', 'LDAP Sync Trigger Settings Have Been Updated.', 'success');          
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })

    })

}); // end of doc.ready 
                    
                            