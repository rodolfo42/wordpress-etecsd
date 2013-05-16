<?php $attachments = new Attachments( 'attachments' ); /* pass the instance name */ ?>
<?php if( $attachments->exist() ) : ?>
<?php 
// organizar anexos em fotos e arquivos
$fotos = array();
$arquivos = array();

while( $attachments->get() ) {
/*
referencia:
ID: <?php echo $attachments->id(); ?><br />
Type: <?php echo $attachments->type(); ?><br />
Subtype: <?php echo $attachments->subtype(); ?><br />
URL: <?php echo $attachments->url(); ?><br />
Image: <?php echo $attachments->image( 'thumbnail' ); ?><br />
Source: <?php echo $attachments->src( 'full' ); ?><br />
Size: <?php echo $attachments->filesize(); ?><br />
Title Field: <?php echo $attachments->field( 'title' ); ?><br />
Caption Field: <?php echo $attachments->field( 'caption' ); ?>
*/
	$at = array(
		'title' => $attachments->field( 'title' ),
		'mimetype' => $attachments->type(),
		'size' => $attachments->filesize(),
		'src' => $attachments->src( 'full' ),
		'thumb' => $attachments->src( 'thumbnail' ),
		'url' => $attachments->url()
	);
	
	if( preg_match("/^image.*/i", $attachments->type()) ) {
		$fotos[] = $at;
	} else {
		$arquivos[] = $at;
	}
} ?>


<?php if(!empty($fotos)) : ?>
	<div class="hentry">
		<div class="entry-inner">
			<div class="entry-content">
				<div style="float: right;"><strong><?php echo count($fotos); ?></strong> <?php echo __("fotos na galeria"); ?></div>
				<h3 style="margin-top: 0;"><?php echo __("Galeria de fotos"); ?></h3>
				<?php foreach( $fotos as $foto ) : ?>
					<a href="<?php echo $foto['url']; ?>" 
						style="float: left; display: block; width: 140px; height: 140px; margin: 0 13px 10px 0; border: 1px solid #DDD; cursor: -webkit-zoom-in;" 
						target="_blank"><img style="width: 140px;" src="<?php echo $foto['thumb']; ?>" />
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
<?php endif; // fotos ?>

<?php if(!empty($arquivos)) : ?>
	<div class="hentry">
		<div class="entry-inner">
			<div class="entry-content">
				<div style="float: right;"><strong><?php echo count($arquivos); ?></strong> <?php echo __("anexos para download"); ?></div>
				<h3 style="margin-top: 0;"><?php echo __("Anexos"); ?></h3>
				<?php foreach( $arquivos as $arquivo ) : ?>
					<p style="line-height: 20px; padding-left: 52px; background: url(<?php echo $arquivo['thumb']; ?>) no-repeat left center;">
						<strong><?php echo $arquivo['title']; ?></strong>
						<br />
						<span style="color: gray"><?php echo $arquivo['mimetype']; ?> (<?php echo $arquivo['size']; ?>)</span>
						<br />
						<a href="<?php echo $arquivo['url']; ?>">download</a>
					</p>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
<?php endif; // arquivos ?>

<?php endif; // attachments exist ?>