<?php
if (!defined('_PS_VERSION_')) {
	exit;
}

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class CustomAddress extends Module
{
	public function __construct()
	{
		$this->name = 'customaddress';
		$this->version = '1.0.0';
		$this->author = 'iCreative';
		$this->tab = 'administration';
		$this->need_instance = 0;
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Custom Address');
		$this->description = $this->l('Make phone required in FO and BO address forms and remove optional label.');
		$this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => _PS_VERSION_];
	}

	public function install()
	{
		return parent::install() &&
			$this->registerHook('actionCustomerAddressFormBuilderModifier') // Front Office
			&& $this->registerHook('additionalCustomerAddressFields');
		//&& $this->registerHook('actionAdminAddressesFormModifier'); // Back Office
		//	&& $this->registerHook('actionValidateCustomerAddressForm'); // Validation
	}

	public function hookActionCustomerAddressFormBuilderModifier($params)
	{
		/** @var \Symfony\Component\Form\FormBuilder $formBuilder */
		$formBuilder = $params['form_builder'];

		if ($formBuilder->has('phone')) {
			$formBuilder->add('phone', TextType::class, [
				'required' => true,
				'label' => $this->l('Phone'),
				'constraints' => [
					new \Symfony\Component\Validator\Constraints\NotBlank(),
				],
			]);
		}

		if ($formBuilder->has('phone_mobile')) {
			$formBuilder->add('phone_mobile', TextType::class, [
				'required' => true,
				'label' => $this->l('Mobile Phone'),
				'constraints' => [
					new \Symfony\Component\Validator\Constraints\NotBlank(),
				],
			]);
		}
	}

	public function hookAdditionalCustomerAddressFields($params)
	{
		$fields = $params['fields'];

		if (isset($fields['phone'])) {
			$fields['phone']->setRequired(true);
		}
	}


	// public function hookActionValidateCustomerAddressForm($params)
	// {
	// 	/** @var CustomerAddressForm $form */
	// 	$form = $params['form'];

	// 	$phoneField = $form->getField('phone');

	// 	if ($phoneField && empty($phoneField->getValue())) {
	// 		$phoneField->addError($this->l('Phone is required.'));
	// 		return false;
	// 	}

	// 	return true;
	// }

	// public function hookActionAdminAddressesFormModifier($params)
	// {
	// 	/** @var \Symfony\Component\Form\FormBuilder $formBuilder */
	// 	$formBuilder = $params['form_builder'];

	// 	if ($formBuilder->has('phone')) {
	// 		$formBuilder->add('phone', TextType::class, [
	// 			'required' => true,
	// 			'label' => $this->l('Phone'),
	// 		]);
	// 	}
	// }
}
