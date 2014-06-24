<?php

class Query_NovaPontoCom_ConfigController extends Mage_Adminhtml_Controller_Action
{
    public function testDataFormAction()
    {
        $configId  = $this->getRequest()->getParam('id');

        if(!$configId)
        {
            return;
        }

        // determina qual action utilizar
        if($configId == "novapontocom_customer_individual_verify")
        {
            $actionUrl = $this->getUrl('*/*/individualPost/');
        }
        else if($configId == "novapontocom_customer_company_verify")
        {
            $actionUrl = Mage::helper('adminhtml')->getUrl('*/*/companyPost/');
        }
        else if($configId == "novapontocom_customer_address_verify")
        {
            $actionUrl = Mage::helper('adminhtml')->getUrl('*/*/addressPost/');
        }
		
		$collection = Mage::getModel('customer/customer')->getCollection();
		$collection->addAttributeToSort('entity_id', 'DESC');
		$collection->setPage(1, 15);

		$options = array();

		foreach($collection as $customer)
		{
			$customer = Mage::getModel('customer/customer')->load($customer->getId());
			$options[] = "<option value=\"" . $customer->getId() . "\">" . $customer->getName() . "</option>";
		}
		
        $html  = '<div id="novapontocom_customer_config_window_content">';
        $html .= '  <p>' . $this->__("Choose the customer for the data test:") . '</p>';
        $html .= '  <input type="hidden" id="novapontocom_customer_config_window_configId" value="' . $configId . '" />';
        $html .= '  <select class="select required-entry" id="novapontocom_customer_config_window_customerId" style="width: 100%;">';
		$html .= '  	' . implode("\n", $options);
		$html .= '  </select><br /><br />';
        $html .= '  <button type="button" class="scalable" onclick="loadNovaPontoComConfigTestForm(\'' . $actionUrl . '\');">';
        $html .= '    <span><span>' . $this->__("Test") . '</span></span>';
        $html .= '</div>';

        $this->getResponse()->setBody($html);
    }

    public function individualPostAction()
    {		
		$customerId  = $this->getRequest()->getParam('customerId');

        if(!$customerId)
        {
            $customer = null;
        }
        else
        {
            $customer = Mage::getModel('customer/customer')->load($customerId);
        }

		if(!$customer || !$customer->getId())
        {
			$html = '<p class="novapontocom-error-message">' . $this->__('Customer with ID %d does not exists.', $customerId) . '</p>';
		}
        else
        {
			$address = $customer->getDefaultBillingAddress();

            if(!$address)
            {
                $address = $customer->getDefaultShippingAddress();

                if(!$address)
                {
                    $address = Mage::getModel('customer/address');
                }
            }

            $name = "";
            $lastName = "";
            $cpf = "";
            $rg = "";
            $celphone = "";

            $nameConfig = Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig(null, null, $this->getRequest()->getParam('name'));
            $lastNameConfig = Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig(null, null, $this->getRequest()->getParam('last_name'));
            $cpfConfig = Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig(null, null, $this->getRequest()->getParam('cpf'));
            $rgConfig = Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig(null, null, $this->getRequest()->getParam('rg'));
            $celphoneConfig = Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig(null, null, $this->getRequest()->getParam('cel_phone'));

            // busca do atributo
            if($nameConfig['entity'] == "customer")
            {
                $name = $customer->getData($nameConfig['attribute']);
            }
            else if($nameConfig['entity'] == "address")
            {
                $name = $address->getData($nameConfig['attribute']);
            }

            if($lastNameConfig['entity'] == "customer")
            {
                $lastName = $customer->getData($lastNameConfig['attribute']);
            }
            else if($lastNameConfig['entity'] == "address")
            {
                $lastName = $address->getData($lastNameConfig['attribute']);
            }

            if($cpfConfig['entity'] == "customer")
            {
                $cpf = $customer->getData($cpfConfig['attribute']);
            }
            else if($cpfConfig['entity'] == "address")
            {
                $cpf = $address->getData($cpfConfig['attribute']);
            }

            if($rgConfig['entity'] == "customer")
            {
                $rg = $customer->getData($rgConfig['attribute']);
            }
            else if($rgConfig['entity'] == "address")
            {
                $rg = $address->getData($rgConfig['attribute']);
            }

            if($celphoneConfig['entity'] == "customer")
            {
                $celphone = $customer->getData($celphoneConfig['attribute']);
            }
            else if($celphoneConfig['entity'] == "address")
            {
                $celphone = $address->getData($celphoneConfig['attribute']);
            }



            $html  = "<table>";
    		$html .= "<tr><td><b>" . $this->__('First Name') . ":</b></td>";
    		$html .= "<td>" . $name . "</td></tr>";
    		$html .= "<tr><td><b>" . $this->__('Last Name') . ":</b></td>";
    		$html .= "<td>" . $lastName . "</td></tr>";
    		$html .= "<tr><td><b>" . $this->__('CPF') . ":</b></td>";
    		$html .= "<td>" . $cpf . "</td></tr>";
    		$html .= "<tr><td><b>" . $this->__('RG') . ":</b></td>";
    		$html .= "<td>" . $rg . "</td></tr>";
    		$html .= "<tr><td><b>" . $this->__('Cel Phone') . ":</b></td>";
    		$html .= "<td>" . $celphone . "</td></tr>";
    		$html .= "</table>";

		}
		$this->getResponse()->setBody($html);
    }

