<link rel="stylesheet" href="/agency/fonts/material-design-iconic-font/css/material-design-iconic-font.css">
<link href="/agency/css/style_wizard.css" rel="stylesheet" type="text/css">

<div class="page-wrapper" style="min-height: 818px; margin-right: 240px;">
	<div class="container-fluid">

		<?php $flash = $this->Session->flash('user-detail'); ?>
		<?php if($flash): ?>
			<div class="alert alert-warning">
				<?php echo $flash; ?>
			</div>
		<?php endif; ?>

		<div class="row page-titles">
			<div class="col-md-5 col-8 align-self-center">
				<h3 class="text-themecolor">Agency Login</h3>
			</div>
		</div>
		<div id="wizard_login">
			<?php
				echo $this->Form->create('Agency', 
					array(
						'id' => 'AgencyLoginForm',
						'style' => '',
						'url' => '/login'
					)); ?>

					<?php
						echo $this->Form->inputs(array(
							'legend' => __(''),
							'email' => array(
								'autofocus' => 'autofocus',
								'label' => false,
								'required' => false,
								'div' => array('class' => 'form-group'),
								'class' => 'form-control',
								'placeholder' => 'Email',
								'value' => 'agency1@test.com' // for test only
							),
							'password' => array(
								'type' => 'password',
								'label' => false,
								'required' => false,
								'div' => array('class' => 'form-group'),
								'class' => 'form-control',
								'placeholder' => 'Password'
							),
							'Login' => array(
								'label' => false,
								'type' => 'submit',
								'name' => 'login',
								'div' => array('class' => 'form-group'),
								'class' => 'btn btn-default submit'
							)
						));
					?>
				
			<?php echo $this->Form->end(); ?>
			<p>For New Account</p>
			<p><a href="/agency/register">Register Here</a> (Or) Login as <a href="/user/login">Client(User)<span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span></a></p>
		</div>
	</div>
</div>