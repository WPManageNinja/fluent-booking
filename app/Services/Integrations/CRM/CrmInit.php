<?php
namespace FluentBooking\App\Services\Integrations\CRM;

class CrmInit {

	public function __construct() {
		$this->registerIntegrations();
	}

	/**
	 * Register all the CRM integrations from here
	 * @return void
	 */
	public function registerIntegrations()
	{
		$this->addContactMenuSection();
		$this->addAutomations();
	}

	/**
	 * load Assets for to Fluent CRM  contact section
	 * @return void
	 */
	public function addContactMenuSection()
	{
		add_action( 'fluent_crm/global_appjs_loaded', function () {
			wp_enqueue_script( 'fluent_booking_in_crm', FLUENT_BOOKING_URL . 'assets/js/fluent-crm-in-calendar.js');
		});
	}

	public function addAutomations()
	{
		/*
		 * TODO: You may Register your automations here
		 */
	}


}