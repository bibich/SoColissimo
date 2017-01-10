<?php

namespace SoColissimo\Controller;

use SoColissimo\SoColissimo;
use Thelia\Controller\Admin\BaseAdminController;
use SoColissimo\Form\ConfigureSoColissimo;
use Thelia\Core\Translation\Translator;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Security\AccessManager;
use Thelia\Model\ConfigQuery;
use Thelia\Tools\URL;

class SaveConfig extends BaseAdminController
{
    public function save()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('SoColissimo'), AccessManager::UPDATE)) {
            return $response;
        }

        $form = new ConfigureSoColissimo($this->getRequest());
        try {
            $vform = $this->validateForm($form);

            ConfigQuery::write(SoColissimo::CONFIG_LOGIN, $vform->get('accountnumber')->getData(), 1, 1);
            ConfigQuery::write(SoColissimo::CONFIG_PWD, $vform->get('password')->getData(), 1, 1);
            ConfigQuery::write(SoColissimo::CONFIG_GOOGLE_MAP_KEY, $vform->get('google_map_key')->getData(), 1, 1);
            ConfigQuery::write(SoColissimo::CONFIG_URL_PROD, $vform->get('url_prod')->getData(), 1, 1);
            ConfigQuery::write(SoColissimo::CONFIG_URL_TEST, $vform->get('url_test')->getData(), 1, 1);
            ConfigQuery::write(SoColissimo::CONFIG_TEST_MODE, $vform->get('test_mode')->getData(), 1, 1);
            ConfigQuery::write(SoColissimo::CONFIG_EXPORT_TYPE, $vform->get('export_type')->getData(), 1, 1);
            ConfigQuery::write(SoColissimo::CONFIG_SENDER_CODE, $vform->get('sender_code')->getData(), 1, 1);
            ConfigQuery::write(SoColissimo::CONFIG_DEFAULT_PRODUCT, $vform->get('default_product')->getData(), 1, 1);

            return $this->generateRedirect(
                URL::getInstance()->absoluteUrl('/admin/module/SoColissimo', ['current_tab' => 'configure'])
            );
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans("So Colissimo update config"),
                $e->getMessage(),
                $form,
                $e
            );

            return $this->render(
                'module-configure',
                [
                    'module_code' => 'SoColissimo',
                    'current_tab' => 'configure',
                ]
            );
        }
    }
}
