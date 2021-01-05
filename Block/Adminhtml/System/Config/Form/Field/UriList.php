<?php

declare(strict_types=1);

namespace Fom\Clockwork\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class UriList extends AbstractFieldArray
{
    /**
     * @retrun void
     */
    protected function _construct(): void
    {
        $this->addColumn('uri', ['label' => 'URI']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add URI');
    }
}
