<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace SoColissimo\Form;

use SoColissimo\SoColissimo;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\ConfigQuery;

/**
 * Class ConfigureSoColissimo
 * @package SoColissimo\Form
 * @author Thelia <info@thelia.net>
 */
class ConfigureSoColissimo extends BaseForm
{
    /** @var Translator */
    protected $translator = null;

    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder attribute :
     *
     * $this->formBuilder->add("name", "text")
     *   ->add("email", "email", array(
     *           "attr" => array(
     *               "class" => "field"
     *           ),
     *           "label" => "email",
     *           "constraints" => array(
     *               new \Symfony\Component\Validator\Constraints\NotBlank()
     *           )
     *       )
     *   )
     *   ->add('age', 'integer');
     *
     * @return null
     */
    protected function buildForm()
    {
        $translator = Translator::getInstance();
        $this->formBuilder
            ->add(
                'accountnumber',
                'text',
                [
                    'constraints' => [new NotBlank()],
                    'data'        => ConfigQuery::read(SoColissimo::CONFIG_LOGIN),
                    'label'       => $this->trans("Account number"),
                    'label_attr'  => ['for' => 'accountnumber']
                ]
            )
            ->add(
                'password',
                'text',
                [
                    'constraints' => [new NotBlank()],
                    'data'        => ConfigQuery::read(SoColissimo::CONFIG_PWD),
                    'label'       => $this->trans("Password"),
                    'label_attr'  => ['for' => 'password']
                ]
            )
            ->add(
                'url_prod',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                        new Url([
                            'protocols' => ['https', 'http']
                        ])
                    ],
                    'data'        => ConfigQuery::read(SoColissimo::CONFIG_URL_PROD),
                    'label'       => $this->trans("So Colissimo url prod"),
                    'label_attr'  => ['for' => 'socolissimo_url_prod']
                ]
            )
            ->add(
                'url_test',
                'text',
                [
                    'constraints' => [
                        new NotBlank(),
                        new Url([
                            'protocols' => ['https', 'http']
                        ])
                    ],
                    'data'        => ConfigQuery::read(SoColissimo::CONFIG_URL_TEST),
                    'label'       => $this->trans("So Colissimo url test"),
                    'label_attr'  => ['for' => 'socolissimo_url_test']
                ]
            )
            ->add(
                'test_mode',
                'text',
                [
                    'constraints' => [new NotBlank()],
                    'data'        => ConfigQuery::read(SoColissimo::CONFIG_TEST_MODE),
                    'label'       => $this->trans("test mode"),
                    'label_attr'  => ['for' => 'test_mode']
                ]
            )
            ->add(
                'google_map_key',
                'text',
                [
                    'constraints' => [],
                    'data'        => ConfigQuery::read(SoColissimo::CONFIG_GOOGLE_MAP_KEY),
                    'label'       => $this->trans("Google map API key"),
                    'label_attr'  => ['for' => 'google_map_key']
                ]
            )
            ->add(
                'export_type',
                'choice',
                [
                    'choices' => [
                        SoColissimo::EXPORT_COLISHIP => $this->trans('Coliship'),
                        SoColissimo::EXPORT_EXPEDITOR => $this->trans('Expeditor'),
                    ],
                    'constraints' => [
                        new Callback(
                            array("methods" => array(array($this, "verifyValue")))
                        )
                    ],
                    'label' => $this->trans('Export type'),
                    'label_attr' => [
                        'for' => 'export_type'
                    ],
                    'data' => ConfigQuery::read(SoColissimo::CONFIG_EXPORT_TYPE, SoColissimo::EXPORT_COLISHIP)
                ]
            )
            ->add(
                'sender_code',
                'text',
                [
                    'label' => $this->trans('Sender code'),
                    'label_attr' => [
                        'for' => 'sender_code',
                        'help' => $this->trans('Sender address code in Coliship address book')
                    ],
                    'required' => false,
                    'data' => ConfigQuery::read(SoColissimo::CONFIG_SENDER_CODE)
                ]
            )
            ->add(
                'default_product',
                'choice',
                [
                    'label' => $this->trans('Default product'),
                    'choices' => SoColissimo::getProducts(),
                    'label_attr' => [
                        'for' => 'sender_code',
                        'help' => $this->trans('The default Colissimo product to use')
                    ],
                    'required' => false,
                    'data' => ConfigQuery::read(SoColissimo::CONFIG_DEFAULT_PRODUCT)
                ]
            )
        ;
    }

    public function verifyValue($value, ExecutionContextInterface $context)
    {
        if (SoColissimo::EXPORT_COLISHIP == $value) {
            $data = $context->getRoot()->getData();

            $senderCode = $data["sender_code"];
            $accountNumber = $data["accountnumber"];

            if (empty($senderCode) || empty($accountNumber)) {
                $context->addViolation(
                    $this->trans(
                        'For Coliship export, you should provide an account number and a sender code',
                        [],
                        SoColissimo::DOMAIN
                    )
                );
            }
        }
    }

    protected function trans($id, $parameters = [], $locale = null)
    {
        if (null === $this->translator) {
            $this->translator = Translator::getInstance();
        }
        return $this->translator->trans($id, $parameters, SoColissimo::DOMAIN, $locale);
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return "configuresocolissimo";
    }

}
