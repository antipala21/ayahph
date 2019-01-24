(function(){
	$(document).ready(function(){

		var HOST = window.location.host;
		var HREF = window.location.href;

		var ENABLE_NOTIFICATION = true;
		var supplier_to_ship_count_flag = false;
		var buyer_bidding_count_flag = false;
		var supplier_bidding_paid_count_flag = false;
		var supplier_suki_flag = false;
		var buyer_suki_confirm_flag = false;

		var user_type = localStorage.getItem("user_type");

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
						if (supplier_to_ship_count_flag == false) {
							$.notify({
								message: "New Hire Request!",
								url: "/agency/transaction"
							},{
								url_target: "_self"
							});
						}
						supplier_to_ship_count_flag = true;
					}
				},
				error: function(){console.log('error')},
			});
		}

	}); // end doc. ready.
})();