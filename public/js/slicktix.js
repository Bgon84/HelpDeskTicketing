

$(document).ready(function()
{
	// Tabs
	$('#tabs a').click(function(e) 
	{
	  	e.preventDefault();
	  	$(this).tab('show');
	});

	$('.tabs a').on('click', function()
	{
		$('li').removeClass('active');
		var target = $(this).attr('data-target').split('#')[1];
		$('#li' + target).addClass('active');
	})

    // Remember selected tab and return when page is refreshed
	if(location.hash !== '')
    {
        $('a[data-target="' + location.hash + '"]').tab('show');
    } 

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) 
    {
        if(history.pushState) 
        {
            history.pushState(null, null, '#'+$(e.target).attr('data-target').substr(1));
        } 
        else 
        {
            location.hash = '#'+$(e.target).attr('data-target').substr(1);
        }
    });

	// end tabs

    // User Create/Edit and User Settings Update Info validations 
        
    // Phone Number Validation
    $(document).on("keyup", "input.primaryphone", function()
    {
        $(this).stripToNumeric().formatPhone();
    });

    $(document).on("keyup", "input.otherphone", function()
    {
        $(this).stripToNumeric().formatPhone();
    });

    $('input.primaryphone').on('change', function()
    {   
        var phone = this.value;

        if(phone.length < 14)
        {
            swal('Whoops!','Phone numbers must have ten digits (###) ###-####. <br> Please complete.', 'error');
            $(this).addClass('alert');
            $(this).focus();
        }

        if(phone.length == 14)
        {
            $(this).removeClass('alert');
        }        
    });

    $('input.otherphone').on('change', function()
    {   
        var phone = this.value;

        if(phone.length < 14 && phone !== '() -')
        {
            swal('Whoops!','Phone numbers must have ten digits (###) ###-####. <br> Please complete.', 'error');
            $(this).addClass('alert');
            $(this).focus();
        }

        if(phone.length == 14 || phone == '() -')
        {
            $(this).removeClass('alert');
        }        
    });

    // Extension numeric validation
    $('input.ext').on('change', function()
    {   
        var ext = this.value;

        if(isNaN(ext))
        {
            swal('Whoops!', 'Phone Extensions Must Be Only Numeric.', 'error');
            $(this).addClass('alert');
            $(this).focus();
        }
        else 
        {
            $(this).removeClass('alert');
        }
    });

    //Email validation
    $('input.primaryemail').on('change', function()
    {
        var email = this.value;
        
        if(!validateEmail(email))
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

    // End User info validations


	// New Ticket
    $('#newticketbtn').on('click', function()
    {
    	$('#newticketmodal').modal('show');
    });

    $('#categoryselect').selectpicker('refresh');
    $('#techselect').selectpicker('refresh');
    $('#proxyselect').selectpicker('refresh');

    $('#clearnewtickform').on('click', function()
    {
    	clearTicketModal();
    });


    $('#submitnewticket').on('click', function()
    {
        if($('#categoryselect').val() == '')
        {
            swal({
                title: 'Wait a Second!', 
                text: 'You Must Select a Category For This Ticket.', 
                type: 'error',
            }).then(function()
                {
                    $('#categoryselect').selectpicker('toggle');
                });
           
            return;
        }

        if($('#description').val().trim() == '')
        {
            swal({
                title: 'Wait a Second!', 
                text: 'You Must Provide a Description of Your Problem.', 
                type: 'error',
            }).then(function()
                {
                    $('#description').focus().addClass('alert');
                });

            return;
        }

        if($('#selecttechdiv').hasClass('required') && $('#techselect').val() == 0)
        {
            swal({
                title: 'Wait a Second!', 
                text: 'You Must Select a Tech For This Ticket.', 
                type: 'error',
            }).then(function()
                {
                    $('#techselect').selectpicker('toggle');
                });
           
            return;
        }

        var attachments = $('#attachment').prop('files');

        if(attachments.length > 0)
        {
            var totalsize = 0;
            for(i=0; i<attachments.length; i++)
            {
                var filesize = attachments[i]['size'];
                if(filesize > 20000000)
                {
                    var name = attachments[i]['name'];
                    swal('Whoa!', 'File: '+ name +' is too large. Files must be less than 20MB.', 'error');
                    return;
                }
                totalsize += filesize;
            }  

            if(totalsize > 20000000)     
            {
                swal('Whoa!', 'Total upload size must be less than 20MB. Please remove one or more files and try again.', 'error');
                return;
            }     
        }

        swal({
            title: "Submitting Your Ticket...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });

        var formData = new FormData($('#createticketform')[0]);

    	$.ajax(
        {
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'text',
            url:'createticket',
            type: 'POST',
            data: formData,
            success: function(resp)
            {
                if(resp == "success")
                {
                    swal('Success!', 'Your Ticket Has Been Submitted!', 'success'); 
                    clearTicketModal(); 
                    $('#newticketmodal').modal('hide'); 

                    var currentpage = location.href
                    if(currentpage.includes('userdash'))
                    {
                        location.reload();
                    }                 
                }
                else
                {
                    swal('Oh No!', resp, 'error');
                    $('#newticketmodal').modal('hide');
                }
            }
        });
    });

    $('#categoryselect').on('change', function()
    {
        $selecttech = $('#categoryselect option:selected').data('selecttech');

        if($selecttech == true)
        {
            $('#selecttechdiv').removeClass('nodisplay');
            $('#selecttechdiv').addClass('required');

            var queues = $('#categoryselect option:selected').data('queues');

            $.ajaxSetup(
            {
                headers: 
                {
                  'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax(
            {
                url:'gettechsforqueue',
                type: 'POST',
                data: {queues:queues},
                success: function(resp)
                { 
                    if($.isArray(resp))
                    {
                        var options = '<option value="0"></option>';

                        for(var i=0; i<resp.length; i++) 
                        {   
                            options += '<option value="'+ resp[i]['userid'] + '">'+ resp[i]['name'] +'</option>';                            
                        }

                        $('#techselect').html(options);
                        $('#techselect').selectpicker('refresh');
                    }
                    else if(resp == 'No Techs Available')
                    {
                        var options = '<option value="unassigned">There are no techs currently available</option>';
                        $('#techselect').html(options);
                        $('#techselect').selectpicker('refresh');
                    }                    
                }
            })
        }
        else
        {
            if(!$('#selecttechdiv').hasClass('nodisplay'))
            {
                $('#selecttechdiv').addClass('nodisplay');
                $('#selecttechdiv').removeClass('required');
            }
        }
    });


    // end New Ticket



    // Opting In and Out
    $('#optinbtn').on('click', function()
    { 
        $.ajax(
        {
            url:'opt',
            type: 'POST',
            data: $('#optform').serialize(),
            success: function(resp)
            {
                if(resp == "opted in")
                {
                    swal('Saddle Up!', 'Get Ready to Work! <br> You\'re Now Receiving Tickets!', 'success');  
                    $('#optstatus').removeClass('optedout');                       
                    $('#optstatus').addClass('optedin');
                    $('#optmessage').text('ACTIVE');                  

                }

                if(resp == 'opted out')
                {
                    swal('Take a Breather!', 'You\'re No Longer Receiving Tickets. <br> Take a Minute to Relax, You Deserve It!', 'success');
                    $('#optstatus').removeClass('optedin');                  
                    $('#optstatus').addClass('optedout'); 
                    $('#optmessage').text('INACTIVE'); 
                }
            }
        })
    });

    // User Settings

    $('#usersettingsbtn').on('click', function()
    {
        $('#usersettingsmodal').modal('show');
    });

    $('#submitupdateuserinfobtn').on('click', function(e)
    {
        var officephone = $('#updateuserprimaryphone').val();
        var ext = $('#updateuserprimaryphoneext').val();
        var mobile = $('#updateuserotherphone').val();
        var email = $('#updateuserprimaryemail').val();

        if(officephone.length < 14)
        {
            swal('Whoops!','Phone numbers must have ten digits (###) ###-####. <br> Please complete.', 'error');
            $('#updateuserprimaryphone').addClass('alert');
            $('#updateuserprimaryphone').focus();
            e.preventDefault();
            return;
        }

        if(officephone.length == 14)
        {
            $('#updateuserprimaryphone').removeClass('alert');
        } 

        if(officephone == '() -')
        {
            swal('Hey!', 'You must enter an Office Phone number.', 'error');
            e.preventDefault();
            return;
        }

        if(mobile.length < 14 && mobile !== '() -' && mobile !== '')
        {
            swal('Hey!','Phone numbers must have ten digits (###) ###-####. <br> Please complete.', 'error');
            e.preventDefault();
            return;
        }

        if(isNaN(ext))
        {
            swal('Hey!', 'Phone Extensions Must Be Only Numeric.', 'error');
            e.preventDefault();
            return;
        }

        if(!validateEmail(email))
        {
            swal('Hey!', 'Email Addresses Must Be in "user@domain.com" format.', 'error');
            e.preventDefault();
            return;
        }

        if(email.length < 1)
        {
            swal('Hey!', 'You must enter an Email address.', 'error');
            e.preventDefault();
            return;
        }

        $.ajax(
        {
            url:'updatemyinfo',
            type: 'POST',
            data: $('#updateuserinfofrm').serialize(),
            success: function(resp)
            {
                if(resp == "success")
                {
                    swal('Success!', 'Your Information Has Been Updated.', 'success');         
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })
    });


    $('#submitchangepassbtn').on('click', function(e)
    {
        var currentpass = $('#changepasswordcurrent').val();
        var newpass = $('#changepasswordnew').val();
        var confirmpass = $('#changepasswordnewconfirm').val().trim();

        if(currentpass.length < 1 || newpass.length < 1 || confirmpass.length < 1)
        {
            swal('Uh Oh!', 'All Fields are Required', 'error');   
            e.preventDefault();
            return;   
        }

        if(newpass !== confirmpass)
        {
            swal('Uh Oh!', 'Your New Passwords Do Not Match! <br> Please Correct and Try Again.', 'error');
            e.preventDefault();
            return;
        }

        $.ajax(
        {
            url:'changepass',
            type: 'POST',
            data: $('#changepassform').serialize(),
            success: function(resp)
            {
                if(resp == "success")
                {
                    swal('Success!', 'Your Password Has Been Updated.', 'success');         
                }
                else if(resp == "fail")
                {
                    swal('Oh No!', 'Invalid Current Password, Please Try Again', 'error');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })
    });


    $('#submitdashprefbtn').on('click', function()
    {
        $.ajax(
        {
            url:'prefdash',
            type: 'POST',
            data: $('#prefdashfrm').serialize(),
            success: function(resp)
            {
                if(resp == "success")
                {
                    swal('Success!', 'Your Dashboard Preference Has Been Updated!', 'success');         
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })
    });


    // End User Settings


}); // end document.ready


// Clear New Ticket Modal
function clearTicketModal()
{
    $('#categoryselect').val('default');
    $('#categoryselect').selectpicker('refresh');
    $('#createticketform')[0].reset();
    $('#charCount').text(500);
    $('#description').removeClass('alert');
}

// function for counting characters in textarea
function countChars(val) 
{
    var len = val.value.length;
    if (len >= 500) 
    {
      val.value = val.value.substring(0, 500);
    } 
    else 
    {
      $('#charCount').text(500 - len);
    }
}

function ClearForm(formid)
{
    $('#'+formid+' select').val('default');
    $('#'+formid+' select').selectpicker('refresh');
    $('#'+formid)[0].reset();
    $('#'+formid+' input.alert').removeClass('alert');
    $('#'+formid+' textarea.alert').removeClass('alert');
}

function isPosInt(input)
{
    var regex = /^[1-9]\d*$/;
    return regex.test(input);
}

function validateEmail(email) 
{
    var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return regex.test(email);
}

function onlyAlpha(input)  
{  
   var regex = /^[A-Za-z]+$/;  
   return regex.test(input);
}

var to = null;
function searchTable(tableid)
{    
    $(document).on('keyup', '.search', function() 
    {
        var elem = this;
        clearTimeout(to);
        to = setTimeout(function()
        {
         search(tableid, elem);   
        }, 500);
    });
}

function search(tableid, elem)
{
    var $rows = $('#'+tableid+' tbody tr.searchable')
    var val = '^(?=.*\\b' + $.trim($(elem).val()).split(/\s+/).join('\\b)(?=.*\\b') + ').*$',
    reg = RegExp(val, 'i'),
    text;

    $rows.show().filter(function() 
    {
        text = $(this).text().replace(/\s+/g, ' ');
        return !reg.test(text);
    }).hide();
}