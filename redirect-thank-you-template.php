<?php
/*
Template Name: Redirect Thank You
*/

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        while (have_posts()) : the_post();

            if (get_query_var('vote_received') == 'yes') {
                $source_url = esc_url(rawurldecode(get_query_var('source')));
                echo "<h1>Thank you for your feedback!</h1>";
                echo "<p><a href='$source_url'>Click here to return home</a></p>";
            }

        endwhile; // End of the loop.
        ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
?>