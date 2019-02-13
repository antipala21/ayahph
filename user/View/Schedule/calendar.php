<link href="/css/fullcalendar.min.css" rel="stylesheet" type="text/css">
<link href="/css/fullcalendar.print.min.css" rel="stylesheet" type="text/css" media="print">

<style type="text/css">
	.shedules-detail-container {
		margin-top: 25px;
	}
	#calendar {
		max-width: 800px;
		margin: 0 auto;
	}
</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12 shedules-detail-container">
				<h3>-  -</h3>
			</div>
			<div class="col-md-12"><hr></div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div id='calendar'></div>
			</div>
		</div>
	</div>
</div>

<script src="/js/moment.min.js"></script>
<script src="/js/fullcalendar.min.js"></script>

<script>
(function(){
	$(document).ready(function() {

		var nowDate = new Date();
		var year = nowDate.getFullYear();
		var _month = nowDate.getMonth();
		var _day = nowDate.getDate();

		var month = _month < 10 ? '0' + _month : _month;
		var day = _day < 10 ? '0' + _day : _day;

		var final_month = parseInt(month) + 1;

		var defaultDate = year + '-' + final_month + '-' + day;

		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next,today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay,listWeek'
			},
			defaultDate: defaultDate,
			navLinks: true, // can click day/week names to navigate views
			editable: true,
			eventLimit: true, // allow "more" link when too many events
			eventSources: [
				{
					url: '/ajax/get_calendar_data',
					type: 'POST',
					data: {
						custom_param1: 'something',
						custom_param2: 'somethingelse'
					},
					error: function() {
						alert('there was an error while fetching events!');
					},
				}
			]
		});
	});
})();

</script>