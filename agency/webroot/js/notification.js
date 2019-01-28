(function(){
	$(document).ready(function(){

		var HOST = window.location.host;
		var HREF = window.location.href;

		var ENABLE_NOTIFICATION = true;

		// Agency NOTIF
		if (ENABLE_NOTIFICATION && is_login) {
			setInterval(function(){
				agency_hire_request_notif();
			},3000);
		}

		function agency_hire_request_notif () {
			$.ajax({
				type: 'post',
				url: 'http://' + HOST +'/agency/notif/hire_request',
				data: {},
				success: function(data){
					var data = JSON.parse(data);
					console.log(data.count);
					if (data.count > hire_request_count) {
							$.notify({
								message: "New Hire Request!",
								url: "/agency/transaction"
							},{
								url_target: "_self"
							});
						hire_request_count = hire_request_count + 1;
					}
				},
				error: function(){console.log('error')},
			});
		}

	}); // end doc. ready.
})();