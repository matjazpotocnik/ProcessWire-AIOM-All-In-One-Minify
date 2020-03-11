$(document).ready(function() {
	var AIOMtabs = $('#AIOMtabs');
	if(AIOMtabs.length == 0) return;
	
	AIOMtabs.WireTabs({
		items: $(".Inputfields li.WireTab"),
		id: 'AIOMlinks',
		rememberTabs: true
	});
	
	$('#ModuleEditForm').addClass('AIOM');
	
});
