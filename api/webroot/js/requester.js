
$(document).ready(function() {
	
	$("#submit").click(function () {

		var url = $("#url").val();
		try{
			var postData = JSON.parse($("#value").val());

			$.ajax({
			        type: "POST",
			        dataType: "JSON",
			        url: $("#url").val(),
			        data: postData,
			        success: function(data){
			        	$("#result").html(data);
			        },
			        error: function(e){
			        	try{
				        	var data = JSON.parse(e.responseText);
				        	$("#result").html(JSON.stringify(data));
				        }catch(e) {
				        	$("#result").html(e.responseText);
				        }
			        }
			});
		}catch(error) {
			console.log("Invalid JSON Data!");
		}
		return false;
	});

});