    public function companyPostAction()
    {
        $customerId  = $this->getRequest()->getParam('customerId');

        if(!$customerId)
        {
            $customer = null;
        }
        else
        {
            $customer = Mage::getModel('customer/customer')->load($customerId);
        }

        if(!$customer || !$customer->getId())
        {
            $html = '<p class="novapontocom-error-message">' . $this->__('Customer with ID %d does not exists.', $customerId) . '</p>';
        }
        else
        {
            $address = $customer->getDefaultBillingAddress();

            if(!$address)
            {
                $address = $customer->getDefaultShippingAddress();

                if(!$address)
                {
                    $address = Mage::getModel('customer/address');
                }
            }

            $company = "";
            $ie = "";
            $cnpj = "";

            $companyConfig = Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig(null, null, $this->getRequest()->getParam('company_name'));
            $ieConfig = Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig(null, null, $this->getRequest()->getParam('ie'));
            $cnpjConfig = Mage::helper('Query_NovaPontoCom')->getCustomerAttributeConfig(null, null, $this->getRequest()->getParam('cnpj'));

            // busca do atributo
            if($companyConfig['entity'] == "customer")
            {
                $company = $customer->getData($companyConfig['attribute']);
            }
            else if($companyConfig['entity'] == "address")
            {
                $company = $address->getData($companyConfig['attribute']);
            }

            if($ieConfig['entity'] == "customer")
            {
                $ie = $customer->getData($ieConfig['attribute']);
            }
            else if($ieConfig['entity'] == "address")
            {
                $ie = $address->getData($ieConfig['attribute']);
            }

            if($cnpjConfig['entity'] == "customer")
            {
                $cnpj = $customer->getData($cnpjConfig['attribute']);
            }
            else if($cnpjConfig['entity'] == "address")
            {
                $cnpj = $address->getData($cnpjConfig['attribute']);
            }


            $html  = "<table>";
            $html .= "<tr><td><b>" . $this->__('Company') . ":</b></td>";
            $html .= "<td>" . $company . "</td></tr>";
            $html .= "<tr><td><b>" . $this->__('IE') . ":</b></td>";
            $html .= "<td>" . $ie . "</td></tr>";
            $html .= "<tr><td><b>" . $this->__('CNPJ') . ":</b></td>";
            $html .= "<td>" . $cnpj . "</td></tr>";
            $html .= "</table>";

        }
        $this->getResponse()->setBody($html);
    }

    public function addressPostAction()
    {
        $customerId  = $this->getRequest()->getParam('customerId');

        if(!$customerId)
        {
            $customer = null;
        }
        else
        {
            $customer = Mage::getModel('customer/customer')->load($customerId);
        }

        if(!$customer || !$customer->getId())
        {
            $html = '<p class="novapontocom-error-message">' . $this->__('Customer with ID %d does not exists.', $customerId) . '</p>';
        }
        else
        {
            $address = $customer->getDefaultBillingAddress();
			
			$html  = "<table>";
            $html .= "<tr><td><b>" . $this->__('Street') . ":</b></td>";
            $html .= "<td>" . $this->_getAddressData($address, $this->getRequest()->getParam('street')). "</td></tr>";
            $html .= "<tr><td><b>" . $this->__('Number') . ":</b></td>";
            $html .= "<td>" . $this->_getAddressData($address, $this->getRequest()->getParam('number')) . "</td></tr>";
            $html .= "<tr><td><b>" . $this->__('Complement') . ":</b></td>";
            $html .= "<td>" . $this->_getAddressData($address, $this->getRequest()->getParam('complement')) . "</td></tr>";
            $html .= "<tr><td><b>" . $this->__('District') . ":</b></td>";
            $html .= "<td>" . $this->_getAddressData($address, $this->getRequest()->getParam('district')) . "</td></tr>";
            $html .= "</table>";
        }
		
        $this->getResponse()->setBody($html);
    }

    private function _getAddressData($address, $index)
    {
        if(!$address || !$address->getId())
		{
			return "";
		}
		
		if($index == "street_1")
        {
            return $address->getStreet(1);
        }
        else if($index == "street_2")
        {
            return $address->getStreet(2);
        }
        else if($index == "street_3")
        {
            return $address->getStreet(3);
        }
        else if($index == "street_4")
        {
            return $address->getStreet(4);
        }
        else
        {
            return $address->getData($index);
        }
    }
}