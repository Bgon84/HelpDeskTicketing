<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'PagesController@index'); 				// Index (sign in page)
Route::get('home', 'PagesController@index'); 			// Index (sign in page)
Route::get('techdash', 'PagesController@tech'); 		// Tech Dashboard
Route::get('admindash', 'PagesController@admin');		// Admin Dashboard
Route::get('userdash', 'PagesController@user');			// User Dashboard
Route::get('auditordash', 'PagesController@auditor'); 	// Auditor Dashboard
Route::get('userdir', 'PagesController@userdir'); 		// User Directory
Route::post('settechdashrefresh', 'PagesController@settechdashrefresh'); 		// Set Refresh Rate for Tech Dashboard
Route::get('edituser/{id}', 'PagesController@edituser'); // User Edit From User Directory settechdashrefresh

// User Routes
Route::post('createuser', 'UsersController@create'); 	// Create
Route::post('getuserinfo', 'UsersController@getinfo'); 	// Get Info
Route::post('edituser', 'UsersController@update'); 		// Update
Route::post('opt', 'UsersController@opt'); 				// Opt In/Out

// Authentication Routes
Route::post('createauth', 'AuthenticationController@create'); 		// Create
Route::post('editauth', 'AuthenticationController@update'); 		// Update
Route::post('updateAuthOptions', 'AuthenticationController@updateAuthOption'); // Update Authentication Option
Route::post('editldapsynctrigger', 'AuthenticationController@updateldapsynctrigger'); // Sync LDAP
Route::get('syncLdap', 'AuthenticationController@ldapsync');

// Group Routes
Route::post('creategroup', 'GroupsController@create'); 	// Create 
Route::post('getgroupinfo', 'GroupsController@getinfo');// Get Info
Route::post('editgroup', 'GroupsController@update'); 	// Update
Route::post('defaultgroup', 'GroupsController@setdefaultgroup'); 	// Default Group Set/Update

// Role Routes
Route::post('createrole', 'RolesController@create'); 	// Create 
Route::post('getroleinfo', 'RolesController@getinfo');	// Get Info
Route::post('editrole', 'RolesController@update'); 		// Update

// Queue Routes
Route::post('createqueue', 'QueuesController@create'); 	// Create 
Route::post('getqueueinfo', 'QueuesController@getinfo');// Get Info
Route::post('editqueue', 'QueuesController@update'); 	// Update
Route::post('gettechsforqueue', 'QueuesController@gettechs'); 	// Get Techs for Select Tech
Route::post('checkfortickets', 'QueuesController@checkfortickets'); 	// Check if Queue has tickets
Route::post('movetickets', 'QueuesController@movetickets'); 	// Move Tickets

// Category Routes
Route::post('createcategory', 'CategoriesController@create'); 	// Create 
Route::post('getcategoryinfo', 'CategoriesController@getinfo'); // Get Info
Route::post('editcategory', 'CategoriesController@update'); 	// Update

// Notification Routes
Route::post('createnotification', 'NotificationsController@create'); 	// Create 
Route::post('getnotificationinfo', 'NotificationsController@getinfo'); 	// Get Info
Route::post('editnotification', 'NotificationsController@update'); 		// Update

// Ticket Routes
Route::post('createticket', 'TicketsController@create'); 				// Create 
Route::post('newtechtixcheck', 'TicketsController@newtechtixcheck');// Check For New Tech Tickets
Route::post('getticketinfo/editticket', 'TicketsController@update');	// Update
Route::post('getticketinfo/voidticket', 'TicketsController@voidticket');// Void Ticket
Route::post('getticketinfo/escalateticket', 'TicketsController@escalateticket');// Escalate Ticket
Route::post('getticketinfo/freezeticket', 'TicketsController@freezeticket');// Freeze Ticket
Route::post('getticketinfo/thawticket', 'TicketsController@thawticket');// Thaw Ticket
Route::post('getticketinfo/mergetickets', 'TicketsController@mergetickets');// Thaw Ticket
Route::post('getticketinfo/reopenticket', 'TicketsController@reopenTicket');// Reopen Ticket
Route::get('getticketinfo/{id}', 'TicketsController@getinfo'); 			// Get Info

// Priority Routes
Route::post('createpriority', 'PriorityController@create'); // Create 
Route::post('editpriority', 'PriorityController@update');	// Update

// SMTP settings
Route::post('editsmtp', 'SMTPController@update'); // Update
Route::post('testsmtp', 'SMTPController@sendSMTPtest'); // Send test email

// User Settings Routes
Route::post('updatemyinfo', 'UsersController@updateself'); 		// Update Info
Route::post('changepass', 'UsersController@changepassword'); 	// Change Password
Route::post('prefdash', 'UsersController@preferreddash'); 		// Preferred Dashboard

// Report Routes
Route::post('tprreport', 'ReportsController@techPerfomance'); 			// Tech Performance Report
Route::post('catclosure', 'ReportsController@catClosureTime'); 			// Category Closure Time Report
Route::post('frozentickets', 'ReportsController@frozenTickets'); 		// Frozen Ticket Report
Route::post('queueperform', 'ReportsController@queuePerformance'); 		// Queue Performance Report
Route::post('ticketresolution', 'ReportsController@resolutionTimeSearch'); 	// Ticket Resolution Time Search Report
Route::post('techsum', 'ReportsController@techSummary'); 			// Tech Summary Report

// Tech Dash Tables Routes
Route::get('getalltechtix', 'TechTablesController@getAllTechTix'); // Get all Tech's Tickets for Tech Dash
Route::get('getalltix', 'TechTablesController@getAllTix'); // Get all Tickets for Tech Dash 
Route::get('getallopentix', 'TechTablesController@getAllOpenTix'); // Get all open Tickets for Tech Dash 
Route::get('getallopentechtix', 'TechTablesController@getAllOpenTechTix'); // Get all Tech's open Tickets for Tech Dash 

Auth::routes();


// Added by make:auth
//Route::get('/home', 'HomeController@index')->name('home');

