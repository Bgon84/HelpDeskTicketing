$(document).ready(function()
{
	// Retrieve table length cookies
	var techtickettbl_length = getTechTicketTableLength();
	var allopentickettable_length = getAllOpenTicketTableLength();
	var alltechtickettable_length = getAllTechTicketTableLength();
	var alltickettable_length = getAllTicketTableLength();

	// Initialize Data
	$('#techtickettbl').DataTable(
	{
        "paging": true,
        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
        "pageLength": parseInt(techtickettbl_length),
        "order": [5, 'desc']
    });

	$('#alltickettable').DataTable(
	{
        "paging": true,
        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
        "pageLength": parseInt(alltickettable_length),
        "order": [5, 'desc']
    });

	$('#alltechtickettable').DataTable(
	{
        "paging": true,
        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
        "pageLength": parseInt(alltechtickettable_length),
        "order": [5, 'desc']
    });

	$('#allopentickettable').DataTable(
	{
        "paging": true,
        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
        "pageLength": parseInt(allopentickettable_length),
        "order": [5, 'desc']
    });

    $('#alltickettable_info').addClass('nodisplay');
    $('#alltickettable_paginate').addClass('nodisplay');
    $('#alltickettable_length').addClass('nodisplay');
    $('#alltickettable_filter').addClass('nodisplay');

    $('#alltechtickettable_info').addClass('nodisplay');
    $('#alltechtickettable_paginate').addClass('nodisplay');
    $('#alltechtickettable_length').addClass('nodisplay');
    $('#alltechtickettable_filter').addClass('nodisplay');

    $('#allopentickettable_info').addClass('nodisplay');
    $('#allopentickettable_paginate').addClass('nodisplay');
    $('#allopentickettable_length').addClass('nodisplay');
    $('#allopentickettable_filter').addClass('nodisplay');

	// Set table length cookies
	$(document).on('change', '#techtickettbl_length', function()
	{	
		var length = $('#techtickettbl_length option:selected').val();
		cookies.set('techtickettbl_length', length);
	});

	$(document).on('change', '#allopentickettable_length', function()
	{	
		var length = $('#allopentickettable_length option:selected').val();
		cookies.set('allopentickettable_length', length);
	});

	$(document).on('change', '#alltechtickettable_length', function()
	{	
		var length = $('#alltechtickettable_length option:selected').val();
		cookies.set('alltechtickettable_length', length);
	});

	$(document).on('change', '#alltickettable_length', function()
	{	
		var length = $('#alltickettable_length option:selected').val();
		cookies.set('alltickettable_length', length);
	});

	
	var viewclosedtix = cookies.get('viewclosedtix');
	var viewalltix = cookies.get('viewalltix');

	if(viewclosedtix == 'true')
	{
		$('#viewclosedtixbtn').prop('checked', true);
	}
	else
	{
		$('#viewclosedtixbtn').prop('checked', false);		
	}

	if(viewalltix == 'true')
	{
		$('#viewalltixbtn').prop('checked', true);
	}
	else
	{
		$('#viewalltixbtn').prop('checked', false);		
	}

	if((viewclosedtix == 'false' && viewalltix == 'false') || (viewclosedtix == null && viewalltix == null) || (viewclosedtix == null && viewalltix == 'false') || (viewclosedtix == 'false' && viewalltix == null))
	{
		showTechTicketTable();
	}

	if($('#viewclosedtixbtn').is(':checked'))
	{	
		if(!$('#viewalltixbtn').is(':checked'))
		{
			// view closed checked, view all not checked, show alltechtickettable
			showAllTechTicketTable();
		}
		else if($('#viewalltixbtn').is(':checked'))
		{
			// view all and view closed are both checked, show alltickettable 
			showAllTicketTable();
		}

		$('#closedtixwords').text('Hide Closed Tickets');
	}

	if($('#viewalltixbtn').is(':checked'))
	{
		if(!$('#viewclosedtixbtn').is(':checked'))
		{
			// view all is checked view closed is not checked, show allopentickettable
			showAllOpenTicketTable();
		}
		else if($('#viewclosedtixbtn').is(':checked'))
		{
			// view all and view closed are both checked, show alltickettable 
			showAllTicketTable();
		}

		$('#alltixwords').text('View Your Tickets');
	}

	$(document).on('click', '#viewclosedtixbtn', function()
	{
		if($('#viewclosedtixbtn').is(':checked') && !$('#viewalltixbtn').is(':checked'))
		{
			// view closed checked, view all not checked, show alltechtickettable
			showAllTechTicketTable();
		}
		else if(!$('#viewclosedtixbtn').is(':checked') && $('#viewalltixbtn').is(':checked'))
		{
			// view all is checked view closed is not checked, show allopentickettable
			showAllOpenTicketTable();
		}
		else if($('#viewclosedtixbtn').is(':checked') && $('#viewalltixbtn').is(':checked'))
		{
			// view all and view closed are both checked, show alltickettable 
			showAllTicketTable();
		}
		else if(!$('#viewclosedtixbtn').is(':checked') && !$('#viewalltixbtn').is(':checked'))
		{
			// view all and view closed are not checked, show techtickettbl
			showTechTicketTable();
		}

		viewclosedtix = cookies.get('viewclosedtix');

		if(viewclosedtix == 'true')
		{
			$('#closedtixwords').text('View Closed Tickets');
			cookies.set('viewclosedtix', 'false');
		}
		else
		{
			$('#closedtixwords').text('Hide Closed Tickets');
			cookies.set('viewclosedtix', 'true');
		}
	});

	$(document).on('click', '#viewalltixbtn', function()
	{
		if($('#viewclosedtixbtn').is(':checked') && !$('#viewalltixbtn').is(':checked'))
		{
			// view closed checked, view all not checked, show alltechtickettable
			showAllTechTicketTable();
		}
		else if(!$('#viewclosedtixbtn').is(':checked') && $('#viewalltixbtn').is(':checked'))
		{
			// view all is checked view closed is not checked, show allopentickettable
			showAllOpenTicketTable();
		}
		else if($('#viewclosedtixbtn').is(':checked') && $('#viewalltixbtn').is(':checked'))
		{
			// view all and view closed are both checked, show alltickettable 
			showAllTicketTable();
		}		
		else if(!$('#viewclosedtixbtn').is(':checked') && !$('#viewalltixbtn').is(':checked'))
		{
			// view all and view closed are not checked, show techtickettbl
			showTechTicketTable();
		}

		viewalltix = cookies.get('viewalltix');

		if(viewalltix == 'true')
		{
			$('#alltixwords').text('View All Tickets');
			cookies.set('viewalltix', 'false');
		}
		else
		{
			$('#alltixwords').text('View Your Tickets');
			cookies.set('viewalltix', 'true');
		}
	});


	// New Ticket Check
	var interval = 10000;

	setInterval(function()
	{
		var date = new Date();
		var userid = $('#userid').val(); 
		var lastupdate = date.getTime() - interval;

        $.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            }
        });
		
        $.ajax(
        {
            url: 'newtechtixcheck',
            type: 'POST',
            data: {userid:userid, lastupdate:lastupdate},
            datatype: 'JSON',
            success: function(resp)
            { 
            	if(resp[0].length > 0)
            	{ 
            		for(i=0;  i<resp[0].length; i++)
            		{
	            		var createddate = new moment(resp[0][i]['created_at']);
	            		createddate = createddate.format('MM-DD-YYYY HH:mm:ss');

	            		var updateddate = new moment(resp[0][i]['updated_at']);
	            		updateddate = updateddate.format('MM-DD-YYYY HH:mm:ss');

		            	newrow = '<tr class="queueitem searchable" id="row-'+ resp[0][i]['ticketid'] +'"'; 
		            	newrow += 'ondblclick="window.open(' + "'getticketinfo/"+ resp[0][i]['ticketid'] +"', '_blank')";
		            	newrow += '">';
		            	newrow += '<td>'+ resp[0][i]['ticketid'] +'</td>';
		            	newrow += '<td><span class="label label-success">'+ resp[0][i]['status'] +'</span></td>';
		            	
		            	if(resp[0][i]['tech'] !== null)
		            	{
		            		newrow += '<td>'+ resp[0][i]['tech'] +'</td>';
		            	}
		            	else
		            	{
		            		newrow += '<td><label class="red">Unassigned</label></td>';
		            	}
		            	
		            	newrow += '<td>'+ resp[0][i]['queuename'] +'</td>';
		            	newrow += '<td>'+ resp[0][i]['category'] +'</td>';
		            	newrow += '<td>'+ resp[0][i]['priority'] +'</td>';
		            	newrow += '<td><label id="ticketrequestor">'+ resp[0][i]['requestor'] +'</label></td>'; 		            	
		            	newrow += '<td class="desc"><div class="desc" title="'+ resp[0][i]['description'] +'">'+ resp[0][i]['description'] + '</div></td>';
		            	newrow += '<td>'+ createddate +'</td>';
		            	newrow += '<td>'+ updateddate +'</td>';
		            	newrow += '<td>No</td></tr>'

		            	if($('#notixrow').length)
		            	{
		            		$('#notixrow').addClass('nodisplay');
		            	}

		            	$('#techstbody').prepend(newrow);	
		            	$('#allopentixtbody').prepend(newrow);	
		            	$('#alltechtixtbody').prepend(newrow);	
		            	$('#alltixtbody').prepend(newrow);	
            		}
            	}
            }
        })
	}, interval);
	// End New Ticket Check

	$('.techcell').each(function()
	{
		if($(this).html().trim().length < 1)
		{
			$(this).html('<label class="red">Unassigned</label>');
		}
	})

}); // end doc.ready


