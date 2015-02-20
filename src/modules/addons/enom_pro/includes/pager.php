<?php
/**
 * Project: enom_pro
 * @license GPL v2
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */

/**
 * @param int    $count total count to paginate
 * @param string $view ?view link
 * @param bool   $large is it a large or normal sized pager
 * @param int    $per_page
 * @param bool   $dom_element #hash to point browser to
 *
 * @uses $_GET['start'] 0 index start of result set
 */
function pager( $count, $view, $large = false, $per_page = 25, $dom_element = false) {

	$total       = $count;
	$pages       = ceil( ( $total / $per_page ) );
	$pages_array = array_keys( array_fill( 1, $pages, '' ) );
	echo '<nav><ul class="pagination' . ( $large ? ' pagination-lg' : '' ) . '">';
	echo '<li';
	$start = $_GET['start'];
	if ( @$start < $per_page ) {
		echo ' class="disabled"';
	}
	$currentPage = ( $start / $per_page ) + 1;
	$prevStart   = ( $currentPage - 2 ) * $per_page;
	if ( $prevStart < 0 ) {
		$prevStart = 0;
	}
	echo '><a data-start="' . $prevStart . '" href="' . $_SERVER['PHP_SELF'] . '?module=enom_pro&view=' . $view . '&start=' . $prevStart . ($dom_element ? $dom_element : '').'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';

	foreach ( $pages_array as $page ) {
		echo '<li';
		if ( $currentPage == $page ) {
			echo ' class="active"';
		}
		echo '>';
		$pageStart = ( $page - 1 ) * $per_page;
		echo '<a data-start="' . $pageStart . '" href="' . $_SERVER['PHP_SELF'] . '?module=enom_pro&view=' . $view . '&start=' . $pageStart . '#enom_pro_pricing_table">' . $page . '</a>';
		echo '</li>';
	}
	echo '<li';
	if ( @$start >= ( $total - $per_page ) ) {
		echo ' class="disabled"';
	}
	$nextStart = ( $currentPage ) * $per_page;
	echo '><a data-start="' . $nextStart . '" href="' . $_SERVER['PHP_SELF'] . '?module=enom_pro&view=' . $view . '&start=' . $nextStart . '#enom_pro_pricing_table" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';

	echo '</ul></nav>'; ?>

<div class="clearfix">
		<span class="floatleft">
			Page
			<span class="badge">
				<?php echo (ceil( $_GET['start'] / $per_page ) + 1);?>
			</span>
			of
			<span class="badge">
				<?php echo ceil( $count / $per_page); ?>
			</span>
	    </span>
			<span class="text-right">
				<span class="badge"><?php echo $count;?></span>
				Total
	    </span>
</div>
	<?php
}
