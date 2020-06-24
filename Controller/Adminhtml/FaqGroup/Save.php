<?php

/**
 * MagePrince
 * Copyright (C) 2020 Mageprince <info@mageprince.com>
 *
 * @package Mageprince_Faq
 * @copyright Copyright (c) 2020 Mageprince (http://www.mageprince.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MagePrince <info@mageprince.com>
 */

namespace Mageprince\Faq\Controller\Adminhtml\FaqGroup;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Mageprince\Faq\Model\FaqGroup
     */
    private $faqModel;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Mageprince\Faq\Model\FaqGroup
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Mageprince\Faq\Model\FaqGroup $faqModel,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->faqModel = $faqModel;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mageprince_Faq::FaqGroup');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('faqgroup_id');
        
            $model = $this->faqModel->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This FAQgroup no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            
            $data = $this->_filterFaqGroupData($data);
            $cGroup = $data['customer_group'];
            if (isset($cGroup)) {
                $customerGroup = implode(',', $data['customer_group']);
                $data['customer_group'] = $customerGroup;
            }
            $stores = $data['storeview'];
            if (isset($stores)) {
                $store = implode(',', $data['storeview']);
                $data['storeview'] = $store;
            }
            
            $model->setData($data);
        
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the FAQgroup.'));
                $this->dataPersistor->clear('prince_faq_faqgroup');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['faqgroup_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager
                ->addExceptionMessage($e, __('Something went wrong while saving the Faqgroup.'));
            }
        
            $this->dataPersistor->set('prince_faq_faqgroup', $data);
            return $resultRedirect->setPath('*/*/edit',
                [
                    'faqgroup_id' => $this->getRequest()->getParam('faqgroup_id')
                ]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    public function _filterFaqGroupData(array $rawData)
    {
        $data = $rawData;
        if (isset($data['icon'][0]['name'])) {
            $data['icon'] = $data['icon'][0]['name'];
        } else {
            $data['icon'] = null;
        }
        return $data;
    }
}
