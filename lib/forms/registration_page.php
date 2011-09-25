<?php get_header(); ?>

<?php
$redirect_url = is_multisite() ?
	'/wp-signup.php?action=register&fb_register=1'
	:
	'/wp-login.php?action=register&fb_register=1'
;
$new_blog_options = $wp_grant_blog ?
	"{'name': 'blog_title', 'description': 'Your blog title', 'type': 'text'},{'name': 'blog_domain', description: 'Your blog address', 'type': 'text'}"
	: '';
$captcha = $this->data->get_option('wdfbaio_connect', 'no_captcha') ?
	'' : "{'name': 'captcha'}";

$new_blog_options .= ($new_blog_options && $captcha) ? ',' : '';
?>

	<h2>Register with Facebook</h2>

<?php foreach ($errors as $error) { ?>
	<?php $error = is_array($error) ? array_reduce($error, create_function('$val,$el', 'return "$val <br />$el";')) : $error; ?>
	<div class="error fade"><p><?php echo $error; ?></p></div>
<?php } ?>

<div style="margin-top:2em">

<iframe src="http://www.facebook.com/plugins/registration.php?
             client_id=<?php echo $this->data->get_option('wdfbaio_api', 'app_key');?>&
             redirect_uri=<?php echo urlencode(site_url($redirect_url));?>&
             fields=[
             	{'name': 'name'},
             	{'name': 'email'},
             	{'name': 'first_name'},
             	{'name': 'last_name'},
             	{'name': 'gender'},
             	{'name': 'location'},
             	{'name': 'birthday'},
             	<?php echo $new_blog_options;?>
             	<?php echo $captcha;?>
             ]
             "
        scrolling="auto"
        frameborder="no"
        style="border:none"
        allowTransparency="true"
        width="100%"
        height="530">
</iframe>

</div>

<?php //if ($this->data->get_option('wdfbaio_connect', 'force_facebook_registration')) get_footer(); ?>
<?php get_footer(); ?>