<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
class MyCustomAddress extends Module
{
    public function __construct()
    {
        $this->name = 'mycustomaddress';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'iCreative';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Custom Address Fields');
        $this->description = $this->l('Makes the phone field required in the admin panel.');
    }

    public function install()
    {
        return parent::install()
        && $this->registerHook('actionCustomerAddressFormBuilderModifier');
    }

    public function hookActionCustomerAddressFormBuilderModifier($params)
	{
		/** @var \Symfony\Component\Form\FormBuilder $formBuilder */
		$formBuilder = $params['form_builder'];

          $notBlankConstraint = [
            new NotBlank([
                'message' => 'This value should not be blank.', 
            ]),
        ];
		if ($formBuilder->has('phone')) {
			$formBuilder->add('phone', TextType::class, [
				'required' => true,
				'label' => $this->l('Phone'),
				'constraints' => $notBlankConstraint,
			]);
		}
	}

}
