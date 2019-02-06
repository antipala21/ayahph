<link rel="stylesheet" href="/user/fonts/material-design-iconic-font/css/material-design-iconic-font.css">
<link href="/css/style_wizard.css" rel="stylesheet" type="text/css">

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
				<h3 class="text-themecolor">User Login</h3>
			</div>
		</div>
		<div id="wizard_login">
			<?php
				echo $this->Form->create('User', 
					array(
						'id' => 'UserLoginForm',
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
								'value' => 'user1@test.com' // for test only
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
			<p>For New Acccount</p>
			<p><a href="/user/register">Register Here</a> (Or) Log in as <a href="/agency/login">Agency<span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span></a></p>
		</div>
	</div>
</div>