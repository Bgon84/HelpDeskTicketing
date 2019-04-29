$(document).ready(function(){
	
    // var conn = new WebSocket('ws://localhost:8090');
    // conn.onopen = function(e) {
    //     console.log("Connection established!");
    // };

    // conn.onmessage = function(e) {
    //     console.log(e.data);
    //     $('#chatwindow').append('\n' + e.data);
    // };

    // $(document).on('click', '#sendchatbtn', function()
    // {
    //     var msg = $('#chatmessage').val();
    //     conn.send(msg);        
    //     $('#chatwindow').append('\n' + msg);
    //     $('#chatmessage').val('');
    // });


	$('#updateticketpriority').selectpicker('refresh');
	$('#updateticketcategory').selectpicker('refresh');
	$('#updateticketstatus').selectpicker('refresh');
	$('#updatetickettech').selectpicker('refresh');


    // View/Hide Attachments
    $('#viewatt').on('click', function()
    {
        $('#attachmentcontainer').css('display', 'block');
        $('#viewatt').css('display', 'none');
        $('#hideatt').css('display', 'block');
    });

    $('#hideatt').on('click', function()
    {
        $('#attachmentcontainer').css('display', 'none');
        $('#viewatt').css('display', 'block');
        $('#hideatt').css('display', 'none');
    });

    $(document).on('change', '#updateticketstatus', function()
    {
        if($('#updateticketstatus option:selected').val() == 3) // status id 3 - Resolved
        {
            swal({
              title: 'Has the issue been resolved?',
              text: 'Please ensure that the issue presented in this ticket has been resolved and that the requestor is satisfied with the resolution before you save this update.',
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'I understand'              
            }).then(function(result) 
            { 
                if(result) 
                {
                    $('#updateticketpriority').prop('disabled', true);        
                    $('#updateticketcategory').prop('disabled', true);        
                    $('#updatetickettech').prop('disabled', true);   
                    $('#updateticketpriority').selectpicker('refresh');        
                    $('#updateticketcategory').selectpicker('refresh');        
                    $('#updatetickettech').selectpicker('refresh');  

                    $('#ticketupdatemessage').prop('readonly', true);   
                    $('#ticketupdateinternalnotes').prop('readonly', true);   
                    $('#ticketupdateattachment').prop('disabled', true);   

                }
            });                 
        }
        else
        {
            $('#updateticketpriority').prop('disabled', false);        
            $('#updateticketcategory').prop('disabled', false);        
            $('#updatetickettech').prop('disabled', false);   
            $('#updateticketpriority').selectpicker('refresh');        
            $('#updateticketcategory').selectpicker('refresh');        
            $('#updatetickettech').selectpicker('refresh');  

            $('#ticketupdatemessage').prop('readonly', false);   
            $('#ticketupdateinternalnotes').prop('readonly', false);   
            $('#ticketupdateattachment').prop('disabled', false);  
        }
    })


    // Update Ticket
	$('#submitticketupdates').on('click', function(e)
	{
        if($('#updateticketstatus option:selected').val() == 3) // status id 3 - Resolved
        {
            $('#ticketupdatetbl').css('display', 'none');
            $('#escalateticketbtn').css('display', 'none');
            $('#freezeticketbtn').css('display', 'none');
            $('#thawticketbtn').css('display', 'none');
            $('#mergeticketbtn').css('display', 'none');
            $('#resolvedmsg').css('display', 'block');
        }

        $('#editticketform').find(':disabled').prop('disabled', false);

        var attachments = $('#ticketupdateattachment').prop('files');

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
            title: "Submitting Your Updates...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });

        var formData = new FormData($('#editticketform')[0]);

        $.ajax(
        {
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'text',
            url:'editticket',
            type: 'POST',
            data: formData,
            success: function(resp)
            { 
                resp = JSON.parse(resp); console.log(resp);
                if(resp[0]['status'] == 'success')
                {
                    swal('Success!', 'Your Ticket Has Been Updated!', 'success');

                    $('#editticketform')[0].reset();      

                    $('#ticketpriority').html(resp[1]['priority']);
                    $('#ticketstatus').html(resp[1]['status']);
                    $('#ticketcategory').html(resp[1]['category']);                    
                    $('#ticketdateupdated').html(resp[2]['updated_at']);   

                    if(resp[1]['techs'] !== null)
                    {
                        $('#tickettechs').text('');    
                        $('#tickettechs').text(resp[1]['techs']); 
                    }                 

                    if(resp[1]['internalnote'] !== undefined) 
                    {                
                        $('#internalnotestbl').append('<tr><td>' + resp[1]['internalnote'] + '</td><td>' + resp[2]['updated_at'] + '</td><td>' + resp[1]['changedby'] + '</td></tr>'); 
                    }

                    if(resp[1]['publicnote'] !== undefined) 
                    {   
                        $('#publicnotestbl').append('<tr><td>' + resp[1]['publicnote'] + '</td><td>' + resp[2]['updated_at'] + '</td><td>' + resp[1]['changedby'] + '</td></tr>');                       
                    }

                    if(resp[3] !== undefined)
                    {
                        for(i=0; i<resp[3].length; i++)
                        {
                            if(resp[3][i]['updatetypeid'] !== 5 && resp[3][i]['updatetypeid'] !== 6)
                            {
                                $('#ticketupdatestbl').append('<tr><td>' + resp[3][i]['content'] + '</td><td>' + resp[2]['updated_at'] + '</td><td>' + resp[1]['changedby'] + '</td></tr>');  
                            }
                            
                        }
                             
                    }
                }
                else if(resp == "nochange")
                {
                    swal('What The...?', 'You didn\'t change anything! Please make some updates and try again.', 'warning')
                }
                else
                {
                    swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                }
            }
        });
        
	});


    // Change Priority when Category is changed
    $('#updateticketcategory').on('change', function()
    {
        var selected = $(this).find('option:selected');
        var priorityid = selected.data('priority');
        
        $('#updateticketpriority').selectpicker('val', priorityid);
        $('#updateticketpriority').selectpicker('refresh');
    });

    // Void Ticket
    $(document).on('click', '#voidticketbtn', function()
    {
        swal({
              title: 'Are You Sure?',
              text: "Do you really want to Void this Ticket?",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Void Ticket'
            }).then(function(result) 
            { 
                if(result) 
                { 
                    swal({
                        title: "Submitting Your Updates...",
                        text: "Please be patient!",
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        imageUrl: "/img/loadgear.gif"
                    });

                    var ticketid = $('#voidticketbtn').data('ticketid');
                   
                    $.ajaxSetup(
                    {
                        headers: 
                        {
                          'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                        }
                    });


                    $.ajax(
                    {
                        url:'voidticket',
                        type: 'POST',
                        data: {ticketid:ticketid},
                        datatype: 'JSON',
                        success: function(resp)
                        {
                            if(resp == "success")
                            {
                                swal('Success!', 'This Ticket Has Been Voided!', 'success'); 

                                $('#ticketupdatetbl').css('display', 'none');
                                $('#voidmsg').css('display', 'block');
                                $('div.actionbtns').css('display', 'none');

                            }
                            else
                            {
                                swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                            }
                        }
                    })
                }
            })
    })


    //Escalate Ticket
    $(document).on('click', '#escalateticketbtn', function()
    {
        $('#escalationModal').modal('show');
    });

    
    $(document).on('click', '#submitEscalationBtn', function()
    {
       
        if($('#escalationApplication').val() == '' || $('#escalationArea').val() == '' || $('#escalationFields').val() == '' || $('#escalationProblem').val() == '' || $('#escalationSteps').val() == '' || $('#escalationActions').val() == '')
        {
            swal('Wait a Second!', 'All fields must be filled in!', 'error');
            return;
        }

        swal({
              title: 'Are You Sure?',
              text: "Do you really want to Escalate this Ticket?",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Escalate Ticket'
            }).then(function(result) 
            { 
                if(result) 
                { 
                    swal({
                        title: "Submitting Your Updates...",
                        text: "Please be patient!",
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        imageUrl: "/img/loadgear.gif"
                    });

                    // ticket info
                    var ticketid = $('#escalateticketbtn').data('ticketid');
                    var currentstatus = $('#ticketstatus').text();
                    var currentqueue = $('#queuename').val();
                    var elevationqueueid = $('#elevationqueueid').val();
                   
                    //Escalation Questionnaire fields
                    var application = $('#escalationApplication').val(); 
                    var area = $('#escalationArea').val(); 
                    var fields = $('#escalationFields').val(); 
                    var problem = $('#escalationProblem').val();
                    var steps = $('#escalationSteps').val();
                    var actions = $('#escalationActions').val();

                    $('#escalationModal').modal('hide');

                    $.ajaxSetup(
                    {
                        headers: 
                        {
                          'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax(
                    {
                        url:'escalateticket',
                        type: 'POST',
                        data: {ticketid:ticketid, currentstatus:currentstatus, currentqueue:currentqueue, elevationqueueid:elevationqueueid, application:application, area:area, fields:fields, problem:problem, steps:steps, actions:actions},
                        datatype: 'JSON',
                        success: function(resp)
                        {
                            if(resp == "success")
                            {
                                swal('Success!', 'This Ticket Has Been Escalated!', 'success');

                                setTimeout(function()
                                    {
                                        location.reload();
                                    }, 1000)
                            }
                            else
                            {
                                swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                            }
                        }
                    })
                }
            })
    })

    // Freeze Ticket
    $(document).on('click', '#freezeticketbtn', function()
    {
        swal({
              title: 'Are You Sure?',
              text: "Do you really want to Freeze this Ticket?",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Freeze Ticket'
            }).then(function(result) 
            { 
                if(result) 
                { 
                    swal({
                        title: "Submitting Your Updates...",
                        text: "Please be patient!",
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        imageUrl: "/img/loadgear.gif"
                    });

                    var ticketid = $('#freezeticketbtn').data('ticketid');
                    var currentstatus = $('#ticketstatus').text();
                   
                    $.ajaxSetup(
                    {
                        headers: 
                        {
                          'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax(
                    {
                        url:'freezeticket',
                        type: 'POST',
                        data: {ticketid:ticketid, currentstatus:currentstatus},
                        datatype: 'JSON',
                        success: function(resp)
                        {
                            if(resp == "success")
                            {
                                swal('Brrrrrrrrr!', 'This Ticket Has Been Frozen!', 'success');

                                $('#freezeticketbtn').css('display', 'none');
                                $('#thawticketbtn').css('display', 'inline-block');
                                $('#frozenmsg').css('display', 'block');               
                                $('#escalateticketbtn').css('display', 'none');
                                $('#freezeticketbtn').css('display', 'none');
                                $('#voidticketbtn').css('display', 'none');
                                $('#mergeticketbtn').css('display', 'none');   

                                $('select.ticketinfoselect').prop('disabled', 'true');
                                $('select.ticketinfoselect').selectpicker('refresh');

                            }
                            else
                            {
                                swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                            }
                        }
                    })
                }
            })  
    })

    // Thaw Ticket
    $(document).on('click', '#thawticketbtn', function()
    {
        swal({
              title: 'Are You Sure?',
              text: "Do you really want to Thaw this Ticket?",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Thaw Ticket'
            }).then(function(result) 
            { 
                if(result) 
                { 
                    swal({
                        title: "Submitting Your Updates...",
                        text: "Please be patient!",
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        imageUrl: "/img/loadgear.gif"
                    });

                    var ticketid = $('#thawticketbtn').data('ticketid');
                   
                    $.ajaxSetup(
                    {
                        headers: 
                        {
                          'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax(
                    {
                        url:'thawticket',
                        type: 'POST',
                        data: {ticketid:ticketid},
                        datatype: 'JSON',
                        success: function(resp)
                        {
                            if(resp == "success")
                            {
                                swal('Success!', 'This Ticket Has Been Thawed!', 'success');

                                setTimeout(function()
                                {
                                    location.reload();
                                }, 1000)
                            }
                            else
                            {
                                swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                            }
                        }
                    })
                }
            })  
    })

    // Merge Ticket
    $(document).on('click', '#mergeticketbtn', function()
    {
        var opentixsameq = JSON.parse($('#opentixsameq').val());

        if(opentixsameq.length > 0)
        {
            var mergeselect = "<select id='mergeselect' name='mergeselect[]' class='form-control' data-actions-box='true' data-live-search='true' multiple>";

            for(i=0; i<opentixsameq.length; i++)
            {
                mergeselect += "<option value='" + opentixsameq[i]['ticketid'] + "'> Ticket # " + opentixsameq[i]['ticketid'] + " - " + opentixsameq[i]['description'] + "</option>";
            }

            mergeselect += "</select>";
        }
        else
        {
            var mergeselect = "There are no other open tickets from this queue to merge with."
        }


        swal({
            title: 'Merge Ticket?',
            type: 'warning',
            html: 'Please Select Tickets to Merge With: <br> <small>Hold CTRL while selecting multiple tickets</small> <br>' + mergeselect,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Merge'
        }).then(function(result) 
        { 
            if(result) 
            { 
                var ticketid = $('#mergeticketbtn').data('ticketid');
                var mergewithids = $('#mergeselect').val();

                swal({
                    title: "Submitting Your Updates...",
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
                      'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax(
                {
                    url:'mergetickets',
                    type: 'POST',
                    data: {ticketid:ticketid, mergewithids:mergewithids},
                    datatype: 'JSON',
                    success: function(resp)
                    { 
                        if(resp [0] == "success")
                        {
                            var masterticketid = resp[1];
                            swal('Success!', 'This Ticket Has Been Merged! A Master ticket has been created <a href=/getticketinfo/' + masterticketid + ' target="_blank">#' + masterticketid + '</a>', 'success');
                            $('#childmsg').css('display', 'inline-block');
                            $('#childmsg').append(' <a href=/getticketinfo/' + masterticketid + ' target="_blank">' + masterticketid + '</a>');
                            $('#ticketupdatetbl').css('display', 'none');
                            $('div.actionbtns').css('display', 'none');
                        }
                        else
                        {
                            swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                        }
                    }
                })
            }
        })  
    })

// reopen ticket

$(document).on('click', '.reopenbtn', function()
{     
    var button = this.id;
    var ticketid = button.split('-')[1];
    
    swal({
        title: 'Reopen Ticket?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Reopen'
    }).then(function(result) 
    {
        if(result)
        {
            $.ajaxSetup(
            {
                headers: 
                {
                  'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax(
            {
                url:'reopenticket',
                type: 'POST',
                data: {ticketid:ticketid},
                datatype: 'JSON',
                success: function(resp)
                { 
                    if(resp == "success")
                    {
                        swal('Success!', 'This ticket has been reopened.', 'success');

                        setTimeout(function()
                        {
                            location.reload();
                        }, 500)
                    }
                    else
                    {
                        swal('Oh No!', 'Something Went Wrong. Please Try Again.', 'error');
                    }
                }
            })
        }
    })  
})



}); // end doc.ready