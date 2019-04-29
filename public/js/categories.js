$(document).ready(function() 
{
    $("#createcategorypriorityselect").selectpicker('refresh');
    $("#createcategoryqueueselect").selectpicker('refresh');
    $("#editcategoryqueueselect").selectpicker('refresh');    
    $('#editcategoryselect').selectpicker('refresh');
    $('#editcategorypriorityselect').selectpicker('refresh');

    // Clear Create Category Form
    $('#clearcreatecategoryform').on('click', function()
    {
        ClearForm('createcategoryform');
    });

    // Clear Edit Category Form
    $('#cleareditcategoryform').on('click', function()
    {
       ClearForm('editcategoryform');
    });

	// Submit Category Creation
    $('#createcategorysubmit').on('click', function()
    {
        var createcatname = $('#createcategoryname').val().trim();

        if(createcatname.length == 0)
        {
            swal('Hey!', 'How about naming this Category?', 'error');
            $('#createcategoryname').addClass('alert');
            $('#createcategoryname').val('');
            return;
        }

        if($('#createcategorypriorityselect').val().length == 0)
        {
            swal('Hey!', 'You Must Assign a Priority to This Category.', 'error').then(function()
                {
                    $('#createcategorypriorityselect').selectpicker('toggle');
                });            
            return;
        }

        if($('#createcategoryqueueselect').val().length == 0)
        {
            swal('Hey!', 'You Must Assign This Category to a Queue.', 'error').then(function()
                {
                    $('#createcategoryqueueselect').selectpicker('toggle');
                });            
            return;
        }

        $.ajax(
        {
            url:'createcategory',
            type: 'POST',
            data: $('#createcategoryform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                if(resp[0] == "success")
                {
                    var catid = resp[1]['categoryid'];
                    var catname = resp[1]['category'];
                    var active = resp[1]['active'];

                    swal('Success!', 'Your Category Has Been Created!', 'success');

                    ClearForm('createcategoryform');

                    PopulateCatSelects(catid, catname, active);                     
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
        });
    });

    //Populate Category Editing Form when Category is selected
    $('#editcategoryselect').on('change', function()
    {
        var categoryid = $('#editcategoryselect option:selected').val();       

    	$('#editcategorypriorityselect').val('default');
    	$('#editcategorypriorityselect').selectpicker('refresh');
        $("#editcategoryqueueselect").val('default');
        $("#editcategoryqueueselect").selectpicker('refresh');
        
        $.ajaxSetup(
        {
            headers: 
            {
              'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            }
        });

        $.ajax(
        {
            url: 'getcategoryinfo',
            type: 'POST',
            data: {categoryid:categoryid},
            success: function(resp)
            {
                $('#editcategoryname').val(resp['category']['category']);
                $('#editcategorydescription').val(resp['category']['description']);

                $('#editcategorypriorityselect').selectpicker('val', resp['category']['priorityid']);

                var queues = [];
                for(i=0; i<resp['queues'].length; i++)
                {
                    queues.push(resp['queues'][i]['queueid']);
                }
                $("#editcategoryqueueselect").selectpicker('val', queues);

                if(resp['category']['active'] == 1)
                {
                    $('#editcategoryactive1').prop('checked', true);
                }
                else
                {
                    $('#editcategoryactive0').prop('checked', true);
                }      

                if(resp['category']['internal'] == 1)
                {
                    $('#editcategoryinternal1').prop('checked', true);
                }
                else
                {
                    $('#editcategoryinternal0').prop('checked', true);
                }            
            }
        });                               
    });
    
    // Submit Group Editing          
    $('#editcategorysubmit').on('click', function()
    {
        if($('#editcategoryselect').val().length == 0)
        {
            swal('Hey!', 'Please Select a Category to Edit.', 'error').then(function()
                {
                    $('#editcategoryselect').selectpicker('toggle');
                });

            return;
        }

        var editcatname = $('#editcategoryname').val().trim();

        if(editcatname.length == 0)
        {
            swal('Hey!', 'You Cannot Have a Blank Category Name.', 'error');
            $('#editcategoryname').addClass('alert');
            $('#editcategoryname').val('');
            return;
        }

        if($('#editcategorypriorityselect').val().length == 0)
        {
            swal('Hey!', 'You Must Assign a Priority to This Category.', 'error').then(function()
                {
                    $('#editcategorypriorityselect').selectpicker('toggle');
                });            
            return;
        }

        if($('#editcategoryqueueselect').val().length == 0)
        {
            swal('Hey!', 'You Must Assign This Category to a Queue.', 'error').then(function()
                {
                    $('#editcategoryqueueselect').selectpicker('toggle');
                });            
            return;
        }
        
        var newname = $('#editcategoryname').val().toUpperCase();

        if($('#editcategoryactive0').is(':checked'))
        {
            var inactivestatus = ' (Inactive)';
        }
        else
        {
            var inactivestatus = '';
        }

        $('#editcategoryselect option:selected').text(newname + ' ' + inactivestatus);

        $.ajax(
        {
            url:'editcategory',
            type: 'POST',
            data: $('#editcategoryform').serialize(),
            datatype: 'JSON',
            success: function(resp)
            {
                if(resp == "success")
                {
                    swal('Success!', 'Your Category Has Been Saved!', 'success');
 
                    ClearForm('editcategoryform');                    
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
        });
    });

}); // end doc.ready


function PopulateCatSelects(catid, catname, active)
{
    if(active == 0)
    {
        var status = ' (Inactive)';
        var inactive = true;
    }
    else
    {
        var status = '';
        var inactive = false;
    }

    $('#editcategoryselect').append('<option value="'+catid+'">' + catname + status + '</option>');
    $('#editcategoryselect').selectpicker('refresh'); 

    if(!inactive)
    {
        $('#categoryselect').append('<option value="'+catid+'">' + catname + '</option>');
        $('#categoryselect').selectpicker('refresh');         
    }
}