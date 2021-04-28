<?php
$affiliate_id = affwp_get_affiliate_id();
?>

<div>
		<h2> Estadísticas globales</h2>
		<button
              class="btn btn-secondary btn-sign-in"
              onclick="window.location.href='https://www.theclassyoga.com/plan-amigo'">
              << Volver a tu plan amigo
		  </button>
	<div>

	<br/>

<div id="affwp-affiliate-dashboard-referral-counts" class="affwp-tab-content">

	<table class="affwp-table affwp-table-responsive">
		<thead>
			<tr>
				<th><?php _e( 'Unpaid Referrals', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Paid Referrals', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Visits', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Conversion Rate', 'affiliate-wp' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td data-th="<?php _e( 'Unpaid Referrals', 'affiliate-wp' ); ?>"><?php echo affwp_count_referrals( $affiliate_id, 'unpaid' ); ?></td>
				<td data-th="<?php _e( 'Paid Referrals', 'affiliate-wp' ); ?>"><?php echo affwp_count_referrals( $affiliate_id, 'paid' ); ?></td>
				<td data-th="<?php _e( 'Visits', 'affiliate-wp' ); ?>"><?php echo affwp_count_visits( $affiliate_id ); ?></td>
				<td data-th="<?php _e( 'Conversion Rate', 'affiliate-wp' ); ?>"><?php echo affwp_get_affiliate_conversion_rate( $affiliate_id ); ?></td>
			</tr>
		</tbody>
	</table>

	<?php
	/**
	 * Fires immediately after stats counts in the affiliate area.
	 *
	 * @since 1.0
	 *
	 * @param int $affiliate_id Affiliate ID of the currently logged-in affiliate.
	 */
	do_action( 'affwp_affiliate_dashboard_after_counts', $affiliate_id );
	?>

</div>

<div id="affwp-affiliate-dashboard-earnings-stats" class="affwp-tab-content">
	<table class="affwp-table affwp-table-responsive">
		<thead>
			<tr>
				<th><?php _e( 'Unpaid Earnings', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Paid Earnings', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Commission Rate', 'affiliate-wp' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td data-th="<?php _e( 'Unpaid Earnings', 'affiliate-wp' ); ?>"><?php echo affwp_get_affiliate_unpaid_earnings( $affiliate_id, true ); ?></td>
				<td data-th="<?php _e( 'Paid Earnings', 'affiliate-wp' ); ?>"><?php echo affwp_get_affiliate_earnings( $affiliate_id, true ); ?></td>
				<td data-th="<?php _e( 'Commission Rate', 'affiliate-wp' ); ?>"><?php echo affwp_get_affiliate_rate( $affiliate_id, true ); ?></td>
			</tr>
		</tbody>
	</table>

	<?php
	/**
	 * Fires immediately after earnings stats in the affiliate area.
	 *
	 * @since 1.0
	 *
	 * @param int $affiliate_id Affiliate ID of the currently logged-in affiliate.
	 */
	do_action( 'affwp_affiliate_dashboard_after_earnings', $affiliate_id );
	?>

</div>
