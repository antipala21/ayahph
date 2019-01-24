
<style type="text/css">
	img.legal_docu {
		width: 35%;
	}

	#img{
		width: 25px;
		margin-top: 5px;
	}
	#img:hover {
		cursor: pointer;
	}

</style>

<div class="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-xlg-12 col-md-12">

				<?php $success = $this->Session->flash('success'); ?>
				<?php if($success): ?>
					<div class="alert alert-success">
						<?php echo $success; ?>
					</div>
			<?php endif; ?>

				<br>
				<h3>Legal Documents</h3>
				<a class="btn btn-info" href="/agency/register-legal-documents/">Upload</a>
				<hr>
				<?php if($documents): ?>
					<?php foreach($documents as $key => $value): ?>
						<img class="legal_docu rounded mx-auto d-block" src="/agency/img/agency_permit/<?php echo $value['AgencyLegalDocument']['filename']; ?>">
						<center><img id="img" src="/agency/img/trash.png" data-id="<?php echo $value['AgencyLegalDocument']['id'] ?>" data-filename="<?php echo $value['AgencyLegalDocument']['filename']; ?>"></center>
						<hr>
					<?php endforeach; ?>
				<?php else: ?>
					<p>Empty.</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	
	$(document).ready(function(){

		$('img#img').click(function(){
			console.log($(this).attr('data-filename'));
			$.ajax({
				url: '/agency/register-legal-documents/delete',
				type: 'post',
				data: {filename : $(this).attr('data-filename'), id : $(this).attr('data-id')},
				success: function(data){
					console.log(data);
					if (data) {
						location.reload();
					}
				},
				error : function (){

				}
			});
		});
		// $('#business_permit').change(function(){
		// 	var fileName = $(this).val();
		// 	if(fileName !== null) {
		// 		console.log(fileName);
		// 		readURL(this, '#permit');
		// 	}
		// });

		// function readURL(input, el) {
		// 	if (input.files && input.files[0]) {
		// 		var reader = new FileReader();

		// 		reader.onload = function (e) {
		// 			$(el).attr('href', e.target.result);
		// 			$(el).show();

		// 			$('img#test').attr('src', e.target.result);
		// 			$('img#test').show();
		// 		}

		// 		reader.readAsDataURL(input.files[0]);
		// 	}
		// }

		// $('a#permit').click(function(e){
		// 	e.preventDefault();
		// 	viewImage($(this).attr('href'));
		// });
	});

</script>