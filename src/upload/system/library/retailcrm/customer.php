<?php

namespace Retailcrm;

class Customer extends Base
{
    protected $registry;
    protected $data = array(
        'externalId' => 0,
        'createdAt' => null,
        'firstName' => null,
        'lastName' => null,
        'patronymic' => null,
        'email' => null,
        'phones' => array(),
        'address' => array(),
        'customFields' => array()
    );

    public function prepare($customer) {
        if (file_exists(DIR_SYSTEM . 'library/retailcrm/custom/customer.php')) {
            $custom = new \Retailcrm\Custom\Customer($this->registry);
            $this->data = $custom->prepare($customer);
        } else {
            $this->load->model('setting/setting');
            $setting = $this->model_setting_setting->getSetting(\Retailcrm\Retailcrm::MODULE);

            if (isset($customer['firstname']) && $customer['firstname']) {
                $this->setField('firstName', $customer['firstname']);
            }

            if (isset($customer['lastname']) && $customer['lastname']) {
                $this->setField('lastName', $customer['lastname']);
            }

            if (isset($customer['telephone']) && $customer['telephone']) {
                $phones = array(
                    array(
                        'number' => $customer['telephone']
                    )
                );
                $this->setDataArray($phones, 'phones');
            }

            $this->setField('email', $customer['email']);
            $this->setField('createdAt', date('Y-m-d H:i:s'));

            if (isset($customer['customer_id']) && !$this->data['externalId']) {
                $this->setField('externalId', $customer['customer_id']);
            }

            if (isset($settings[\Retailcrm\Retailcrm::MODULE . '_custom_field']) && $customer['custom_field']) {
                $custom_fields = $this->prepareCustomFields($customer['custom_field'], $setting, 'c_');

                if ($custom_fields) {
                    $this->setDataArray($custom_fields, 'customFields');
                }
            }
        }

        parent::prepare($customer);
    }

    /**
     * @param $retailcrm_api_client
     *
     * @return mixed
     */
    public function create($retailcrm_api_client) {
        if ($retailcrm_api_client === false) {
            return false;
        }

        $response = $retailcrm_api_client->customersCreate($this->data);

        return $response;
    }

    /**
     * @param $retailcrm_api_client
     *
     * @return mixed
     */
    public function edit($retailcrm_api_client) {
        if ($retailcrm_api_client === false) {
            return false;
        }

        $response = $retailcrm_api_client->customersEdit($this->data);

        return $response;
    }
}