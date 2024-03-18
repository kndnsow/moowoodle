<?php
namespace MooWoodle\Emails;
class Emails_New_Enrollment extends \WC_Email {
	/**
	 * Constructor
	 */
	public $id;
	public $title;
	public $description;
	public $customer_email;
	public $heading;
	public $subject;
	public $template_html;
	public $template_plain;
	public $template_base;
	public $recipient;
	public $form_fields;
	public $object;
	public $email_data;
	function __construct() {
		$this->id = 'new_moodle_enrollment';
		$this->title = __('New Moodle Enrollment', 'moowoodle');
		$this->description = __('This is a notification email sent to the enrollees for new enrollment.', 'moowoodle');
		$this->customer_email = true;
		$this->heading = __('New Enrollment', 'moowoodle');
		$this->subject = __('{site_title} New Enrollment', 'moowoodle');
		$this->template_html = 'emails/new-enrollment.php';
		$this->template_plain = 'emails/plain/new-enrollment.php';
		// Call parent constructor
		parent::__construct();
		$this->template_base = MOOWOODLE_PLUGIN_PUTH . '/templates/';
	}
	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	public function trigger($email_data) {
		$this->recipient = $email_data['email'];
		$this->object = $email_data;
		if (!$this->is_enabled() || !$this->get_recipient()) {
			return;
		}
		$this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
	}
	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		ob_start();
		MooWoodle()->Template->get_template(
			$this->template_html,
			array(
				'enrollments' => $this->object,
				'user_data' => $this->recipient,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text' => false,
			)
		);
		return ob_get_clean();
	}
	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		MooWoodle()->Template->get_template(
			$this->template_plain,
			array(
				'enrollments' => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text' => true,
			)
		);
		return ob_get_clean();
	}
	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' => __('Enable/Disable', 'moowoodle'),
				'type' => 'checkbox',
				'label' => __('Enable this email notification', 'moowoodle'),
				'default' => 'yes',
			),
			'subject' => array(
				'title' => __('Subject', 'moowoodle'),
				'type' => 'text',
				'description' => sprintf(__('This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'moowoodle'), $this->subject),
				'placeholder' => '',
				'default' => '',
			),
			'heading' => array(
				'title' => __('Email Heading', 'moowoodle'),
				'type' => 'text',
				'description' => sprintf(__('This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'moowoodle'), $this->heading),
				'placeholder' => '',
				'default' => '',
			),
			'email_type' => array(
				'title' => __('Email type', 'moowoodle'),
				'type' => 'select',
				'description' => __('Choose which format of email to send.', 'moowoodle'),
				'default' => 'html',
				'class' => 'email_type',
				'options' => array(
					'plain' => __('Plain text', 'moowoodle'),
					'html' => __('HTML', 'moowoodle'),
					'multipart' => __('Multipart', 'moowoodle'),
				),
			),
		);
	}
}