function showAllTechTicketTable()
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
        url: 'getalltechtix',
        type: 'GET',
        success: function(resp)
        { 
        	if(resp.length > 0)
        	{ 
        		alltechtickettable_length = getAllTechTicketTableLength();
        		$('#alltechtickettable').DataTable().destroy();

				// view closed checked, view all not checked show alltechtickettable
				$('#techtickettbl').addClass('nodisplay');
				$('#allopentickettable').addClass('nodisplay');
				$('#alltickettable').addClass('nodisplay');				

			    $('#techtickettbl_info').addClass('nodisplay');
			    $('#techtickettbl_paginate').addClass('nodisplay');
			    $('#techtickettbl_length').addClass('nodisplay');
			    $('#techtickettbl_filter').addClass('nodisplay');

			    $('#allopentickettable_info').addClass('nodisplay');
				$('#allopentickettable_paginate').addClass('nodisplay');
				$('#allopentickettable_length').addClass('nodisplay');
				$('#allopentickettable_filter').addClass('nodisplay');

			    $('#alltickettable_info').addClass('nodisplay');
			    $('#alltickettable_paginate').addClass('nodisplay');
			    $('#alltickettable_length').addClass('nodisplay');
			    $('#alltickettable_filter').addClass('nodisplay');

			    $('#alltechtickettable').removeClass('nodisplay');
			    $('#alltechtickettable_info').removeClass('nodisplay');
			    $('#alltechtickettable_paginate').removeClass('nodisplay');
			    $('#alltechtickettable_length').removeClass('nodisplay');
			    $('#alltechtickettable_filter').removeClass('nodisplay');

        		$('#alltechtixtbody').html(resp);
    			$('#alltechtickettable').DataTable(
				{
			        "paging": true,
			        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],		
			        "pageLength": parseInt(alltechtickettable_length),	        
			        "order": [5, 'desc']
			    });

			    
        	}
        }
    })
}

