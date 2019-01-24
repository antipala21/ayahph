<?php

$cakeDescription = __d('cake_dev', 'AyahPH Admin');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $this->fetch('title'); ?>
	</title>
	<?php
		echo $this->Html->meta('icon');
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');

		// echo $this->Html->css(array(
		// 	'plugins/bootstrap.min.css'
		// ));
	?>
	<!-- Bootstrap Core CSS -->
	<link href="/user/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<!-- chartist CSS -->
	<!-- <link href="/user/plugins/chartist-js/dist/chartist.min.css" rel="stylesheet">
	<link href="/user/plugins/chartist-js/dist/chartist-init.css" rel="stylesheet">
	<link href="/user/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.css" rel="stylesheet"> -->
	<!--This page css - Morris CSS -->
	<link href="/user/plugins/c3-master/c3.min.css" rel="stylesheet">
	<!-- Custom CSS -->
	<link href="/user/css/style.css" rel="stylesheet">
	<link href="/user/css/stylechild.css" rel="stylesheet">
	<!-- You can change the theme colors from here -->
	<link href="/user/css/colors/blue.css" id="theme" rel="stylesheet">
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

	<!-- ============================================================== -->
	<!-- All Jquery -->
	<!-- ============================================================== -->
	<script src="/user/plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap tether Core JavaScript -->
	<script src="/user/plugins/bootstrap/js/tether.min.js"></script>
	<script src="/user/plugins/bootstrap/js/bootstrap.min.js"></script>
	<!-- slimscrollbar scrollbar JavaScript -->
	<!-- <script src="/user/js/jquery.slimscroll.js"></script> -->
	<!--Wave Effects -->
	<script src="/user/js/waves.js"></script>
	<!--Menu sidebar -->
	<script src="/user/js/sidebarmenu.js"></script>
	<!--stickey kit -->
	<script src="/user/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
	<!--Custom JavaScript -->
	<script src="/user/js/custom.min.js"></script>
	<!-- ============================================================== -->
	<!-- This page plugins -->
	<!-- ============================================================== -->
	<!-- chartist chart -->
	<!-- <script src="/user/plugins/chartist-js/dist/chartist.min.js"></script>
	<script src="/user/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.min.js"></script> -->
	<!--c3 JavaScript -->
	<script src="/user/plugins/d3/d3.min.js"></script>
	<script src="/user/plugins/c3-master/c3.min.js"></script>
	<!-- Chart JS -->
	<script src="/user/js/dashboard1.js"></script>

	<!-- REGISTRATION -->
	<link href="/css/steps.css" rel="stylesheet" type="text/css">

	<!-- Modal -->
	<script src="/admin/js/jquery.modal.min.js"></script>
	<link rel="stylesheet" href="/admin/css/jquery.modal.min.css" />

</head>
<body class="fix-header fix-sidebar card-no-border">

	<div class="preloader">
		<svg class="circular" viewBox="25 25 50 50">
			<circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
		</svg>
	</div>

	<div id="main-wrapper">

		<?php echo $this->element('header'); ?>
		<?php if (
			strtolower($this->params['controller']) != "register"
			&& strtolower($this->params['controller']) != "login"
		): ?>
			<?php echo $this->element('aside'); ?>
		<?php endif; ?>

		<div id="content">

			<?php echo $this->Flash->render(); ?>

			<?php echo $this->fetch('content'); ?>
		</div>

		<footer class="footer"> © 2018 AayahPH </footer>

	</div>
</body>
</html>
