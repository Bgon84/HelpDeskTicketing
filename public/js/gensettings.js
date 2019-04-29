$(document).ready(function()
{
	$('#smtpencryptionselect').selectpicker('refresh');
	$('#defaultgroupselect').selectpicker('refresh');
	

	$('#editsmtpbtn').on('click', function()
	{
		var encryption = $('#smtpencryptionlbl').text();

		$('.smtplabel').css('display', 'none');
		$('#editsmtpbtn').css('display', 'none');

		$('#smtpencryptionselect').selectpicker('val', encryption);

		$('.smtpsettings').css('display', 'inline');
		$('.smtpsettings').removeClass('nodisplay');
		$('#savesmtpbtn').css('display', 'inline');
		$('#savesmtpbtn').removeClass('nodisplay');

		$('#smtpserver').val($('#smtpserverlbl').text());		
		$('#smtpport').val($('#smtpportlbl').text());		     		
		$('#smtpencryption').val($('#smtpencryptionlbl').text());    			
		$('#smtpusername').val($('#smtpusernamelbl').text());		
		$('#smtpfrom').val($('#smtpfromlbl').text());

	});

	$('#savesmtpbtn').on('click', function(e)
	{		
		if($('#smtpserver').val().length < 1)
		{
			swal('Whoops!', 'You must provide a Server!', 'error');
			e.preventDefault();
			$('#smtpserver').addClass('alert');
			return;
		}

		if($('#smtpport').val().length < 1)
		{
			swal('Whoops!', 'You must provide a Port!', 'error');
			e.preventDefault();
			$('#smtpport').addClass('alert');
			return;
		}

		if($('#smtpfrom').val().length < 1)
		{
			swal('Whoops!', 'You must provide a From Address!', 'error');
			e.preventDefault();
			$('#smtpfrom').addClass('alert');
			return;
		}

		var port = $('#smtpport').val();
		var email = $('#smtpfrom').val();

		if(!isPosInt(port))
		{
			swal('Whoops!', 'Port must be a positive integer!', 'error');
			$('#smtpport').addClass('alert');
			return;
		}

		if(!validateEmail(email))
		{
			swal('Whoops!', 'From Email Address Must Be in "user@domain.com" Format.', 'error');
			$('#smtpfrom').addClass('alert');
			return;
		}


        $.ajax(
        {
            url:'editsmtp',
            type: 'POST',
            data: $('#smtpsettingsform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            { 
                if(resp[0] == "success")
                {
                    swal('Success!', 'Your Settings Have Been Saved!', 'success');

            		$('#smtpserverlbl').text(resp[1]['server']);         		
            		$('#smtpportlbl').text(resp[1]['port']);
            		$('#smtpencryptionlbl').text(resp[1]['encryption']);	
    		
            		if(resp[1]['username'].length > 0)
            		{
            			$('#smtpusernamelbl').text(resp[1]['username']);
            		}
            		else
            		{
            			$('#smtpusernamelbl').text('');
            		}
            		
            		$('#smtpfromlbl').text(resp[1]['fromaddress']);;

            		ClearForm('smtpsettingsform');

            		$('.smtpsettings').css('display', 'none');
					$('#savesmtpbtn').css('display', 'none');

					$('.smtplabel').css('display', 'inline');
					$('#editsmtpbtn').css('display', 'inline');

					location.reload();
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        })
	});

	$('#testsmtpbtn').on('click', function()
	{
		$('#smtptestmodal').modal('show');
	});

	$('#smtptestsendbtn').on('click', function(e)
	{
		var to = $('#smtptestemail').val();
		var from = $('#smtptestfrom').val();
		var msg = $('#smtptestmessage').val();

		if(!validateEmail(to))
		{
			swal('Whoops!', 'Email Address Must Be in "user@domain.com" Format.', 'error');
			e.preventDefault();
			return;
		}
    
	    swal({
	        title: "Testing...",
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
        });

		$.ajax(
        {
            url:'testsmtp',
            type: 'POST',
            data: {to:to, from:from, msg:msg},
            datatype: 'JSON',
            success: function(resp)
            { 
                if(resp == "success")
                {
                    swal('Success!', 'Test Email Successfully Sent!', 'success');
                    $('#smtptestmodal').modal('hide');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. <br>' + resp + '<br> Please Try Again.', 'error');
                }
            }
        })
	});

	$(document).on('click', '#defaultgroupsavebtn', function()
	{
		var groupid = $('#defaultgroupselect').val();

		 $.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            }
        });

		$.ajax(
        {
            url:'defaultgroup',
            type: 'POST',
            data: {groupid:groupid},
            datatype: 'JSON',
            success: function(resp)
            { 
                if(resp == "success")
                {
                    swal('Success!', 'Default Group Has Been Saved', 'success');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. <br>' + resp + '<br> Please Try Again.', 'error');
                }
            }
        })
	});

    $(document).on('click', '#techdashrefreshsavebtn', function()
    {
        var value = $('#techdashrefreshrate').val();

        if(!isPosInt(value))
        {
            swal('Hey!', 'Refresh rate value must be a positive integer.', 'error');
            return;
        }

        // convert to milliseconds for use in setTimeOut() in techdash.js
        value *= 1000;
		
		$.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            }
        });

		$.ajax(
        {
            url:'settechdashrefresh',
            type: 'POST',
            data: {value:value},
            datatype: 'JSON',
            success: function(resp)
            { 
                if(resp == "success")
                {
                    swal('Success!', 'Tech Dash Refresh Rate Has Been Saved', 'success');
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. <br>' + resp + '<br> Please Try Again.', 'error');
                }
            }
        })
    })

});  // end doc.ready