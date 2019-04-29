$(document).ready(function()
{
 	$('.selectpicker').selectpicker('refresh');

 	// Tech Performance Report
 	$(document).on('click', '#submitTPR', function()
 	{
 		if($('#TPRuserselect').val().length == 0)
 		{
 			swal('Hey!', 'Please Select Tech(s) to Include in This Report.', 'error').then(function()
                {
                    $('#TPRuserselect').selectpicker('toggle');
                });
            return;
	    } 

        swal({
            title: "Compiling Your Report...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });

        var start = $('#TPRstartdate').val();
        var end = $('#TPRenddate').val();
        var msg = "Tech Performance Report For " + start + ' to ' + end;

        $.ajax(
        {
            url: 'tprreport',
            type: 'POST',
            data: $('#TPRform').serialize(),
            success: function(resp)
            {           
                if(resp.length > 1)
                {
                	swal('Success!', 'Your Report is Ready!', 'success');

                	var reportrows = totalsrow = '';
                	for(var i=0; i<resp.length-1; i++)
                	{
                		reportrows += '<tr>';
                		reportrows += '<td>' + resp[i]['techname'] + '</td>';
                		reportrows += '<td>' + resp[i]['ticketsassigned'] + '</td>';
                		reportrows += '<td>' + resp[i]['ticketsclosed'] + '</td>';
                		reportrows += '<td>' + resp[i]['ticketsescalated'] + '</td>';
                		reportrows += '<td>' + resp[i]['percentcovered'] + '</td>';
                		reportrows += '<td>' + resp[i]['avgclosetime'] + '</td>';
                		reportrows += '<td>' + resp[i]['timeoptedin'] + '</td>';
                		reportrows += '</tr>';
                	}

                	var j = resp.length-1;
                	totalsrow += '<b>TOTALS:</b>';                	
                	totalsrow += ' <u><b>Tickets:</b></u> ' + resp[j]['totaltickets'];
                	totalsrow += ', <u><b>Assigned:</b></u> ' + resp[j]['totalassigned'];
                	totalsrow += ', <u><b>Closed:</b></u> ' + resp[j]['totalclosed'];
                	totalsrow += ', <u><b>Escalated:</b></u> ' + resp[j]['totalescalated'];
                	totalsrow += ', <u><b>% Covered:</b></u> ' + resp[j]['totalpercentcovered'];
                	totalsrow += ', <u><b>Avg Close Time:</b></u> ' + resp[j]['totalavgclosetime'];
                	totalsrow += ', <u><b>Opt In Time:</b></u> ' + resp[j]['totaltimeoptedin'];       	

                	$('#TPRtbody').append(reportrows);
                	$('#TPRtotals').html(totalsrow);
                	$('#TPRheading').text(msg);
                	$('#TPRdiv').toggleClass('nodisplay');
                	$('#TPRtablediv').toggleClass('nodisplay');

                    $('#TPRtable').DataTable(
                    {
                        "paging": true,
                        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ]
                    });

                }               
                else
                {
                    swal('Nope.', 'There is no data for your requested time range. Please adjust and try again.', 'error');
                }              
            }
        }); 
 	});


 	// Category Closure Time Report
 	$(document).on('click', '#submitCCTR', function()
 	{
        swal({
            title: "Compiling Your Report...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });
        
        var start = $('#CCTRstartdate').val();
        var end = $('#CCTRenddate').val();
        var msg = "Category Closure Time Report for Tickets Closed Between " + start + ' and ' + end;

        $.ajax(
        {
            url: 'catclosure',
            type: 'POST',
            data: $('#CCTRform').serialize(),
            success: function(resp)
            {
            	
                if(resp.length > 0)
                {
                	swal('Success!', 'Your Report is Ready!', 'success');

                	var reportrows = '';
                	for(var i=0; i<resp.length; i++)
                	{
                		reportrows += '<tr>';
                		reportrows += '<td>' + resp[i]['category'] + '</td>';
                		reportrows += '<td>' + resp[i]['totaltickets'] + '</td>';
                		reportrows += '<td>' + resp[i]['avgtimetoclose'] + '</td>';
                		reportrows += '</tr>';
                	}

                	$('#CCTRtbody').append(reportrows);
                	$('#CCTRheading').text(msg);
                	$('#CCTRdiv').toggleClass('nodisplay');
                	$('#CCTRtablediv').toggleClass('nodisplay');

                    $('#CCTRtable').DataTable(
                    {
                        "paging": true,
                        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ]
                    });
                }               
                else
                {
                    swal('Nope.', 'There is no data for your requested time range. Please adjust and try again.', 'error');
                }
            }
        }); 
 	});


 	// Frozen Ticket Report
 	$(document).on('click', '#submitFTR', function()
 	{   
        swal({
            title: "Compiling Your Report...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });

        var start = $('#FTRstartdate').val();
        var end = $('#FTRenddate').val();
        var msg = "Frozen Ticket Report for Tickets Created Between " + start + ' and ' + end;

        $.ajax(
        {
            url: 'frozentickets',
            type: 'POST',
            data: $('#FTRform').serialize(),
            success: function(resp)
            {
                if(resp.length > 0)
                {
                    swal('Success!', 'Your Report is Ready!', 'success');

                    var reportrows = '';

                    for(var i=0; i<resp.length; i++)
                    {
                        var ondblclick = '';
                        ondblclick="window.open('getticketinfo/" + resp[i]['ticketid'] + "', '_blank')";
                        reportrows += '<tr ondblclick="' + ondblclick + '">';
                        reportrows += '<td>' + resp[i]['ticketid'] + '</td>';
                        reportrows += '<td>' + resp[i]['requestor'] + '</td>';
                        reportrows += '<td>' + resp[i]['category'] + '</td>';
                        reportrows += '<td>' + resp[i]['frozendt'] + '</td>';
                        reportrows += '<td>' + resp[i]['timefrozen'] + '</td>';
                        reportrows += '</tr>';
                    }

                    $('#FTRtbody').append(reportrows);
                    $('#FTRheading').text(msg);
                    $('#FTRdiv').toggleClass('nodisplay');
                    $('#FTRtablediv').toggleClass('nodisplay');

                    $('#FTRtable').DataTable(
                    {
                        "paging": true,
                        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ]
                    });

                }   
                else
                {
                    swal('Nope.', 'There is no data for your requested time range. Please adjust and try again.', 'error');
                }            
            }
        }); 
 	});


 	// Queue Performance Report
 	$(document).on('click', '#submitQPR', function()
 	{
        swal({
            title: "Compiling Your Report...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });
        
        var start = $('#QPRstartdate').val();
        var end = $('#QPRenddate').val();
        var msg = "Queue Performance Report for Tickets Created Between " + start + ' and ' + end;

        $.ajax(
        {
            url: 'queueperform',
            type: 'POST',
            data: $('#QPRform').serialize(),
            success: function(resp)
            {
            	if(resp.length > 1)
                {
                    swal('Success!', 'Your Report is Ready!', 'success');

                    var reportrows = '';

                    for(var i=0; i<resp.length; i++)
                    {
                        reportrows += '<tr>';
                        reportrows += '<td>' + resp[i]['queuename'] + '</td>';
                        reportrows += '<td>' + resp[i]['ticketsopened'] + '</td>';
                        reportrows += '<td>' + resp[i]['ticketsclosed'] + '</td>';
                        reportrows += '<td>' + resp[i]['averageclosetime'] + '</td>';
                        reportrows += '</tr>';
                    }

                    $('#QPRtbody').append(reportrows);
                    $('#QPRheading').text(msg);
                    $('#QPRdiv').toggleClass('nodisplay');
                    $('#QPRtablediv').toggleClass('nodisplay');

                    $('#QPRtable').DataTable(
                    {
                        "paging": true,
                        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ]
                    });

                }   
                else
                {
                    swal('Nope.', 'There is no data for your requested time range. Please adjust and try again.', 'error');
                }                   
            }
        }); 
 	});


 	// Ticket Resolution Time Search Report
 	$(document).on('click', '#submitTRTS', function()
 	{
 		var value = $('#TRTSvalue').val();
 		var unit = $('#TRTSunit').val();
 		var timeperiod = 0;

 		if(!isPosInt(value))
 		{
 			swal('Hey!', 'Time value must be a positive integer.', 'error')
 		}

 		// Convert value to seconds for easier calculations in PHP
 		switch(unit) {
		    case 'minutes':
		        timeperiod = value * 60;
		        break;
		    case 'hours':
		        timeperiod = (value * 60) * 60;
		        break;
		    case 'days':
		        timeperiod = ((value * 60) * 60) * 24;
		        break;
		    case 'weeks':
		        timeperiod = (((value * 60) * 60) * 24) * 7;
		        break;
		}

        swal({
            title: "Compiling Your Report...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });

        var msg = "Ticket Resolution Time for Tickets That Took Longer Than " + value + " " + unit + " to Resolve";

		$.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax(
        {
            url: 'ticketresolution',
            type: 'POST',
            data: {timeperiod:timeperiod},
            success: function(resp)
            {
            	if(resp.length > 1)
                {
                    swal('Success!', 'Your Report is Ready!', 'success');

                    var reportrows = '';

                    for(var i=0; i<resp.length; i++)
                    {
                        var ondblclick = '';
                        ondblclick="window.open('getticketinfo/" + resp[i]['ticketid'] + "', '_blank')";
                        reportrows += '<tr ondblclick="' + ondblclick + '">';
                        reportrows += '<td>' + resp[i]['ticketid'] + '</td>';
                        reportrows += '<td>' + resp[i]['requestor'] + '</td>';
                        reportrows += '<td>' + resp[i]['tech'] + '</td>';
                        reportrows += '<td>' + resp[i]['status'] + '</td>';
                        reportrows += '<td>' + resp[i]['timetoclose'] + '</td>';
                        reportrows += '</tr>';
                    }

                    $('#TRTtbody').append(reportrows);
                    $('#TRTheading').text(msg);
                    $('#TRTdiv').toggleClass('nodisplay');
                    $('#TRTtablediv').toggleClass('nodisplay');

                    $('#TRTtable').DataTable(
                    {
                        "paging": true,
                        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ]
                    });

                }   
                else
                {
                    swal('Nope.', 'There is no data for your requested time range. Please adjust and try again.', 'error');
                }                           
            }
        }); 
 	});


 	// Tech Summary Report
 	$(document).on('click', '#submitTSR', function()
 	{
 		if($('#TSRuserselect').val().length == 0)
 		{
 			swal('Hey!', 'Please Select Tech(s) to Include in This Report.', 'error').then(function()
                {
                    $('#TSRuserselect').selectpicker('toggle');
                });
            return;
	    }

        swal({
            title: "Compiling Your Report...",
            text: "Please be patient!",
            showCancelButton: false,
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            imageUrl: "/img/loadgear.gif"
        });

        var start = $('#TSRstartdate').val();
        var end = $('#TSRenddate').val();
        var msg = "Tech Summary Report for Tickets Created Between " + start + ' and ' + end;

        $.ajax(
        {
            url: 'techsum',
            type: 'POST',
            data: $('#TSRform').serialize(),
            success: function(resp)
            {            	console.log(resp);
                if(resp.length > 1)
                {
                    swal('Success!', 'Your Report is Ready!', 'success');

                    var reportrows = '';

                    for(var i=0; i<resp.length; i++)
                    {
                            var ondblclick = '';
                            ondblclick="window.open('getticketinfo/" + resp[i]['ticketid'] + "', '_blank')";
                            reportrows += '<tr ondblclick="' + ondblclick + '">';
                            reportrows += '<td>' + resp[i]['ticketid'] + '</td>';
                            reportrows += '<td>' + resp[i]['queue'] + '</td>';
                            reportrows += '<td>' + resp[i]['category'] + '</td>';
                            reportrows += '<td>' + resp[i]['requestor'] + '</td>';
                            reportrows += '<td>' + resp[i]['tech'] + '</td>';
                            reportrows += '<td>' + resp[i]['created_at'] + '</td>';
                            reportrows += '<td>' + resp[i]['dateresolved'] + '</td>';
                            reportrows += '<td>' + resp[i]['status'] + '</td>';
                            reportrows += '<td>' + resp[i]['timetoclose'] + '</td>';
                            reportrows += '</tr>';
                    }

                    $('#TSRtbody').append(reportrows);
                    $('#TSRheading').text(msg);
                    $('#TSRdiv').toggleClass('nodisplay');
                    $('#TSRtablediv').toggleClass('nodisplay');

                    $('#TSRtable').DataTable(
                    {
                        "paging": true,
                        "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ]
                    });
                }   
                else
                {
                    swal('Nope.', 'There is no data for your requested time range. Please adjust and try again.', 'error');
                }
            }
        }); 
 	});


 	$(document).on('click', '#rerunbtn', function()
 	{
 		location.reload();
 	});

}); // end of doc.ready


// Daterange picker 
$(function() 
{

    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) 
    {
        $('.reportrange input.start').val(start.format('MMMM D, YYYY'));
        $('.reportrange input.end').val( end.format('MMMM D, YYYY'));
    }

    $('.reportrange').daterangepicker(
    {
        startDate: start,
        endDate: end,
        ranges: 
        {
           	'Today': [moment(), moment()],
           	'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           	'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           	'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           	'This Month': [moment().startOf('month'), moment().endOf('month')],
           	'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }

    }, cb);

    cb(start, end);    
});

function printData(tableid)
{
   var divToPrint=document.getElementById(tableid);
   newWin= window.open("");
   newWin.document.write(divToPrint.outerHTML);
   newWin.print();
   newWin.close();
}
