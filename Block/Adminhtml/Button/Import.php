<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Import edit block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Expertime\Import\Block\Adminhtml\Button;

class Import extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->update('save', 'label', __('Import User'));
        $this->buttonList->update('save', 'id', 'import_button');
        $this->buttonList->update('save', 'onclick', 'setLocation(\''.$this->getUrl('expertime/import/process').'\')');
        $this->buttonList->update('save', 'data_attribute', '');

        $this->_blockGroup = 'Expertime_Import';
        $this->_controller = 'adminhtml_import';
    }

}
