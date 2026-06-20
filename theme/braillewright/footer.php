<?php do_action( 'main_bottom' ); ?>
</section> <!-- .main -->
<?php get_sidebar( 'primary' ); ?>
<?php do_action( 'after_main' ); ?>
</div><!-- .max-width -->
</div><!-- .primary-container -->

<?php 
// Elementor `footer` location
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) :
?>
<footer id="site-footer" class="site-footer" role="contentinfo">
    <div class="max-width">
        <?php do_action( 'footer_top' ); ?>
    </div>
    <div class="design-credit">
        <span>
            <?php
            $footer_text = '';
            $footer_text = apply_filters( 'braillewright_footer_text', $footer_text );
            echo do_shortcode( wp_kses_post( $footer_text ) );
            ?>
        </span>
    </div>
</footer>
<?php endif; ?>
</div><!-- .overflow-container -->

<?php do_action( 'body_bottom' ); ?>

<?php wp_footer(); ?>

</body>
</html>