<?php
	get_header();
?>

<div style="margin-top:2em; text-align:center;">

<?php if (!$wp_grant_blog) { ?>
	<p>Thank you for registering. <a href="<?php echo home_url(); ?>">Proceed to main site.</a></p>
<?php } else { ?>
	<p>Your new blog &quot;<?php echo $new_blog_title;?>&quot; is created.</p>
	<p><a href="<?php echo $new_blog_url;?>">Proceed to your blog.</a></p>
<?php } ?>

</div>

<?php
	get_footer();
?>