(function(){
	$(document).ready(function(){

		var HOST = window.location.host;
		var HREF = window.location.href;

		var ENABLE_NOTIFICATION = true;

		// User NOTIF
		if (ENABLE_NOTIFICATION && is_login) {
			setInterval(function(){
				agency_hire_accept_notif();
			},3000);
		}

		function agency_hire_accept_notif () {
			$.ajax({
				type: 'post',
				url: 'http://' + HOST +'/notif/hire_accept',
				data: {},
				success: function(data){
					var data = JSON.parse(data);
					console.log(data.count);
					if (data.count > hire_accept_count) {
							$.notify({
								message: "Hire Request was accepted.",
								// url: "/schedules"
							},{
								// url_target: "_self"
							});
						hire_accept_count = hire_accept_count + 1;
					}
				},
				error: function(){console.log('error')},
			});
		}

	}); // end doc. ready.
})();