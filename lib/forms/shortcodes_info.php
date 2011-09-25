<div class="wrap">

<h2><?php _e('Facebook All Ine One Shortcodes', 'wdfbaio');?></h2>

<h3>"Like" button shortcode</h3>
<p><em>Tag:</em> <code>[wdfbaio_like_button]</code></p>
<p><em>Attributes:</em> none</p>
<p>
	<em>Example:</em>
	<code>[wdfbaio_like_button]</code> - will create a Facebook Like/Send button with the settings you set up <a href="?page=wdfbaio">here</a>.
</p>
<p><strong>Note:</strong> you have to <a href="?page=wdfbaio">allow</a> usage of <em>Facebook "Like/Send" button</em> for this shortcode to have any effect. If you dislike the default button placement options, you can set the placement to "Manual" and use this shortcode in your posts to insert the button wherever you wish.</p>

<h3>Events shortcode</h3>
<p><em>Tag:</em> <code>[wdfbaio_events]</code></p>
<p>
	<em>Attributes:</em>
	<ul>
		<li><code>for</code> - <strong>required.</strong> Valid Facebook ID (e.g. <code>100002370116390</code>)</li>
		<li><code>starting_from</code> - <em>optional.</em> Limits shown events to ones after the specified date. Accepted value is a date in <code>YYYY-MM-DD</code> format. (e.g. <code>2011-06-15</code>)</li>
		<li><code>only_future</code> - <em>optional.</em> Limits shown events to ones that start in the future. Accepted values are <code>true</code> and <code>false</code>. Defaults to <code>false</code>.</li>
		<li><code>show_image</code> - <em>optional.</em> Shows the image associated with event. Accepted values are <code>true</code> and <code>false</code>. Defaults to <code>true</code>.</li>
		<li><code>show_location</code> - <em>optional.</em> Shows event location. Accepted values are <code>true</code> and <code>false</code>. Defaults to <code>true</code>.</li>
		<li><code>show_start_date</code> - <em>optional.</em> Shows when the event starts. Accepted values are <code>true</code> and <code>false</code>. Defaults to <code>true</code>.</li>
		<li><code>show_end_date</code> - <em>optional.</em> Shows when the event ends. Accepted values are <code>true</code> and <code>false</code>. Defaults to <code>true</code>.</li>
	</ul>
</p>
<p>
	<em>Examples:</em>
	<ul>
		<li><code>[wdfbaio_events for="100002370116390"]</code> - will create a list of upcoming Facebook events for this Facebook user. All optional fields will be included (image, location, start and end dates).</li>
		<li><code>[wdfbaio_events for="100002370116390" show_image="false"]</code> - will create a list of upcoming Facebook events for this Facebook user. Event image will <strong>not</strong> be included.</li>
		<li><code>[wdfbaio_events for="100002370116390"]I don't have anything going on right now. Don't judge me.[/wdfbaio_events]</code> - will create a list of upcoming Facebook events for this Facebook user. All optional fields will be included (image, location, start and end dates). If there are no events, the tag content will be displayed instead (<code>I don't have anything going on right now. Don't judge me.</code>)</li>
		<li><code>[wdfbaio_events for="100002370116390" starting_from="2011-06-01"]</code> - will create a list of upcoming Facebook events for this Facebook user, starting from June 1st, 2011.</li>
		<li><code>[wdfbaio_events for="100002370116390" only_future="true"]</code> - will create a list of upcoming Facebook events for this Facebook user, no past events will be shown.</li>
	</ul>
</p>

<h3>Connect shortcode</h3>
<p><em>Tag:</em> <code>[wdfbaio_connect]</code></p>
<p>
	<em>Attributes:</em>
	<ul>
		<li>None</li>
		<li>Any text you supply between <code>[wdfbaio_connect]</code> and <code>[/wdfbaio_connect]</code> tags will be used as button text.</li>
	</ul>
</p>
<p>
	<em>Examples:</em>
	<ul>
		<li><code>[wdfbaio_connect]</code> - will create a Facebook Connect button with default text (&quot;Log in with Facebook&quot;)</li>
		<li><code>[wdfbaio_connect]Get in![/wdfbaio_connect]</code> - will create a a Facebook Connect button that says &quot;Get in!&quot;.</li>
	</ul>
</p>
<p><strong>Note:</strong> you have to <a href="?page=wdfbaio">allow</a> registering with Facebook in your plugin settings (under &quot;Facebook Connect&quot;) for this shortcode to work.</p>

</div>