$(document).ready(function() 
{
	$('#userticketstbl').DataTable(
	{
		"paging": true,
		"lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ]
	});
    	
}); // end doc.ready

