<style type="text/css">
	@import "http://fonts.googleapis.com/css?family=Droid+Sans";
form{
background-color:#fff
}
#maindiv{
width:960px;
margin:10px auto;
padding:10px;
font-family:'Droid Sans',sans-serif
}
#formdiv{
width:500px;
float:left;
text-align:center
}
form{
padding:40px 20px;
box-shadow:0 0 10px;
border-radius:2px
}
h2{
margin-left:30px
}
.upload{
background-color:red;
border:1px solid red;
color:#fff;
border-radius:5px;
padding:10px;
text-shadow:1px 1px 0 green;
box-shadow:2px 2px 15px rgba(0,0,0,.75)
}
.upload:hover{
cursor:pointer;
background:#c20b0b;
border:1px solid #c20b0b;
box-shadow:0 0 5px rgba(0,0,0,.75)
}
#file{
color:green;
padding:5px;
border:1px dashed #123456;
background-color:#f9ffe5
}
#upload{
margin-left:45px
}
#noerror{
color:green;
text-align:left
}
#error{
color:red;
text-align:left
}
#img{
width: 6%;
border:none;
margin-left:-11px;
margin-bottom:91px;
}
#img:hover {
	cursor: pointer;
}
.abcd{
text-align:center
}
.abcd img{
	/*height:100px;*/
	width:35%;
	padding:5px;
	border:1px solid #e8debd
}
b{
color:red
}
</style>

<div class="page-wrapper" style="min-height: 818px; margin-right: 240px;">
	<div class="container-fluid">

		<?php echo $this->Form->create('Agency', 
			array(
				'id' => 'upload-editrequirements',
				'style' => '',
				'class' => 'form-horizontal form-label-left',
				'enctype'=>'multipart/form-data'
			)); ?>

			<p>Please upload legal documents Ex. Business Permits</p>
			<p>Only JPEG,PNG,JPG type and image size should be less than 100KB.</p>
			<div id="filediv">
				<input name="file[]" type="file" id="file"/>
			</div>
			<br>
			<input type="button" id="add_more" class="btn btn-success" value="Add More Files"/>
			<input type="submit" value="Upload File" name="submit" id="upload" class="btn btn-info"/>
		</form>
	</div>
</div>

<script type="text/javascript">
	$(function(){

		var abc = 0;
		$('#add_more').click(function() {
			$(this).before($("<div/>", {
				id: 'filediv'
			}).fadeIn('slow').append($("<input/>", {
				name: 'file[]',
				type: 'file',
				id: 'file'
			}), $("<br/><br/>")));
		});

		$('body').on('change', '#file', function() {
			if (this.files && this.files[0]) {
				abc += 1; // Incrementing global variable by 1.
				var z = abc - 1;
				var x = $(this).parent().find('#previewimg' + z).remove();
				$(this).before("<div id='abcd" + abc + "' class='abcd'><img id='previewimg" + abc + "' src=''/></div>");
				var reader = new FileReader();
				reader.onload = imageIsLoaded;
				reader.readAsDataURL(this.files[0]);
				$(this).hide();
				$("#abcd" + abc).append($("<img/>", {
				id: 'img',
				src: '/agency/img/trash.png',
				alt: 'delete'
				}).click(function() {
					$(this).parent().parent().remove();
				}));
			}
		});
		// To Preview Image
		function imageIsLoaded(e) {
			$('#previewimg' + abc).attr('src', e.target.result);
		};
		$('#upload').click(function(e) {
			var name = $(":file").val();
			if (!name) {
			alert("Please upload atleast 1 file.");
			e.preventDefault();
			}
		});
		

	}); // end js
</script>