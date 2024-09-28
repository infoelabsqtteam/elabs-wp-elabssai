<?php
if (!defined('MCDATAPATH')) exit;

if (defined('MCCONFKEY')) {
	require_once dirname( __FILE__ ) . '/../protect.php';

	BVProtect_V565::init(BVProtect_V565::MODE_PREPEND);
}