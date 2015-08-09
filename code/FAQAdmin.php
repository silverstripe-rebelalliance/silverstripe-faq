<?php

class FAQAdmin extends ModelAdmin {

	private static $url_segment = 'faq';

	private static $managed_models = array(
		'FAQ'
	);

	private static $menu_title = 'FAQs';

}
