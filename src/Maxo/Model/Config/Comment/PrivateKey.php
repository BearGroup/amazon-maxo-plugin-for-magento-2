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
namespace Amazon\Maxo\Model\Config\Comment;

class PrivateKey implements \Magento\Config\Model\Config\CommentInterface
{
    /**
     * @var \Amazon\Maxo\Model\AmazonConfig
     */
    private $amazonConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    /**
     * MaxoPrivateKey constructor.
     * @param \Amazon\Maxo\Model\AmazonConfig $amazonConfig,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     */
    public function __construct(
        \Amazon\Maxo\Model\AmazonConfig $amazonConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        $this->amazonConfig      = $amazonConfig;
        $this->storeManager     = $storeManager;
        $this->urlBuilder       = $urlBuilder;
    }

    /**
     * Dynamic comment text for Maxo Public Key ID
     *
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $pubkey  = $this->amazonConfig->getPublicKey();
        $privkey = $this->amazonConfig->getPrivateKey();

        $generateUrl = $this->urlBuilder->getUrl('amazon_maxo/maxo/generatekeys');
        $downloadUrl = $this->urlBuilder->getUrl('amazon_maxo/maxo/download');

        if (!$privkey) {
            return '<a href="' . $generateUrl . '">' . __('Generate a new public/private key pair for Amazon Pay') . '</a>';
        } elseif ($pubkey) {
            return '<a href="' . $downloadUrl . '">' . __('Download Public Key') . '</a>';
        }
    }
}
