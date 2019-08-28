<?php
/**
 * Copyright © Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Maxo\Model\Method;

class AmazonMaxoMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'amazon_payment_v2';

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Payment\Block\Form';

    /**
     * Info instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Instructions';

    /**
     * Availability option
     *
     * @var bool
     */
    //protected $_isOffline = true;

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    /*
    public function isActive($storeId = null)
    {
        return (bool)(int)$this->_scopeConfig->getValue(
            AmazonCoreHelper::AMAZON_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        ) && (bool)(int)$this->getConfigData('active', $storeId);
    }
    */

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //todo add functionality later
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //todo add functionality later
    }
}
