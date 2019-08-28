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
namespace Amazon\Maxo\Controller\Adminhtml\Maxo;

/**
 * Generate public/private key pairs for Maxo
 */
class GenerateKeys extends \Magento\Backend\Controller\Adminhtml\System
{
    /**
     * @var \Amazon\Maxo\Model\Adminhtml\GenerateKeys
     */
    private $generateKeys;

    /**
     * GenerateKeys constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Amazon\Maxo\Model\Adminhtml\GenerateKeys $generateKeys
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amazon\Maxo\Model\Adminhtml\GenerateKeys $generateKeys
    ) {
        $this->generateKeys   = $generateKeys;
        parent::__construct($context);
    }

    /**
     * Generate private/public keypairs for Maxo
     */
    public function execute()
    {
        $this->generateKeys->generateKeys();

        $this->messageManager->addSuccess(
            __('Your Amazon Pay public/private key pair has been generated for Amazon Pay v2 Maxo.')
        );
        $this->_redirect('adminhtml/system_config/edit/section/payment');
    }

    /**
     * ACL
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amazon_Maxo::generatekeys');
    }
}
