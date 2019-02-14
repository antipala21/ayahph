<style>
	/* Always set the map height explicitly to define the size of the div
	 * element that contains the map. */
	#map {
		height: 100%;
		height: 300px;
	}
</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12 shedules-detail-container">
				<h3>- map -</h3>
			</div>
			<div class="col-md-12"><hr></div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div id="map"></div>
			</div>
		</div>
	</div>
</div>

<script>
	var map;
	function initMap() {
		map = new google.maps.Map(document.getElementById('map'), {
			center: {lat: -34.397, lng: 150.644},
			zoom: 8
		});
	}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo Configure::read('google_map_api_key') ?>&callback=initMap" async defer></script>