<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package Cazuela
 * @since Cazuela 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<?php
				// Before Content theme hook callback
				thsp_hook_before_content();
			?>

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'page' ); ?>

<?php $attachments = new Attachments( 'attachments' ); /* pass the instance name */ ?>
<?php if( $attachments->exist() ) : ?>
<!-- snippet para mostrar os anexos da página -->
<div class="hentry">
<div class="entry-inner">
<div class="entry-content">
	<div style="float: right;"><strong><?php echo $attachments->total(); ?></strong> <?php echo __("anexos para download"); ?></div>
	<h3 style="margin-top: 0;"><?php echo __("Anexos"); ?></h3>
<?php /*
metodos para pegar informacao
ID: <?php echo $attachments->id(); ?><br />
Type: <?php echo $attachments->type(); ?><br />
Subtype: <?php echo $attachments->subtype(); ?><br />
URL: <?php echo $attachments->url(); ?><br />
Image: <?php echo $attachments->image( 'thumbnail' ); ?><br />
Source: <?php echo $attachments->src( 'full' ); ?><br />
Size: <?php echo $attachments->filesize(); ?><br />
Title Field: <?php echo $attachments->field( 'title' ); ?><br />
Caption Field: <?php echo $attachments->field( 'caption' ); ?>
*/ ?>
		<?php while( $attachments->get() ) : ?>
		<p style="line-height: 20px; padding-left: 52px; background: url(<?php echo $attachments->src( 'thumbnail' ); ?>) no-repeat left center;">
		<strong><?php echo $attachments->field( 'title' ); ?></strong>
<br />
		<span style="color: gray"><?php echo $attachments->subtype(); ?> (<?php echo $attachments->filesize(); ?>)</span>
<br />
<a href="<?php echo $attachments->url(); ?>">download</a>
	</p>
		<?php endwhile; ?>
</div>
</div>
</div>

	<?php endif; ?>

				<?php comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

			<?php
				// After Content theme hook callback
				thsp_hook_after_content();
			?>

		</div><!-- #content .site-content -->
	</div><!-- #primary .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>