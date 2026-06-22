<?php

function braillewright_register_theme_page()
{
    add_theme_page(
        sprintf(esc_html__('%s Dashboard', 'braillewright'), wp_get_theme()),
        sprintf(esc_html__('%s Dashboard', 'braillewright'), wp_get_theme()),
        'edit_theme_options',
        'braillewright-options',
        'braillewright_options_content'
    );
}
add_action('admin_menu', 'braillewright_register_theme_page');

function braillewright_options_content()
{
    $pro_url = '#'; ?>
	<div id="braillewright-dashboard-wrap" class="wrap braillewright-dashboard-wrap">
		<img class="braillewright-logo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/braillewright-logo.png' ); ?>" alt="<?php echo esc_attr__( 'Logo for the Braillewright WordPress Theme featuring a stylized black fountain pen with a gold nib, wrapped in a looping gold flourish with sparkles, dancing above the word Braillewright in large black serif lettering on a white background.', 'braillewright' ); ?>" style="max-width:220px;height:auto;display:block;margin:0 0 16px;">
		<h2><?php printf(esc_html__('%s Dashboard', 'braillewright'), esc_html( (string) wp_get_theme() )); ?></h2>
		<p class="braillewright-credit"><?php esc_html_e( 'Braillewright is created and maintained by Aaron Di Blasi of Mind Vault Solutions, Ltd. on behalf of Top Tech Tidbits, with engineering support from Claude Code.', 'braillewright' ); ?></p>
		<?php do_action('theme_options_before'); ?>
		<div class="main">
			<?php if (function_exists('braillewright_features_init')) : ?>
			<div class="thanks-upgrading" style="background-image: url(<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/bg-texture.png' ); ?>)">
				<h3>Thanks for upgrading!</h3>
				<p>You can find the new features in the Customizer</p>
			</div>
			<?php endif; ?>
			<?php if (!function_exists('braillewright_features_init')) : ?>
			<div class="getting-started">
				<h3>Get Started with Braillewright</h3>
				<p>Follow this step-by-step guide to customize your website with Braillewright:</p>
				<a href="#" target="_blank">Read the Getting Started Guide</a>
			</div>
			<div class="pro">
				<h3>Customize More with Braillewright</h3>
				<p>Add 13 new customization features to your site with the <a href="<?php echo esc_url( $pro_url ); ?>" target="_blank">Braillewright</a> plugin.</p>
				<ul class="feature-list">
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/layouts.png' ); ?>" />
						</div>
						<div class="text">
							<h4>New Layouts</h4>
							<p>New layouts help your content look and perform its best. You can switch to new layouts effortlessly from the Customizer, or from specific posts or pages.</p>
							<p>Braillewright adds 6 new layouts.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/custom-colors.png' ); ?>" />
						</div>
						<div class="text">
							<h4>Custom Colors</h4>
							<p>Custom colors let you match the color of your site with your brand. Point-and-click to select a color, and watch your site update instantly.</p>
							<p>With 60 color controls, you can change the color of any element on your site.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/fonts.png' ); ?>" />
						</div>
						<div class="text">
							<h4>New Fonts</h4>
							<p>Stylish new fonts add character and charm to your content. Select and instantly preview fonts from the Customizer.</p>
							<p>Since Braillewright is powered by Google Fonts, it comes with 728 fonts for you to choose from.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/header-image.png' ); ?>" />
						</div>
						<div class="text">
							<h4>Flexible Header Image</h4>
							<p>Header images welcome visitors and set your site apart. Upload your image and quickly resize it to the perfect size.</p>
							<p>Display the header image on just the homepage, or leave it sitewide and link it to the homepage.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/featured-videos.png' ); ?>" />
						</div>
						<div class="text">
							<h4>Featured Videos</h4>
							<p>Featured Videos are an easy way to share videos in place of Featured Images. Instantly embed a Youtube video by copying and pasting its URL into an input.</p>
							<p>Braillewright auto-embeds videos from Youtube, Vimeo, DailyMotion, Flickr, Animoto, TED, Blip, Cloudup, FunnyOrDie, Hulu, Vine, WordPress.tv, and VideoPress.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/featured-sliders.png' ); ?>" />
						</div>
						<div class="text">
							<h4>Featured Sliders</h4>
							<p>Featured Sliders are an easy way to share image sliders in place of Featured Images. Quickly add responsive sliders to any page or post.</p>
							<p>Braillewright integrates with the free Meta Slider plugin with styling and sizing controls for your sliders.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/background-images.png' ); ?>" />
						</div>
						<div class="text">
							<h4>Background Images</h4>
							<p>Background images help you stand out from the rest. Upload a unique image to use as the backdrop for your site.</p>
							<p>Background images are automatically centered and sized to fit the screen.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/background-textures.png' ); ?>" />
						</div>
						<div class="text">
							<h4>Background Textures</h4>
							<p>Background textures transform the look and feel of your site. Switch to a textured background with a click.</p>
							<p>Braillewright includes 39 bundled textures to choose from.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/featured-image-size.png' ); ?>" />
						</div>
						<div class="text">
							<h4>Featured Image Size</h4>
							<p>Set each Featured Image to the perfect size. You can change the aspect ratio for all Featured Images and individual Featured Images with ease.</p>
							<p>Braillewright includes twelve different aspect ratios for your Featured Images.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/widget-areas.png' ); ?>" />
						</div>
						<div class="text">
							<h4>New Widget Areas</h4>
							<p>Utilize a sidebar and four additional widget areas for greater flexibility. Increase ad revenue and generate more email subscribers by adding widgets throughout your site.</p>
							<p>Braillewright adds 4 new widget areas.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/secondary-menu.png' ); ?>" />
						</div>
						<div class="text">
							<h4>Secondary Menu</h4>
							<p>The additional menu allows you to expand and optimize your site's navigation. Quickly create and publish your new menu just like the Primary menu.</p>
							<p>Braillewright adds a Secondary to the top of the site.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/display-controls.png' ); ?>" />
						</div>
						<div class="text">
							<h4>Display Controls</h4>
							<p>Display controls let you display the parts of your site you want to show off, and hide the rest. Remove elements with just one click.</p>
							<p>Braillewright includes display controls for 11 different elements.</p>
						</div>
					</li>
					<li>
						<div class="image">
							<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/footer-text.png' ); ?>" />
						</div>
						<div class="text">
							<h4>Custom Footer Text</h4>
							<p>Custom footer text lets you further brand your site. Just start typing to add your own text to the footer.</p>
							<p>The footer text supports plain text and full HTML for adding links.</p>
						</div>
					</li>
				</ul>
				<p><a href="<?php echo esc_url( $pro_url ); ?>" target="_blank">Click here</a> to view Braillewright now, and see what it can do for your site.</p>
			</div>
			<div class="pro-ad" style="background-image: url(<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/bg-texture.png' ); ?>)">
				<h3>Add Incredible Flexibility to Your Site</h3>
				<p>Start customizing with Braillewright today</p>
				<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank">View Braillewright</a>
			</div>
			<?php endif; ?>
		</div>
		<div class="sidebar">
			<div class="dashboard-widget">
				<h4>More Amazing Resources</h4>
				<ul>
					<li><a href="#" target="_blank">Support Center</a></li>
					<li><a href="https://github.com/MVSLTD/braillewright/issues" target="_blank">Support (GitHub Issues)</a></li>
					<li><a href="#" target="_blank">Changelog</a></li>
					<li><a href="#" target="_blank">CSS Snippets</a></li>
					<li><a href="#" target="_blank">Starter child theme</a></li>
					<li><a href="#" target="_blank">Demo data</a></li>
					<li><a href="<?php echo esc_url( $pro_url ); ?>" target="_blank">Braillewright</a></li>
				</ul>
			</div>
			<div class="dashboard-widget">
				<h4>User Reviews</h4>
				<img src="<?php echo esc_url( trailingslashit(get_template_directory_uri()) . 'assets/images/reviews.png' ); ?>" />
				<p>Braillewright is maintained in-house. See the GitHub repository for the changelog and to file issues.</p>
			</div>
			<div class="dashboard-widget">
				<h4>Reset Customizer Settings</h4>
				<p><b>Warning:</b> Clicking this buttin will erase the Braillewright theme's current settings in the Customizer.</p>
				<form method="post">
					<input type="hidden" name="braillewright_reset_customizer" value="braillewright_reset_customizer_settings"/>
					<p>
						<?php wp_nonce_field('braillewright_reset_customizer_nonce', 'braillewright_reset_customizer_nonce'); ?>
						<?php submit_button('Reset Customizer Settings', 'delete', 'delete', false); ?>
					</p>
				</form>
			</div>
		</div>
		<?php do_action('theme_options_after'); ?>
	</div>
<?php
}