function showAllTicketTable()
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
        url: 'getalltix',
        type: 'GET',
        success: function(resp)
        { 
        	if(resp.length > 0)
        	{ 
        		alltickettable_length = getAllTicketTableLength(); console.log(alltickettable_length);
        		$('#alltickettable').DataTable().destroy();

    			// view closed and view all are both checked show alltickettable
				$('#techtickettbl').addClass('nodisplay');
				$('#allopentickettable').addClass('nodisplay');
				$('#alltechtickettable').addClass('nodisplay');

			    $('#alltechtickettable_info').addClass('nodisplay');
			    $('#alltechtickettable_paginate').addClass('nodisplay');
			    $('#alltechtickettable_length').addClass('nodisplay');
			    $('#alltechtickettable_filter').addClass('nodisplay');				    

			    $('#techtickettbl_info').addClass('nodisplay');
			    $('#techtickettbl_paginate').addClass('nodisplay');
			    $('#techtickettbl_length').addClass('nodisplay');
			    $('#techtickettbl_filter').addClass('nodisplay');

			    $('#allopentickettable_info').addClass('nodisplay');
				$('#allopentickettable_paginate').addClass('nodisplay');
				$('#allopentickettable_length').addClass('nodisplay');
				$('#allopentickettable_filter').addClass('nodisplay');

				$('#alltickettable').removeClass('nodisplay');
				$('#alltickettable_info').removeClass('nodisplay');
			    $('#alltickettable_paginate').removeClass('nodisplay');
			    $('#alltickettable_length').removeClass('nodisplay');
			    $('#alltickettable_filter').removeClass('nodisplay');

        		$('#alltixtbody').html(resp);
    			$('#alltickettable').DataTable(
				{
			        "paging": true,
			        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
			        "pageLength": parseInt(alltickettable_length),
			        "order": [5, 'desc']
			    });			    
        	}
        }
    })
}

