<?php
namespace Expertime\Import\Api;

interface ImportManagementInterface{
    /**
     * Create/Update customers which match a specified criteria.
     *
     *
     * @param array $data
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomers($data);
}