function showAllOpenTicketTable()
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
        url: 'getallopentix',
        type: 'GET',
        success: function(resp)
        { 
        	if(resp.length > 0)
        	{ 
        		allopentickettable_length = getAllOpenTicketTableLength();
        		$('#allopentickettable').DataTable().destroy();

    			// view all is checked view closed is not checked, show allopentickettable
				$('#techtickettbl').addClass('nodisplay');
				$('#alltechtickettable').addClass('nodisplay');
				$('#alltickettable').addClass('nodisplay');							
				
			    $('#alltickettable_info').addClass('nodisplay');
			    $('#alltickettable_paginate').addClass('nodisplay');
			    $('#alltickettable_length').addClass('nodisplay');
			    $('#alltickettable_filter').addClass('nodisplay');

			    $('#techtickettbl_info').addClass('nodisplay');
			    $('#techtickettbl_paginate').addClass('nodisplay');
			    $('#techtickettbl_length').addClass('nodisplay');
			    $('#techtickettbl_filter').addClass('nodisplay');

			    $('#alltechtickettable_info').addClass('nodisplay');
			    $('#alltechtickettable_paginate').addClass('nodisplay');
			    $('#alltechtickettable_length').addClass('nodisplay');
			    $('#alltechtickettable_filter').addClass('nodisplay');

			    $('#allopentickettable').removeClass('nodisplay');
				$('#allopentickettable_info').removeClass('nodisplay');
				$('#allopentickettable_paginate').removeClass('nodisplay');
				$('#allopentickettable_length').removeClass('nodisplay');
				$('#allopentickettable_filter').removeClass('nodisplay');	

        		$('#allopentixtbody').html(resp);
    			$('#allopentickettable').DataTable(
				{
			        "paging": true,
			        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
			        "pageLength": parseInt(allopentickettable_length),
			        "order": [5, 'desc']
			    });

			    
        	}
        }
    })
}

function showTechTicketTable()
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
        url: 'getallopentechtix',
        type: 'GET',
        success: function(resp)
        { 
        	if(resp.length > 0)
        	{ 
        		techtickettbl_length = getTechTicketTableLength();
        		$('#techtickettbl').DataTable().destroy();

    			// view all and view closed are not checked, show techtickettbl
				$('#allopentickettable').addClass('nodisplay');
				$('#alltechtickettable').addClass('nodisplay');
				$('#alltickettable').addClass('nodisplay');					

				$('#allopentickettable_info').addClass('nodisplay');
				$('#allopentickettable_paginate').addClass('nodisplay');
				$('#allopentickettable_length').addClass('nodisplay');
				$('#allopentickettable_filter').addClass('nodisplay');		
				
			    $('#alltickettable_info').addClass('nodisplay');
			    $('#alltickettable_paginate').addClass('nodisplay');
			    $('#alltickettable_length').addClass('nodisplay');
			    $('#alltickettable_filter').addClass('nodisplay');    

			    $('#alltechtickettable_info').addClass('nodisplay');
			    $('#alltechtickettable_paginate').addClass('nodisplay');
			    $('#alltechtickettable_length').addClass('nodisplay');
			    $('#alltechtickettable_filter').addClass('nodisplay');

			    $('#techtickettbl').removeClass('nodisplay');
				$('#techtickettbl_info').removeClass('nodisplay');
			    $('#techtickettbl_paginate').removeClass('nodisplay');
			    $('#techtickettbl_length').removeClass('nodisplay');
			    $('#techtickettbl_filter').removeClass('nodisplay');	

        		$('#techstbody').html(resp);
    			$('#techtickettbl').DataTable(
				{
			        "paging": true,
			        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
			        "pageLength": parseInt(techtickettbl_length),
			        "order": [5, 'desc']
			    });

			    
        	}
        }
    })
}

function getTechTicketTableLength()
{
    var techtickettbl_length = cookies.get('techtickettbl_length');

	if(techtickettbl_length == null)
	{
		techtickettbl_length = 25;
	}

	return techtickettbl_length;
}

function getAllOpenTicketTableLength()
{
	var allopentickettable_length = cookies.get('allopentickettable_length');

	if(allopentickettable_length == null)
	{
		allopentickettable_length = 25;
	}

	return allopentickettable_length;
}

function getAllTechTicketTableLength()
{
	var alltechtickettable_length = cookies.get('alltechtickettable_length');

	if(alltechtickettable_length == null)
	{
		alltechtickettable_length = 25;
	}

	return alltechtickettable_length;
}

function getAllTicketTableLength()
{
	var alltickettable_length = cookies.get('alltickettable_length');

	if(alltickettable_length == null)
	{
		alltickettable_length = 25;
	}

	return alltickettable_length;
}

